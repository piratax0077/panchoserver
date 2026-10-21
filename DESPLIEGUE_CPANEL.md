# Despliegue en cPanel

La estructura esperada es:

```text
/home/USUARIO/
├── panchoserver/       # app, bootstrap, config, vendor, storage, etc.
└── public_html/        # contenido de public_original
```

## Archivos publicos

Copiar **el contenido** de `public_original` a `public_html` (no la carpeta contenedora). Deben existir, entre otros:

```text
public_html/index.php
public_html/.htaccess
public_html/css/app.css
public_html/css/dev.css
public_html/js/app.js
```

El `index.php` incluido localiza automáticamente la carpeta hermana `panchoserver` y configura `public_html` como el directorio público real.

## Variables de producción

En `panchoserver/.env`:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://panchoserver.cl
ASSET_URL=https://panchoserver.cl
```

No agregar `/public`, `/public_original` ni `/public_html` a estas URL.

## Archivos de storage

Las imágenes usadas por `asset('storage/...')` deben estar realmente en `panchoserver/storage/app/public`. Este contenido no viene normalmente en Git y debe restaurarse desde el servidor anterior o un respaldo.

Desde la Terminal de cPanel, crear el enlace público. Si ya existe una carpeta `public_html/storage` vacía, eliminar solamente esa carpeta antes de ejecutar el comando:

```bash
cd /home/USUARIO/public_html
ln -s ../panchoserver/storage/app/public storage
```

El enlace esperado es:

```text
public_html/storage -> ../panchoserver/storage/app/public
```

Si el hosting no permite enlaces simbólicos, copiar el contenido de `panchoserver/storage/app/public/` a `public_html/storage/`. En ese caso habrá que repetir la copia cuando se agreguen archivos.

## Limpiar cachés

Después de subir los cambios:

```bash
cd /home/USUARIO/panchoserver
php artisan optimize:clear
php artisan config:cache
php artisan view:cache
```

Finalmente comprobar directamente:

- `https://panchoserver.cl/css/app.css`
- `https://panchoserver.cl/js/app.js`
- `https://panchoserver.cl/storage/imagenes/fondoLogin.jpg`

Las tres direcciones deben responder `200`.
