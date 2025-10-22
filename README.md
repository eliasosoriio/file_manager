# 📁 File Manager - Aplicación Web Symfony

Una aplicación web moderna y minimalista para gestionar archivos locales, construida con **Symfony 7**, **Tailwind CSS** y **Stimulus**.

## 🎨 Características

- ✨ **Interfaz minimalista** inspirada en Apple Files y Notion
- 📂 **Gestión completa de archivos**: crear, renombrar, mover, eliminar, abrir
- 🔍 **Búsqueda avanzada** de archivos y carpetas
- 🕒 **Historial de actividad** guardado en base de datos
- 🎯 **Drag & Drop** para mover archivos entre carpetas
- 📱 **Diseño responsivo** (desktop y móvil)
- 🎭 **Menú contextual** con clic derecho
- 💾 **Editor de archivos** integrado
- 🔐 **Seguridad**: protección contra path traversal

## 🛠️ Tecnologías

- **Backend**: Symfony 7 (PHP 8.2+)
- **Frontend**: Tailwind CSS 3, Stimulus JS
- **Base de datos**: SQLite (fácilmente cambiable a MySQL/PostgreSQL)
- **Iconos**: Lucide Icons
- **Tipografía**: Inter (Google Fonts)

## 📦 Instalación

### Requisitos previos

- PHP 8.2 o superior
- Composer
- Extensiones PHP: `pdo_sqlite`, `fileinfo`, `json`

### Pasos de instalación

1. **Clonar o navegar al directorio del proyecto**:
```bash
cd /home/sdweb/elias_osorio_files/file_manager
```

2. **Instalar dependencias de Composer** (si aún no están instaladas):
```bash
composer install
```

3. **Configurar variables de entorno**:
El archivo `.env` ya está configurado con SQLite. Si quieres usar otra base de datos, modifica la variable `DATABASE_URL`.

4. **Crear la base de datos**:
```bash
php bin/console doctrine:database:create
```

5. **Ejecutar migraciones**:
```bash
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

6. **Iniciar el servidor de desarrollo**:
```bash
symfony server:start
```

O si no tienes Symfony CLI:
```bash
php -S localhost:8000 -t public/
```

O con Docker:
```bash
docker-compose build --no-cache

docker-compose up -d

docker-compose exec app php bin/console doctrine:migrations:migrate --no-interaction

docker-compose exec app php bin/console cache:clear
```

7. **Acceder a la aplicación**:
Abre tu navegador en: `http://localhost:8000/files`
Docker: `http://localhost:8765/files` (si quieres mapea en hosts a `files.local`)

## 🗂️ Estructura del proyecto

```
/home/sdweb/elias_osorio_files/file_manager/
├── config/
│   └── packages/
│       ├── doctrine.yaml
│       └── security.yaml
├── src/
│   ├── Controller/
│   │   └── FileController.php      # Controlador principal con todas las rutas
│   ├── Entity/
│   │   └── FileHistory.php         # Entidad para historial de archivos
│   ├── Repository/
│   │   └── FileHistoryRepository.php
│   └── Service/
│       └── FileManager.php         # Servicio para operaciones de archivos
├── templates/
│   ├── base.html.twig              # Layout base con sidebar y header
│   └── files/
│       ├── index.html.twig         # Dashboard principal
│       ├── _file_item.html.twig    # Componente de tarjeta de archivo
│       ├── open.html.twig          # Vista de archivo abierto
│       ├── recent.html.twig        # Historial de archivos recientes
│       └── search.html.twig        # Resultados de búsqueda
├── public/
│   └── index.php
└── var/
    └── data.db                     # Base de datos SQLite
```

## 🚀 Uso

### Rutas disponibles

