# 🔧 Guía de Configuración y Despliegue

## 📦 Desplegar en otro servidor (Git)

### 1️⃣ Subir a Git

```bash
# En tu servidor actual
cd /home/sdweb/elias_osorio_files/file_manager

# Inicializar Git (si no lo has hecho)
git init
git add .
git commit -m "Initial commit: File Manager application"

# Conectar con tu repositorio remoto
git remote add origin https://github.com/tu-usuario/file-manager.git
git push -u origin main
```

**IMPORTANTE**: Asegúrate de que `.env` esté en `.gitignore` (ya debería estarlo):

```bash
# Verificar
cat .gitignore | grep .env
```

### 2️⃣ Clonar en tu servidor de casa

```bash
# En tu servidor de casa
cd /ruta/donde/quieras/instalarlo
git clone https://github.com/tu-usuario/file-manager.git
cd file-manager
```

### 3️⃣ Configurar el nuevo entorno

```bash
# Instalar dependencias
composer install

# Copiar configuración de ejemplo
cp .env.example .env.local

# Editar configuración
nano .env.local
```

**Edita `.env.local` con tus valores:**

```bash
# Genera un APP_SECRET aleatorio
APP_SECRET=$(openssl rand -hex 32)

# Configura tu directorio base
FILE_MANAGER_BASE_PATH=/ruta/absoluta/en/tu/casa

# Ejemplo:
FILE_MANAGER_BASE_PATH=/home/tu-usuario/archivos
```

### 4️⃣ Configurar base de datos

```bash
# Crear base de datos
php bin/console doctrine:database:create

# Ejecutar migraciones
php bin/console doctrine:migrations:migrate
```

### 5️⃣ Configurar permisos

```bash
# Dar permisos de escritura a directorios necesarios
chmod -R 775 var/
chown -R www-data:www-data var/

# Dar permisos al directorio de archivos
chmod -R 755 /ruta/a/tus/archivos
chown -R www-data:www-data /ruta/a/tus/archivos
```

### 6️⃣ Iniciar servidor

```bash
# Desarrollo
symfony server:start

# O con PHP
php -S localhost:8000 -t public/
```

---

## 🔧 Configuraciones personalizadas

### Cambiar directorio base

**Opción 1: Variable de entorno (recomendado)**

Edita `.env.local`:
```bash
FILE_MANAGER_BASE_PATH=/nueva/ruta/absoluta
```

**Opción 2: Directo en services.yaml**

Edita `config/services.yaml`:
```yaml
parameters:
    file_manager.base_path: '/nueva/ruta/absoluta'
```

### Añadir accesos directos en el sidebar

Edita `config/services.yaml`:

```yaml
parameters:
    file_manager.shortcuts:
        # Estructura: { name: 'Nombre', path: 'ruta-relativa', icon: 'icono-lucide', color: 'color' }

        - { name: 'Mis Proyectos', path: 'proyectos', icon: 'code', color: 'blue' }
        - { name: 'Documentos', path: 'documentos', icon: 'file-text', color: 'green' }
        - { name: 'Fotos', path: 'fotos', icon: 'camera', color: 'purple' }
        - { name: 'Videos', path: 'videos', icon: 'video', color: 'red' }
        - { name: 'Descargas', path: 'descargas', icon: 'download', color: 'orange' }
        - { name: 'Música', path: 'musica', icon: 'music', color: 'pink' }
```

**Iconos disponibles**: https://lucide.dev/icons/
**Colores disponibles**: `blue`, `green`, `purple`, `red`, `orange`, `pink`, `yellow`, `indigo`, `gray`

### Cambiar base de datos

Edita `.env.local`:

**Para MySQL:**
```bash
DATABASE_URL="mysql://usuario:contraseña@127.0.0.1:3306/file_manager?serverVersion=8.0&charset=utf8mb4"
```

