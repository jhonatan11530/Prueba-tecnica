<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AuthTokenMiddleware
{
    /**
     * Handle an incoming request.
     * [CUMPLIMIENTO DE PRUEBA TÉCNICA - PASO 6 y 8]
     * Este middleware actúa como el "Portero" de la API. Reemplaza por completo
     * la funcionalidad automática de Sanctum/Passport validando criptográficamente el JWT.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $header = $request->header('Authorization');

        if (!$header || !preg_match('/Bearer\s(\S+)/', $header, $matches)) {
            return $this->errorResponse('AUTH_TOKEN_MISSING', 'Falta el token de autenticación', 401);
        }

        $tokenCifrado = $matches[1];

        // 1. Derivación de llaves usando HMAC-based Extract-and-Expand Key Derivation Function (HKDF)
        $secreto = base64_decode(env("APP_TOKEN_SECRET"));
        $claveFirma = hash_hkdf("sha256", $secreto, 32, "jwt-firma");
        $claveCifrado = hash_hkdf("sha256", $secreto, 32, "jwt-cifrado");

        try {
            // 2. Descifrado de la capa externa AES-256-GCM
            $encrypter = new Encrypter($claveCifrado, "aes-256-gcm");
            $jwt = $encrypter->decryptString($tokenCifrado);

            // 3. Verificación de la firma criptográfica (HS256) y validación de expiración (exp)
            $payload = JWT::decode($jwt, new Key($claveFirma, 'HS256'));

            // 4. Mecanismo de Blacklist: Verificamos que el JTI único no haya sido revocado (Logout/Refresh)
            $tokenDb = DB::table('tokens_revocados')->where('jti', $payload->jti)->first();
            if ($tokenDb && $tokenDb->revocado) {
                return $this->errorResponse('AUTH_TOKEN_REVOKED', 'El token ha sido revocado', 401);
            }

            // 5. Inyección de contexto: Pasamos el ID del usuario de forma segura a los controladores
            $request->attributes->set('auth_usuario_id', $payload->sub);
            $request->attributes->set('auth_jti', $payload->jti);

            // Permitir el paso al controlador
            return $next($request);

        } catch (\Firebase\JWT\ExpiredException $e) {
            return $this->errorResponse('AUTH_TOKEN_EXPIRED', 'El token ha expirado', 401);
        } catch (\Exception $e) {
            return $this->errorResponse('AUTH_TOKEN_INVALID', 'Token inválido o malformado', 401);
        }
    }

    /**
     * Helper para formatear errores respetando la estructura JSON global de la prueba técnica.
     */
    private function errorResponse($code, $message, $status)
    {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => $code,
                'message' => $message
            ],
            'trace_id' => (string) Str::uuid()
        ], $status);
    }
}