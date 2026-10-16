# Plataforma Base Multi-Condominio (SaaS & Boilerplate)

Sistema web rápido, moderno y altamente escalable diseñado como **estructura base para cualquier proyecto de condominios**. Permite gestionar múltiples edificios residenciales simultáneamente, cada uno con su propio nombre, cantidad variable de pisos, diferente cantidad de apartamentos, cuentas bancarias, cuotas y finanzas completamente aisladas.

---

## 🚀 Características Principales de la Plataforma Base

1. **Arquitectura Multi-Condominio Centralizada**:
   - Administre cualquier cantidad de condominios desde una sola instalación y base de datos.
   - Cada condominio es independiente: tiene su propia estructura de pisos, apartamentos, residentes, cuotas, pagos y solicitudes.
2. **Generador Dinámico de Estructura de Edificios**:
   - Asistente para crear nuevos condominios en segundos con vista previa en tiempo real.
   - Configuración flexible: cantidad de pisos (1 a 60), apartamentos por piso (1 a 20), con/sin Planta Baja (PB), con/sin Penthouse (PH), y nomenclatura por letras (`1A, 1B...`) o por números (`101, 102...`).
   - Cálculo automático y equitativo de alícuotas (`100% / total_apartamentos`).
   - Capacidad para agregar apartamentos individuales personalizados (`PH-1`, `Local-01`, etc.) o reconfigurar la estructura.
3. **Portal Super-Administrador Global (`superadmin_dashboard.php`)**:
   - Visión consolidada de todos los condominios registrados.
   - Métricas globales: total de condominios, unidades residenciales, pagos en auditoría y deuda global.
   - Conmutador instantáneo de edificio ("Administrar Edificio") para alternar la vista entre condominios sin cerrar sesión.
   - **Control Exclusivo y Blindaje de Alícuotas**: Conforme a la Ley de Propiedad Horizontal, la modificación de porcentajes de alícuotas está reservada exclusivamente a este rol, bloqueada en los demás niveles y protegida por validación en backend.
4. **Portal del Administrador Local (`admin_dashboard.php`)**:
   - Mapa visual interactivo de pisos y unidades residenciales con semáforo de solvencia en vivo.
   - **Módulo de Perfil del Administrador**: Actualización de datos de contacto institucional (C.I., teléfono principal, secundario, cargo) y cambio de contraseña con verificación obligatoria de la clave actual.
   - Emisión de cuotas ordinarias y extraordinarias (al edificio completo o a unidades específicas).
   - Auditoría de pagos en Bolívares y Dólares con visualización de comprobantes.
   - Atención y resolución de quejas, averías y solicitudes.
   - Cartelera informativa digital.
5. **Portal de Autogestión del Residente (`resident_dashboard.php`)**:
   - Consulta de deudas en USD y su equivalente en Bs. calculado en tiempo real con la tasa oficial BCV.
   - Reporte de transferencias, pago móvil o Zelle con carga de comprobantes.
   - Registro de incidencias y quejas con seguimiento de respuestas.
   - Pestaña **"Mi Perfil"** para registrar habitantes, vehículos, mascotas y contacto de emergencia.
6. **Experiencia de Usuario 2.0 (Menú Vertical Retráctil)**:
   - Nuevo sidebar drawer vertical colapsable implementado transversalmente para los 3 roles (**Admin, Superadmin y Residente**).
   - Elimina las barras horizontales con scroll molesto.
   - Modo compacto (`w-16`) con iconos y modo expandido (`w-64`) con persistencia automática de estado en `localStorage`.
   - Totalmente responsivo en dispositivos móviles mediante overlay táctil con efecto de difuminado (*backdrop blur*).
