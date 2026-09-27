<?php
namespace Tests\Feature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Firebase\JWT\JWT;
use Illuminate\Encryption\Encrypter;
use App\Services\Contracts\CurrencyProviderInterface;
use App\Services\Contracts\WeatherProviderInterface;
use Mockery;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        putenv('APP_TOKEN_SECRET=' . base64_encode(random_bytes(32)));
        
        $gbp = DB::table('monedas')->insertGetId(['codigo' => 'GBP', 'nombre' => 'Libra', 'simbolo' => 'Ã‚Â£']);
        $jap = DB::table('paises')->insertGetId(['nombre' => 'JapÃƒÂ³n', 'codigo' => 'JP', 'moneda_id' => $gbp]);
        DB::table('ciudades')->insert(['id' => 1, 'pais_id' => $jap, 'nombre' => 'Tokio']);
        
        DB::table('usuarios')->insert([
            'id' => 1,
            'nombre' => 'Prueba',
            'correo' => 'test@test.com',
            'password_hash' => Hash::make('Admin123'),
            'idioma' => 'es'
        ]);
    }

    private function generarTokenValido($modificado = false, $secretoIncorrecto = false, $expirado = false)
    {
        $secreto = base64_decode(env("APP_TOKEN_SECRET"));
        if ($secretoIncorrecto) {
            $secreto = random_bytes(32);
        }
        
        $claveFirma = hash_hkdf("sha256", $secreto, 32, "jwt-firma");
        $claveCifrado = hash_hkdf("sha256", $secreto, 32, "jwt-cifrado");

        $payload = [
            "sub" => 1,
            "iat" => time(),
            "exp" => $expirado ? time() - 100 : time() + 900,
            "jti" => 'test-uuid-jti',
            "idioma" => 'es'
        ];

        $jwt = JWT::encode($payload, $claveFirma, "HS256");
        
        if ($modificado) {
            $jwt .= 'x';
        }

        $encrypter = new Encrypter($claveCifrado, "aes-256-gcm");
        return $encrypter->encryptString($jwt);
    }

    public function test_token_valido_aceptado()
    {
        $token = $this->generarTokenValido();
        $response = $this->withHeader('Authorization', "Bearer $token")->getJson('/api/paises');
        $response->assertStatus(200);
    }

    public function test_token_alterado_rechazado()
    {
        $token = $this->generarTokenValido(true);
        $response = $this->withHeader('Authorization', "Bearer $token")->getJson('/api/paises');
        $response->assertStatus(401)->assertJsonPath('error.code', 'AUTH_TOKEN_INVALID');
    }

    public function test_token_otro_secreto_rechazado()
    {
        $token = $this->generarTokenValido(false, true);
        $response = $this->withHeader('Authorization', "Bearer $token")->getJson('/api/paises');
        $response->assertStatus(401)->assertJsonPath('error.code', 'AUTH_TOKEN_INVALID');
    }

    public function test_token_vencido_rechazado()
    {
        $token = $this->generarTokenValido(false, false, true);
        $response = $this->withHeader('Authorization', "Bearer $token")->getJson('/api/paises');
        $response->assertStatus(401)->assertJsonPath('error.code', 'AUTH_TOKEN_EXPIRED');
    }

    public function test_token_rechazado_despues_logout()
    {
        $token = $this->generarTokenValido();
        DB::table('tokens_revocados')->insert(['usuario_id' => 1, 'jti' => 'test-uuid-jti', 'revocado' => true]);
        $response = $this->withHeader('Authorization', "Bearer $token")->getJson('/api/paises');
        $response->assertStatus(401)->assertJsonPath('error.code', 'AUTH_TOKEN_REVOKED');
    }

    public function test_refresh_token_usado_dos_veces()
    {
        $refresh = 'refresh-test';
        DB::table('tokens_revocados')->insert([
            'usuario_id' => 1, 'jti' => 'old-jti', 'refresh_token_hash' => hash('sha256', $refresh),
            'revocado' => true, 'expires_at' => now()->addDays(1)
        ]);

        $response = $this->postJson('/api/auth/refresh', ['refresh_token' => $refresh]);
        $response->assertStatus(401);
        $revocados = DB::table('tokens_revocados')->where('usuario_id', 1)->where('revocado', false)->count();
        $this->assertEquals(0, $revocados);
    }

    public function test_login_rate_limiting()
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/auth/login', ['correo' => 'test@test.com', 'password' => 'Mal123']);
        }
        $response = $this->postJson('/api/auth/login', ['correo' => 'test@test.com', 'password' => 'Mal123']);
        $response->assertStatus(429)->assertJsonPath('error.code', 'TOO_MANY_ATTEMPTS');
    }

    public function test_presupuesto_invalido_422()
    {
        $token = $this->generarTokenValido();
        $response = $this->withHeader('Authorization', "Bearer $token")
                         ->postJson('/api/consultas', ['ciudad_id' => 1, 'presupuesto' => -50]);
        $response->assertStatus(422);
    }

    public function test_conversion_moneda_mock()
    {
        $mock = Mockery::mock(CurrencyProviderInterface::class);
        $mock->shouldReceive('getExchangeRate')->andReturn(2.5);
        $this->app->instance(CurrencyProviderInterface::class, $mock);
        
        $mockW = Mockery::mock(WeatherProviderInterface::class);
        $mockW->shouldReceive('getCurrentTemperature')->andReturn(15.0);
        $this->app->instance(WeatherProviderInterface::class, $mockW);

        $token = $this->generarTokenValido();
        $response = $this->withHeader('Authorization', "Bearer $token")
                         ->postJson('/api/consultas', ['ciudad_id' => 1, 'presupuesto' => 100]);
        $response->assertStatus(200)->assertJsonPath('data.valor_convertido', 250);
    }

    public function test_falla_api_usa_tasa_guardada()
    {
        DB::table('tasas_cambio')->insert([
            'moneda_origen' => 'COP', 'moneda_destino' => 'GBP', 'tasa' => 3.0, 'fecha_consulta' => now()->subDay()
        ]);

        $mock = Mockery::mock(CurrencyProviderInterface::class);
        $mock->shouldReceive('getExchangeRate')->andReturn(null);
        $this->app->instance(CurrencyProviderInterface::class, $mock);
        
        $mockW = Mockery::mock(WeatherProviderInterface::class);
        $mockW->shouldReceive('getCurrentTemperature')->andReturn(15.0);
        $this->app->instance(WeatherProviderInterface::class, $mockW);

        $token = $this->generarTokenValido();
        $response = $this->withHeader('Authorization', "Bearer $token")
                         ->postJson('/api/consultas', ['ciudad_id' => 1, 'presupuesto' => 100]);
        $response->assertStatus(200)->assertJsonPath('data.valor_convertido', 300)->assertJsonPath('data.conversion_aviso', null);
    }
}
