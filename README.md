# Kyros Rent Car — SaaS multi-tenant

Plataforma web moderna, segura y multiempresa para administrar negocios de alquiler de vehículos. Cada rent car tiene su propio espacio operativo y una **página pública con slug** (`/r/mi-empresa`), sin acceso a datos de otras empresas.

Construido con **PHP puro (sin frameworks pesados) + MySQL (PDO) + Tailwind CSS**. Frontend con Chart.js, FullCalendar, Lucide Icons, AOS y Alpine.js (vía CDN).

---

## Características

- **Multi-tenant real**: aislamiento estricto por `tenant_id` en cada consulta (anti-IDOR).
- **Roles y permisos**: Super Admin, Dueño, Administrador, Agente, Flotilla, Contabilidad, Chofer, Cliente.
- **Panel Super Admin (Kyros)**: empresas, planes, usuarios globales, logs, métricas SaaS (MRR).
- **Dashboard Rent Car**: KPIs, ingresos mensuales (Chart.js), ocupación de flotilla, devoluciones vencidas y alertas de documentos.
- **Flotilla**: CRUD completo, categorías, imágenes múltiples, estados, control de vencimientos (seguro, marbete, matrícula, inspección).
- **Página pública por slug**: hero, buscador con filtros, grid de vehículos, detalle con galería y formulario de reserva con cálculo de precio en vivo.
- **Reservas**: públicas e internas, control de disponibilidad inteligente (sin doble reserva), calendario FullCalendar, flujo de estados.
- **Clientes, Contratos, Pagos, Mantenimiento, Reportes, Configuración**.
- **Seguridad**: `password_hash`, sesiones seguras, CSRF en todos los formularios, prepared statements, validación server-side, anti brute-force, subida de archivos con validación de MIME real, headers de seguridad y `.htaccess` endurecido.

---

## Requisitos del servidor

| Requisito | Versión |
|-----------|---------|
| PHP       | 8.0 o superior |
| MySQL / MariaDB | 5.7+ / 10.3+ |
| Apache    | con `mod_rewrite` |

**Extensiones PHP necesarias:** `pdo_mysql`, `json`, `fileinfo`, `mbstring`, `openssl`.
(Todas vienen activas por defecto en XAMPP.)

---

## Instalación (XAMPP en local)

El proyecto asume que está en `C:\xampp\htdocs\kyros-rentcar-saas` y se sirve desde la carpeta `public/`.

### 1. Base de datos (opción A — instalador CLI, recomendado)

Con MySQL de XAMPP activo, desde la raíz del proyecto:

```bash
C:\xampp\php\php.exe install.php
```

Esto crea la base `kyros_rentcar`, importa el esquema, los datos base y los datos demo.
Para instalar **sin** datos demo: `php install.php --no-demo`.

### 1. Base de datos (opción B — manual)

Importa en este orden (phpMyAdmin o consola):

```bash
mysql -u root < database/schema.sql
mysql -u root < database/seeders.sql
mysql -u root < database/demo_data.sql   # opcional (datos demo)
```

### 2. Configuración

Edita `config/database.php` si tus credenciales no son las de XAMPP por defecto
(usuario `root`, sin contraseña):

```php
'host' => '127.0.0.1', 'database' => 'kyros_rentcar',
'username' => 'root',  'password' => '',
```

Edita `config/app.php` y ajusta `url` y `base_path` a tu instalación.
Por defecto:

```php
'url'       => 'http://localhost/kyros-rentcar-saas/public',
'base_path' => '/kyros-rentcar-saas/public',
```

### 3. Permisos de almacenamiento

Asegúrate de que estas carpetas sean escribibles por el servidor web:

```
storage/logs  storage/contracts  storage/invoices  storage/documents  storage/temp
public/assets/uploads
```

(En Windows/XAMPP normalmente ya lo son.)

### 4. SMTP (opcional)

Para envío real de correos, instala PHPMailer (`composer require phpmailer/phpmailer`)
y completa `config/mail.php` (`enabled => true` + credenciales SMTP).
Mientras esté deshabilitado, los correos se registran en `storage/logs/`.

### 5. Acceder

```
http://localhost/kyros-rentcar-saas/public/
```

---

## Credenciales demo

> ⚠️ **Cámbialas en producción.**

| Rol | Email | Contraseña |
|-----|-------|-----------|
| Super Admin Kyros Rent Car | `admin@kyrosrd.com` | `Admin123*` |
| Dueño Rent Car (demo) | `owner@demo.com` | `Demo123*` |

Página pública demo: `http://localhost/kyros-rentcar-saas/public/r/kyros-rent-car`

---

## Estructura del proyecto

```
kyros-rentcar-saas/
├── app/
│   ├── Core/          # Database, Router, Auth, Session, Csrf, View, Validator, Model...
│   ├── Controllers/   # Auth, SuperAdmin, Admin, PublicSite
│   ├── Models/        # Tenant, Vehicle, Customer, Reservation, Contract, Payment...
│   ├── Services/      # LoginThrottle, FileUploader
│   ├── Middlewares/   # Auth, Tenant, SuperAdmin, Permission
│   ├── Helpers/       # functions.php (e, url, money, status_badge, whatsapp_link...)
│   ├── Views/         # layouts, auth, superadmin, admin, public
│   ├── bootstrap.php  # autoloader + init
│   └── routes.php     # definición de rutas
├── config/            # app, database, mail, security
├── public/            # index.php (front controller), assets, .htaccess
├── storage/           # logs, contracts, invoices, documents, temp
├── database/          # schema.sql, seeders.sql, demo_data.sql
├── api/v1/            # (preparado para API REST futura)
├── install.php        # instalador CLI
└── composer.json
```

---

## Rutas principales

| Ruta | Descripción |
|------|-------------|
| `/` | Landing del SaaS |
| `/login`, `/register` | Autenticación / alta de rent car |
| `/super-admin` | Panel Super Admin (Kyros) |
| `/admin/dashboard` | Panel de la rent car |
| `/admin/vehicles` | Flotilla (CRUD) |
| `/admin/reservations` · `/admin/reservations/calendar` | Reservas + calendario |
| `/r/{slug}` | Página pública de la rent car |
| `/r/{slug}/vehiculo/{vehicle_slug}` | Detalle de vehículo |
| `/r/{slug}/reservar/{vehicle_slug}` | Formulario de reserva |

---

## Seguridad para producción

1. En `config/app.php`: `debug => false`, `env => production`.
2. En `config/security.php`: `cookie_secure => true` (sirviendo por HTTPS).
3. Cambia **todas** las credenciales demo y elimina la empresa demo si no la usas.
4. Sirve siempre desde `public/`; mantén `app/`, `config/`, `storage/` y `database/` fuera del docroot o protegidos (ya hay `.htaccess`).
5. Configura SMTP real y revisa los headers de seguridad / CSP en `config/security.php`.
6. Realiza copias de seguridad periódicas de la base de datos.

---

## Notas de arquitectura

- **Tenant scoping**: `App\Core\Model` recibe `tenant_id` en cada método y construye el guard SQL automáticamente. `Auth::tenantId()` es la única fuente de verdad.
- **CSRF**: validado en `public/index.php` para todo método mutante (POST/PUT/PATCH/DELETE).
- **PDF / WhatsApp / API**: la arquitectura está preparada (carpeta `api/v1/`, helper `whatsapp_link()`, tabla `api_keys` con tokens hasheados). Integra Dompdf/mPDF y PHPMailer cuando los necesites.

---

© Kyros Rent Car
