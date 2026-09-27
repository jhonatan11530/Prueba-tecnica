# ✈️ Travel Planner API & Frontend - Prueba Técnica

¡Hola! Mi nombre es **Jhonatan fernandez muñoz** y esta es mi solución Fullstack para la prueba técnica. 
He desarrollado este proyecto utilizando **Laravel 11** para el backend y **Angular 16** para el frontend, asegurándome de cumplir al 100% con cada una de las reglas, restricciones de seguridad y principios de diseño que exige la prueba tecnica.

---

## 🚀 Características Adicionales
Me gusta entregar código listo para producción, así que decidí ir un poco más allá de los requisitos originales e implementé estas mejoras adicionales:

*   **DevOps y Docker:** He creado un `docker-compose.yml` en la raíz para que puedas levantar la base de datos PostgreSQL 15 y pgAdmin con un solo comando. ¡Cero instalaciones manuales!
*   **Integración Continua (CI/CD):** Configuré un pipeline en GitHub Actions (`.github/workflows/laravel-tests.yml`). Cada vez que subo código, las pruebas unitarias se ejecutan solas en la nube.
*   **Caché para Rendimiento:** Envolví las llamadas a las APIs del Clima y Divisas usando `Cache::remember()`. He configurado el sistema para que memorice las respuestas por 10 minutos, protegiendo las cuotas gratuitas de las APIs y logrando respuestas casi instantáneas.
*   **Observabilidad (Logs Estructurados):** Si la API de divisas externa llega a fallar, mi código no solo activa el Fallback a la base de datos de forma silenciosa para el usuario, sino que también deja un rastro estructurado usando `Log::warning` para que el equipo de soporte sepa exactamente qué pasó.

---

## ✅ Cómo cumplí con las Reglas del Proyecto

### 1. Seguridad y JWT a la medida
Como el documento prohibía usar Sanctum, Passport o Fortify, **decidí construir el sistema JWT desde cero**. 
He utilizado la librería `firebase/php-jwt` para generar los tokens. Además, usé la función `hash_hkdf` para derivar dos llaves distintas a partir de mi `APP_TOKEN_SECRET`: una llave para **firmar** el token (HS256) y otra llave para **encriptarlo** por completo usando el `Encrypter` nativo de Laravel (AES-256-GCM). Finalmente, diseñé un Middleware ("el portero") que valida los tokens contra una tabla de revocación (Blacklist) que creé en la base de datos.

### 2. Estructuras de Error Estrictas y Multi-idioma
Me enfoqué mucho en que el backend **jamás** devuelva un error genérico en HTML. 
Intercepté el manejador global de excepciones (`bootstrap/app.php`) para garantizar que todas mis respuestas de error tengan exactamente el formato `{error: {code, message, details}}` junto con un `trace_id` aleatorio. 
Además, le agregué soporte multi-idioma: si configuras Angular para enviar la cabecera `Accept-Language: de`, ¡mis errores de sistema se traducirán automáticamente al Alemán!

### 3. Principios SOLID
Estructuré el código pensando en la escalabilidad. Por ejemplo, mi controlador principal no tiene idea de qué API externa estoy usando; en su lugar, he utilizado Inversión de Dependencias (DIP) inyectando interfaces como `WeatherProviderInterface` y `CurrencyProviderInterface`.

### 4. Testing (Pruebas Unitarias)
He escrito una suite con 10 pruebas unitarias y de integración (`ApiTest.php`) donde demuestro mediante *Mocks* y aserciones estrictas que mis tokens son inviolables y que mis fallbacks funcionan correctamente. 

### 5. Frontend en Angular 16
Para el cliente, he diseñado componentes modulares usando únicamente **Bootstrap 5** (nada de librerías pesadas de componentes). 
Programé un `AuthInterceptor` que inyecta automáticamente el token en mis peticiones HTTP y que, si detecta un error `401 AUTH_TOKEN_EXPIRED`, pide un *Refresh Token* silenciosamente en segundo plano sin sacar al usuario de la aplicación.

---

## ⚙️ Cómo ejecutar mi proyecto

### Opción 1: ¡Con Docker! (La forma más fácil)
1. Abre tu terminal en la raíz del proyecto y ejecuta:
   ```bash
   docker compose up -d
   ```
*(Esto levantará automáticamente PostgreSQL en el puerto 5432).*

### Opción 2: Base de Datos Manual
Si no usas Docker, asegúrate de tener tu PostgreSQL local corriendo en el puerto 5432.

### 🖥️ Levantar el Backend (Laravel)
1. Entra a la carpeta del backend: `cd backend`
2. Instala mis dependencias: `composer install`
3. Asegúrate de tener tu `.env` configurado (puedes copiar el `.env.example`).
4. Genera la llave de cifrado: `php artisan key:generate`
5. Migra y puebla mi base de datos: `php artisan migrate:fresh --seed`
6. Inicia el servidor: `php artisan serve --port=8000`

**Usuario de prueba generado automáticamente:**
* **Correo:** marlon@ejemplo.com
* **Contraseña:** Marlon123

### 🌐 Levantar el Frontend (Angular)
1. Abre otra terminal y entra al frontend: `cd frontend`
2. Instala las dependencias: `npm install`
3. Inicia la aplicación: `npx ng serve -o`
*(Se abrirá automáticamente http://localhost:4200 en tu navegador)*

---

## 📝 Colección de Postman
Te he dejado un archivo llamado `Jhonatan_fernandez_API.postman_collection.json` en la raíz del proyecto. Lo configuré con scripts automáticos para que, cuando hagas Login, guarde el Token en las variables de entorno y lo use automáticamente en las demás rutas sin que tengas que copiar y pegar nada.