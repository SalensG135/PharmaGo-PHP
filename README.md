# PharmaGo (PHP 8.2+)

Migración de `sistema-web` (HTML + JS + Node + localStorage) a PHP + MySQL (PDO).

## Puesta en marcha
1. Crear la base con el script SQL original y aplicar `database/migrations/001_alinear_con_aplicacion.sql`.
2. `cp .env.example .env` y completar credenciales (usuario con privilegios mínimos).
3. (Opcional, desarrollo) `php database/seed.php`
4. `php -S localhost:8000 -t public public/index.php`  — en producción apuntar el DocumentRoot a `public/`.

Requiere extensiones: pdo_mysql, intl, mbstring.

## Equivalencias (origen → destino)
| Origen | Destino |
|---|---|
| server.js, _redirects, package.json | `public/index.php`, `public/.htaccess` (el servidor Node ya no existe) |
| services/storageService.js | `src/Core/Database.php` (PDO) |
| core/modelo.js | `src/Repository/BaseRepository.php` |
| roles/eps/farmacias/productos/inventario/usuarios `*Model.js` | `src/Repository/*Repository.php` |
| `*View.js` + `*Controller.js` de cada módulo | `config/modules.php` + `src/Controller/CrudController.php` + `views/crud.php` |
| core/vista.js, core/controlador.js | `CrudController`, `Validator`, `FieldOptions`, `views/` |
| core/utils.js | `src/Core/helpers.php` (`e()`), `src/Core/Text.php` |
| auth/sesion.js, auth/login.js, auth/registro.js | `Session`, `Auth`, `AuthController`, `views/login.php`, `views/registro.php` |
| core/dashboard.js | `Modulos`, `views/layout.php` |
| disponibilidad/* | `DisponibilidadRepository`, `BuscarController`, `views/buscar.php` |
| reportes/* | `ReporteRepository`, `ReporteController`, `views/reportes.php` |
| services/datosIniciales.js | `database/seed.php` |
| index.html | `views/landing.php` |

No convertidos (se conservan tal cual): `css/*` y `core/landing.js` (animaciones y filtros del navegador).
