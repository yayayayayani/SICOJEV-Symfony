# SICOJEV-Symfony

Sistema Contable para la Sociedad de Jóvenes de la Iglesia Esperanza Viva.

## Preparación local

Requisitos: PHP 8.2 o superior, Composer y una base de datos compatible con las migraciones del proyecto (MySQL/MariaDB).

Desde la carpeta `app`:

```sh
composer install
```

Configura `DATABASE_URL` y un `APP_SECRET` propio en `app/.env.local`. Ese archivo es local y está excluido de Git. Después ejecuta:

```sh
php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:migrations:migrate
php bin/console app:inicializar-sistema
php -S 127.0.0.1:8000 -t public
```

El comando de inicialización solicita los datos del administrador y crea los roles. No necesitas ejecutarlo si ya tienes una cuenta. Abre `http://127.0.0.1:8000/login`.

## Acceso y permisos

- Se puede iniciar sesión con correo electrónico o nombre de usuario y contraseña.
- El correo se compara sin distinguir mayúsculas y se ignoran espacios en los extremos.
- Si un identificador coincide con varias cuentas, se rechaza el acceso con ese identificador; hay que corregir los correos duplicados. El acceso por usuario sigue disponible si es inequívoco.
- Las cuentas y los roles deben estar activos (`A`) para iniciar sesión.
- Administrador y Tesorero pueden crear, editar y eliminar actividades. Consulta puede verlas.

## Logo de la sociedad

Guarda la imagen original en `app/public/images/logo-sjiev.png` (también se admite `.webp`, `.jpg` o `.jpeg` con el mismo nombre). Aparecerá automáticamente en el acceso, el menú y la bienvenida, conservando la imagen completa. Incluye el archivo en Git para compartirlo con el equipo. Si falta, se mantiene la marca de texto sin imágenes rotas.

## Verificación

Desde `app`:

```sh
php tests/regression.php
php tests/login_identifier.php
php bin/console lint:container
php bin/console lint:twig templates
php bin/console lint:yaml config
```

La prueba de identificadores necesita `pdo_sqlite` y utiliza una base en memoria; no modifica la base local.

## Actualización del equipo

Después de descargar los cambios, ejecuta `composer install`, `php bin/console doctrine:migrations:migrate` y `php bin/console cache:clear` desde `app`. Conserva tu configuración local y recarga el navegador con Ctrl + F5 para actualizar los estilos.
