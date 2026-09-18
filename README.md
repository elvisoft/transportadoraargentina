# Transportadora Argentina

Sistema de gestión para empresas de transporte (TMS - Transport Management System), desarrollado con **Laravel 13** y **Livewire 4**.

## Descripción

Transportadora Argentina permite administrar la operación diaria de una empresa de transporte de cargas, centralizando el control de:

- **Choferes**: datos personales, contacto, número y categoría de licencia de conducir, vencimiento de la licencia y estado (activo/inactivo). El sistema detecta automáticamente licencias vencidas o próximas a vencer.
- **Vehículos**: patente, marca, modelo, año y tipo (camión, acoplado, semirremolque, utilitario, etc.), asignación a un chofer, vencimiento de RTO (revisión técnica obligatoria) y seguro, y estado (activo, en mantenimiento, inactivo).
- **Gastos**: registro de gastos asociados a vehículos y/o choferes, clasificados por tipo (combustible, peaje, reparación, viático, otro), con monto y fecha.

El acceso a la aplicación requiere autenticación e incluye verificación de email, autenticación de dos factores (2FA) y soporte para passkeys, mediante **Laravel Fortify**.

## Requisitos

- PHP >= 8.3
- Composer
- Node.js y npm
- Base de datos MySQL (>= 5.7 / MariaDB >= 10.3)
- Extensiones PHP habituales de Laravel (`pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`)

## Instalación

### 1. Clonar el repositorio

```bash
git clone https://github.com/elvisoft/transportadoraargentina.git
cd transportadoraargentina
```

### 2. Instalar dependencias de PHP y Node

```bash
composer install
npm install
```

### 3. Configurar el archivo de entorno

```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configurar la base de datos MySQL

Creá una base de datos vacía en MySQL:

```sql
CREATE DATABASE transportadoraargentina CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Editá el archivo `.env` y configurá la conexión a MySQL:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=transportadoraargentina
DB_USERNAME=root
DB_PASSWORD=tu_contraseña
```

### 5. Ejecutar las migraciones

```bash
php artisan migrate
```

Si querés cargar datos de ejemplo (choferes, vehículos y gastos de prueba):

```bash
php artisan migrate --seed
```

### 6. Compilar los assets del frontend

Para desarrollo:

```bash
npm run dev
```

Para producción:

```bash
npm run build
```

### 7. Levantar el servidor

```bash
php artisan serve
```

La aplicación quedará disponible en `http://localhost:8000`.

> Alternativa: podés ejecutar `composer run dev` para levantar en simultáneo el servidor de Laravel, la cola de trabajos y Vite en modo desarrollo.

## Tests

El proyecto usa Pest para testing:

```bash
php artisan test --compact
```

## Stack tecnológico

- [Laravel 13](https://laravel.com)
- [Livewire 4](https://livewire.laravel.com) + [Flux UI](https://fluxui.dev)
- [Laravel Fortify](https://laravel.com/docs/fortify) (autenticación, 2FA, passkeys)
- [Tailwind CSS 4](https://tailwindcss.com)
- [Pest](https://pestphp.com) (testing)
- MySQL como base de datos