7. **Sincronización Automatizada BCV**:
   - Extracción de la tasa oficial del dólar y fecha valor directamente desde [www.bcv.org.ve](https://www.bcv.org.ve/).
8. **Seguridad y Compatibilidad con Cualquier Hosting**:
   - Configuración desacoplada con soporte para variables de entorno (`.env`).
   - Reglas de seguridad `.htaccess` para Apache/cPanel: protección de archivos sensibles y bloqueo de scripts en subcarpeta `uploads/`.

---

## 🌟 Resumen de Modificaciones Recientes en el Sistema

| Modificación / Característica | Alcance / Rol | Detalle Técnico y Funcional |
| :--- | :--- | :--- |
| **Menú Vertical Retráctil** | Administrador, Superadmin y Residente | Sustitución completa de las pestañas horizontales por una barra lateral retráctil vertical (`sidebar-drawer`). Incluye botón flotante en móviles, backdrop blur y guardado del estado en `localStorage`. |
| **Módulo de Perfil del Administrador** | Administrador Local | Nueva vista para actualizar Cédula de Identidad, teléfonos y cargo de la Junta. Incluye cambio seguro de contraseña validando primero la contraseña actual en frontend y backend (`api/auth.php`). |
| **Blindaje de Alícuotas Inmobiliarias** | Superadmin Exclusivo | Restricción total de edición de alícuotas únicamente para el Superadmin. En los paneles de Administrador y Residente los campos quedan en modo solo lectura (`readonly`), y la API `api/apartamentos.php` rechaza cualquier alteración no autorizada. |
| **Presentación Dinámica Remotion** | SaaS Presentation | Video de presentación corporativo actualizado en formatos horizontal (16:9 1080p) y vertical (9:16 reels/shorts) con 7 escenas secuenciales detallando las ventajas competitivas y la evolución tecnológica. |


---

## 🌐 Guía de Despliegue en Internet (Producción / Hosting)

Este proyecto está diseñado para funcionar en **cualquier servidor web estándar con soporte para PHP 8.0+ y MySQL/MariaDB** (Hosting Compartido cPanel, Hostinger, Siteground, VPS Ubuntu, Docker, Render, etc.).

### Paso 1: Subir los Archivos al Servidor
- Suba todos los archivos de la raíz del proyecto al directorio web de su hosting (normalmente `public_html/` o `htdocs/`).

### Paso 2: Crear la Base de Datos en su Hosting
1. En el panel de su hosting (ej. cPanel -> *Bases de datos MySQL*), cree una base de datos nueva (ej. `miempresa_condominios`).
2. Cree un usuario de base de datos con contraseña segura y asígnele **todos los privilegios** sobre la base de datos creada.

### Paso 3: Configurar las Credenciales de Conexión
Cree un archivo llamado `.env` en la raíz del proyecto (puede duplicar `.env.example`) y configure sus credenciales:

```env
DB_HOST=localhost
DB_PORT=3306
DB_NAME=miempresa_condominios
DB_USER=miempresa_usuario
DB_PASS=SuContraseñaSegura123#

APP_ENV=production
APP_DEBUG=false
APP_URL=https://tudominio.com
```

> **Nota para servidores locales (XAMPP)**: Si no existe el archivo `.env`, el sistema se conectará por defecto a `localhost`, usuario `root`, sin contraseña y base de datos `condominio_db`.

### Paso 4: Inicialización Automática
Al ingresar por primera vez a `https://tudominio.com/`, el sistema verificará y creará automáticamente todas las tablas necesarias y registrará el condominio base y el usuario Super-Admin.

También puede ejecutar la inicialización manualmente desde el navegador o terminal:
```bash
php config/init_db.php
```

---

## 🔑 Credenciales de Acceso al Sistema

### 1. Superusuario Global (Super-Admin)
Acceso con control total de todos los condominios, creación de nuevos edificios y gestión centralizada:
- **URL**: `https://tudominio.com/` (Pestaña *Administración*) o directo en `superadmin_dashboard.php`
- **Usuario**: `clusterlg`
- **Contraseña**: `glretsulc`

### 2. Administradores Locales (Uno por cada Condominio)
Cada condominio tiene su propio usuario administrador que solo accede a los datos y finanzas de su edificio:
- **Residencias Los Samanes (Condominio 1)**: Usuario: `admin` | Contraseña: `admin123`
- **Torre Residencial El Ávila (Condominio 2)**: Usuario: `admin_avila` | Contraseña: `avila123`
- **Nuevos Condominios**: El usuario y clave se definen al crear el condominio desde el Super-Admin (o se pueden editar en cualquier momento desde `superadmin_dashboard.php`).

### 3. Residentes / Copropietarios
- **URL**: `https://tudominio.com/` (Pestaña *Propietario / Apto*)
- **Selector de Condominio**: Seleccione su edificio.
- **Apartamento**: Elija su número de apartamento de la lista desplegable.
- **Contraseña Inicial por Defecto**: `123456`

---

## 🏢 Estructura de Archivos del Proyecto

```text
Condominio/
├── .env.example             # Plantilla de variables de entorno para producción
├── .htaccess                # Directivas de seguridad Apache/cPanel
├── README.md                # Documentación completa y manual de uso
├── index.php                # Pantalla principal de acceso con selector dinámico de condominio
├── superadmin_dashboard.php # Panel del Super-Admin (Gestión central de condominios)
├── admin_dashboard.php      # Panel administrativo del condominio activo
├── resident_dashboard.php   # Portal de autogestión para residentes
├── api/
│   ├── condominios.php      # API para listar, crear, conmutar y editar condominios
│   ├── apartamentos.php     # API de unidades, pisos, alícuotas y detalle
│   ├── auth.php             # API de inicio de sesión y control de roles
│   ├── configuracion.php    # API de ajustes y sincronización BCV
│   ├── cuotas.php           # API para emisión y consulta de cuotas
│   ├── pagos.php            # API de registro y auditoría de pagos
│   └── tickets.php          # API de solicitudes y quejas
├── config/
│   ├── db.php               # Conexión portable PDO con soporte para .env
│   ├── generator.php        # Generador dinámico de estructuras de apartamentos
│   ├── helpers.php          # Funciones de sesión, seguridad, tasas y respuestas JSON
│   ├── bcv_scraper.php      # Extractor en tiempo real de la tasa oficial BCV
│   ├── init_db.php          # Inicializador automático del esquema de base de datos
│   └── migrate_multicondominio.php # Script de migración multi-inquilino
├── assets/
│   ├── css/style.css        # Sistema de diseño moderno, tema oscuro y responsive
│   └── js/app.js            # Lógica cliente para SuperAdmin, Admin y Residentes
└── uploads/                 # Almacenamiento seguro de comprobantes y fotos de incidencias
```

---

## 💡 Cómo Usar este Proyecto como Base para Nuevos Desarrollos

1. **Para Probar Diferentes Condominios en un Solo Sitio**:
   - Inicie sesión como `admin` e ingrese al panel de Super-Admin.
   - Pulse **"+ Nuevo Condominio"**, defina la cantidad de pisos, apartamentos por piso, si tiene PB o PH, y los datos bancarios.
   - El sistema generará todas las unidades al instante. Puede compartir el enlace directo a sus clientes añadiendo el parámetro de URL:
     `https://tudominio.com/?c=slug-del-condominio` (ej. `?c=torre-avila`).
2. **Para Exportar una Instancia Independiente (Boilerplate)**:
   - Si un cliente desea una instalación totalmente exclusiva en su propio hosting/dominio, simplemente copie este repositorio, configure su archivo `.env` específico y cree el condominio del cliente en la plataforma.