**Para PostgreSQL:**
```bash
DATABASE_URL="postgresql://usuario:contraseña@127.0.0.1:5432/file_manager?serverVersion=15&charset=utf8"
```

Luego ejecuta:
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

---

## 🌐 Despliegue en producción

### Con Apache

Crea un VirtualHost:

```apache
<VirtualHost *:80>
    ServerName file-manager.tudominio.com
    DocumentRoot /var/www/file-manager/public

    <Directory /var/www/file-manager/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/file-manager-error.log
    CustomLog ${APACHE_LOG_DIR}/file-manager-access.log combined
</VirtualHost>
```

```bash
# Habilitar mod_rewrite
sudo a2enmod rewrite

# Reiniciar Apache
sudo systemctl restart apache2
```

### Con Nginx

```nginx
server {
    listen 80;
    server_name file-manager.tudominio.com;
    root /var/www/file-manager/public;

    location / {
        try_files $uri /index.php$is_args$args;
    }

    location ~ ^/index\.php(/|$) {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
    }

    location ~ \.php$ {
        return 404;
    }
}
```

### Optimizar para producción

```bash
# Cambiar a modo producción
APP_ENV=prod

# Instalar dependencias sin dev
composer install --no-dev --optimize-autoloader

# Limpiar cache
php bin/console cache:clear --env=prod

# Compilar assets (si usas Webpack Encore)
npm run build
```

---

## 🔐 Seguridad

### Generar APP_SECRET seguro

```bash
# Linux/Mac
openssl rand -hex 32

# O usando PHP
php -r "echo bin2hex(random_bytes(32)) . PHP_EOL;"
```

### Proteger directorios sensibles

```apache
# En .htaccess (Apache)
<DirectoryMatch "^/.*/var/">
    Require all denied
</DirectoryMatch>
```

### Limitar acceso por IP (opcional)

```apache
# En .htaccess
<RequireAll>
    Require ip 192.168.1.0/24
    Require ip 10.0.0.0/8
</RequireAll>
```

---

## 📊 Mantenimiento

### Limpiar historial antiguo

```bash
# Eliminar registros mayores a 30 días
php bin/console dbal:run-sql "DELETE FROM file_history WHERE created_at < DATE('now', '-30 days')"
```

### Vaciar cache

```bash
php bin/console cache:clear
```

### Ver logs

```bash
tail -f var/log/dev.log
tail -f var/log/prod.log
```

---

## ❓ Solución de problemas

### Error: "Access denied: path outside base directory"

**Causa**: Ruta mal configurada o acceso fuera del directorio permitido
**Solución**: Verifica que `FILE_MANAGER_BASE_PATH` esté correctamente configurado

### Error: Permisos denegados

```bash
# Dar permisos correctos
sudo chown -R www-data:www-data /ruta/directorio
sudo chmod -R 755 /ruta/directorio
```

### No aparecen los accesos directos

**Solución**: Limpia la cache
```bash
php bin/console cache:clear
```

### Iconos no cargan

**Causa**: Requiere conexión a internet (CDN de Lucide)
**Solución**: Si no tienes internet, descarga Lucide localmente

---

## 📝 Checklist de despliegue

- [ ] Clonar repositorio
- [ ] `composer install`
- [ ] Copiar `.env.example` a `.env.local`
- [ ] Configurar `FILE_MANAGER_BASE_PATH`
- [ ] Configurar `DATABASE_URL`
- [ ] Generar `APP_SECRET` seguro
- [ ] Crear base de datos: `php bin/console doctrine:database:create`
- [ ] Ejecutar migraciones: `php bin/console doctrine:migrations:migrate`
- [ ] Configurar permisos de directorios
- [ ] Configurar accesos directos en `services.yaml`
- [ ] Iniciar servidor o configurar Apache/Nginx
- [ ] Probar acceso a `/files`
- [ ] Verificar operaciones CRUD

---

¿Necesitas más ayuda? Consulta el [README.md](README.md) principal.