| Ruta | Método | Descripción |
|------|--------|-------------|
| `/files` | GET | Dashboard principal con listado de archivos |
| `/files?path={path}` | GET | Navegar a una carpeta específica |
| `/files/recent` | GET | Ver archivos recientes |
| `/files/search?q={query}` | GET | Buscar archivos |
| `/files/open?path={path}` | GET | Abrir/editar un archivo |
| `/files/create` | POST | Crear archivo o carpeta |
| `/files/delete` | POST | Eliminar archivo o carpeta |
| `/files/rename` | POST | Renombrar archivo o carpeta |
| `/files/move` | POST | Mover archivo o carpeta |
| `/files/copy` | POST | Copiar archivo o carpeta |
| `/files/save` | POST | Guardar contenido de archivo |
| `/files/download?path={path}` | GET | Descargar archivo |
| `/files/info?path={path}` | GET | Obtener información de archivo |

### 📂 Configurar directorio base

Cuando despliegues en otro servidor (como en tu casa), necesitas cambiar el directorio base:

#### Opción 1: Usando variables de entorno (RECOMENDADO)

Edita el archivo `.env` o crea `.env.local`:

```bash
# .env.local (no se sube a Git)
FILE_MANAGER_BASE_PATH=/ruta/completa/a/tu/directorio
```

**Ejemplos:**
```bash
# En Linux/Mac
FILE_MANAGER_BASE_PATH=/home/usuario/mis-archivos

# En Windows (con WSL o Git Bash)
FILE_MANAGER_BASE_PATH=/mnt/c/Users/usuario/Documents

# En producción
FILE_MANAGER_BASE_PATH=/var/www/archivos
```

#### Opción 2: Cambiar en services.yaml

Edita `config/services.yaml`:

```yaml
parameters:
    file_manager.base_path: '/ruta/a/tu/directorio'
```

### 🔖 Accesos directos en el sidebar

**✨ AUTOMÁTICO:** Los accesos directos se generan automáticamente a partir de las carpetas del directorio raíz.

**Características:**
- ✅ Todas las carpetas del directorio raíz aparecen automáticamente
- ✅ Ordenadas alfabéticamente
- ✅ Iconos y colores asignados inteligentemente según el nombre
- ✅ Se actualizan automáticamente al crear/eliminar carpetas

**Mapeo automático de iconos y colores:**

| Nombre de carpeta | Icono | Color |
|-------------------|-------|-------|
| `descargas`, `downloads` | 📥 download | naranja |
| `documentos`, `documents` | 📄 file-text | verde |
| `imagenes`, `images`, `pictures` | 🖼️ image | morado |
| `fotos`, `photos` | 📷 camera | rosa |
| `videos` | 🎬 video | rojo |
| `musica`, `music` | 🎵 music | rosa |
| `code`, `proyectos`, `projects` | 💻 code | azul |
| `trabajo`, `work` | 💼 briefcase | índigo |
| `libros`, `books` | 📚 book | amarillo |
| `archivos`, `archive` | 🗄️ archive | gris |
| Otras carpetas | 📂 folder | azul |

**No requiere configuración:** Simplemente crea carpetas en tu directorio base y aparecerán automáticamente.

### Directorio base

Por defecto, la aplicación gestiona archivos en:
```
/home/sdweb/elias_osorio_files
```

Para cambiar el directorio base, edita la constante `BASE_PATH` en `src/Service/FileManager.php`:

```php
private const BASE_PATH = '/tu/nuevo/directorio';
```

### Características de la interfaz

#### Barra lateral
- **Todos los archivos**: Vista principal
- **Recientes**: Historial de actividad
- **Accesos directos**: Enlaces rápidos a carpetas frecuentes

#### Acciones de archivos
- **Clic izquierdo**: Abrir archivo o carpeta
- **Clic derecho**: Menú contextual (abrir, renombrar, descargar, eliminar)
- **Drag & Drop**: Arrastrar archivos a carpetas para moverlos
- **Botón "..."**: Menú de opciones

#### Crear archivos/carpetas
- **Nuevo archivo**: Botón en la barra superior
- **Nueva carpeta**: Botón azul en la barra superior

#### Búsqueda
- Barra de búsqueda en la parte superior
- Busca en nombres de archivos y rutas
- Muestra resultados de archivos actuales e historial

## 🎨 Personalización de estilos

Los estilos están integrados en `templates/base.html.twig`. Para personalizarlos:

### Colores principales
```css
/* Fondo general */
background-color: #f9f9fb

/* Tarjetas */
background: white
border: #e5e7eb

/* Color primario (botones, enlaces) */
background: linear-gradient(135deg, #667eea 0%, #764ba2 100%)

/* Hover states */
hover:bg-gray-50
```

### Tipografía
Actualmente usa **Inter**. Para cambiar a otra fuente:

1. Modifica el `<link>` en `base.html.twig`
2. Actualiza la regla CSS `font-family`

## 🔐 Seguridad

### Protección implementada

- ✅ **Path Traversal**: Validación de rutas para prevenir acceso fuera del directorio base
- ✅ **CSRF**: Protección en formularios (automática con Symfony)
- ⚠️ **Autenticación**: Actualmente **no implementada** (ver sección siguiente)

### Agregar autenticación (opcional)

Para proteger la aplicación con login:

1. **Crear un usuario**:
```bash
php bin/console make:user
php bin/console make:auth
```

2. **Configurar security.yaml**:
```yaml
security:
    firewalls:
        main:
            lazy: true
            provider: app_user_provider
            form_login:
                login_path: app_login
                check_path: app_login
            logout:
                path: app_logout
    access_control:
        - { path: ^/files, roles: ROLE_USER }
```

3. **Crear usuario de prueba**:
```bash
php bin/console make:user
php bin/console security:hash-password
```

## 📊 Base de datos

### Tabla `file_history`

Almacena el historial de acciones sobre archivos:

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | INT | ID autoincremental |
| `filename` | VARCHAR(255) | Nombre del archivo |
| `path` | VARCHAR(500) | Ruta relativa |
| `action` | VARCHAR(50) | Acción: `opened`, `created`, `deleted`, `renamed`, `moved`, `updated` |
| `file_type` | VARCHAR(50) | Extensión del archivo |
| `file_size` | INT | Tamaño en bytes |
| `created_at` | DATETIME | Fecha de la acción |

### Consultas útiles

```bash
# Ver todas las acciones
php bin/console dbal:run-sql "SELECT * FROM file_history ORDER BY created_at DESC LIMIT 20"

# Limpiar historial antiguo
php bin/console dbal:run-sql "DELETE FROM file_history WHERE created_at < DATE('now', '-30 days')"
```

## 🐛 Solución de problemas

### Error: "Access denied: path outside base directory"
**Causa**: Intentas acceder a un archivo fuera del directorio base configurado.
**Solución**: Verifica que la ruta esté dentro de `/home/sdweb/elias_osorio_files`.

### Error: "Database not found"
**Solución**:
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### Error: "Permission denied" al crear/eliminar archivos
**Solución**: Verifica permisos del directorio:
```bash
chmod -R 755 /home/sdweb/elias_osorio_files
```

### Los iconos no aparecen
**Solución**: Verifica que tengas conexión a Internet (Lucide Icons se carga via CDN).

### Tailwind CSS no funciona
**Solución**: Verifica que el CDN de Tailwind esté cargando correctamente (requiere Internet).

## 📝 Tareas pendientes / Mejoras futuras

- [ ] Sistema de autenticación completo
- [ ] Soporte para múltiples usuarios con permisos
- [ ] Vista previa de más tipos de archivos (video, audio)
- [ ] Compartir archivos con enlaces públicos
- [ ] Compresión/descompresión de archivos ZIP
- [ ] Subida de archivos mediante drag & drop
- [ ] Editor de código con syntax highlighting
- [ ] Papelera de reciclaje
- [ ] Favoritos/marcadores de archivos
- [ ] Tema oscuro/claro

## 📄 Licencia

Proyecto de uso libre para fines educativos y personales.

## 👨‍💻 Autor

Desarrollado con ❤️ usando Symfony y Tailwind CSS.

---

**¿Necesitas ayuda?** Revisa la documentación oficial de Symfony: https://symfony.com/doc
