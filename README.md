# �️ Work Manager - Aplicación de Gestión de Trabajo y Archivos

Una aplicación web completa para gestionar archivos, tareas y generar reportes de trabajo, construida con **Symfony 6**, **Tailwind CSS** y **JavaScript moderno**.

## 🎨 Características Principales

### 📂 Gestión de Archivos
- ✨ **Interfaz minimalista** inspirada en Apple Files y Notion
- 📂 **Gestión completa de archivos**: crear, renombrar, mover, eliminar, abrir
- 🔍 **Búsqueda avanzada** de archivos y carpetas
- 🕒 **Historial de actividad** guardado en base de datos
- 🎯 **Drag & Drop** para mover archivos entre carpetas
- 📱 **Diseño responsivo** (desktop y móvil)
- 🎭 **Menú contextual** con clic derecho
- 💾 **Editor de archivos** integrado con syntax highlighting
- 📝 **Visualizador Markdown** con modo preview/edit y soporte dark mode
- 🔐 **Seguridad**: protección contra path traversal

### ✅ Gestión de Tareas
- 📋 **Sistema de tareas** con notas integradas
- ⏱️ **Control de tiempo** con cronómetro y pausas
- 📊 **Generación automática de partes de trabajo** con formato personalizado
- 📧 **Integración con Thunderbird** para envío de reportes por email
- 📋 **Copia al portapapeles** de reportes generados
- 💾 **Persistencia de datos** con localStorage para preferencias de usuario
- 📄 **Exportación a PDF** de tareas y reportes

### 💾 Backup de Base de Datos
- ☁️ **Backup automático** de la base de datos MySQL
- 📅 **Nombres personalizados** con formato fecha (dump-file_manager_YYYYMMDD.sql)
- 📂 **Visualización del directorio actual** al crear backups
- ✅ **Validación de permisos** y verificación de archivos generados

### 🎨 Diseño y UX
- 🌓 **Modo oscuro completo** para toda la aplicación
- 🖱️ **Scrollbars personalizados** acordes al tema (claro/oscuro)
- 🎯 **Iconos consistentes** con Lucide Icons
- 💫 **Animaciones fluidas** y transiciones suaves

## 🛠️ Tecnologías

- **Backend**: Symfony 6 (PHP 8.2+)
- **Frontend**: Tailwind CSS 3, JavaScript ES6+
- **Base de datos**: MySQL 8.0 (Dockerizado)
- **Contenedores**: Docker + Docker Compose (PHP-FPM, Nginx, MySQL, Supervisor)
- **Librerías JS**:
  - Marked.js (renderizado de Markdown)
  - SheetJS (XLSX) para hojas de cálculo
  - LocalStorage API (persistencia de datos)
  - Clipboard API (copiar al portapapeles)
- **Iconos**: Lucide Icons
- **Tipografía**: Inter (Google Fonts)
- **PDF**: DomPDF para generación de documentos

## 📦 Instalación

### Requisitos previos

- Docker y Docker Compose
- Git (opcional)

### Instalación con Docker (Recomendado)

1. **Clonar o navegar al directorio del proyecto**:
```bash
cd /home/sdweb/elias_osorio_files/file_manager
```

2. **Construir e iniciar los contenedores**:
```bash
docker-compose build --no-cache
docker-compose up -d
```

3. **Ejecutar migraciones de base de datos**:
```bash
docker-compose exec app php bin/console doctrine:migrations:migrate --no-interaction
```

4. **Limpiar caché**:
```bash
docker-compose exec app php bin/console cache:clear
```

5. **Acceder a la aplicación**:
   - URL: `http://localhost:8765/files`
   - Opcional: Mapear en `/etc/hosts` a `files.local` para acceder vía `http://files.local:8765/files`

### Instalación Manual (Sin Docker)

1. **Requisitos**:
   - PHP 8.2 o superior
   - Composer
   - MySQL 8.0 o superior
   - Extensiones PHP: `pdo_mysql`, `fileinfo`, `json`, `mbstring`, `xml`

2. **Instalar dependencias**:
```bash
composer install
npm install  # Para Webpack Encore (opcional)
```

3. **Configurar variables de entorno**:
   - Copia `.env` a `.env.local`
   - Configura `DATABASE_URL` con tus credenciales MySQL:
```bash
DATABASE_URL="mysql://usuario:contraseña@127.0.0.1:3306/file_manager"
```

4. **Crear base de datos y ejecutar migraciones**:
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

5. **Iniciar servidor**:
```bash
symfony server:start
# O: php -S localhost:8000 -t public/
```

6. **Acceder**: `http://localhost:8000/files`

## 🗂️ Estructura del proyecto

```
/home/sdweb/elias_osorio_files/file_manager/
├── config/
│   ├── packages/                   # Configuración de bundles
│   │   ├── doctrine.yaml
│   │   ├── framework.yaml
│   │   ├── security.yaml
│   │   └── twig.yaml
│   ├── routes.yaml                 # Rutas principales
│   └── services.yaml               # Contenedor de servicios
├── docker/
│   ├── nginx.conf                  # Configuración Nginx
│   └── supervisord.conf            # Supervisor para procesos
├── docker-compose.yml              # Orquestación de contenedores
├── Dockerfile                      # Imagen PHP-FPM con extensiones
├── src/
│   ├── Controller/
│   │   ├── FileController.php     # Gestión de archivos y backups
│   │   └── TaskController.php     # Gestión de tareas y reportes
│   ├── Entity/
│   │   ├── FileHistory.php        # Historial de archivos
│   │   ├── Note.php               # Notas de tareas
│   │   └── Task.php               # Tareas con tiempo
│   ├── Repository/
│   │   ├── FileHistoryRepository.php
│   │   ├── NoteRepository.php
│   │   └── TaskRepository.php
│   ├── Service/
│   │   └── FileManager.php        # Operaciones de archivos
│   └── Twig/
│       └── AppExtension.php       # Extensiones Twig personalizadas
├── templates/
│   ├── base.html.twig             # Layout con navbar, modals y estilos
│   ├── files/
│   │   ├── index.html.twig        # Explorador de archivos
│   │   ├── _file_item.html.twig   # Componente de archivo
│   │   ├── open.html.twig         # Editor/Visor (Markdown, PDF, texto)
│   │   ├── recent.html.twig       # Archivos recientes
│   │   └── search.html.twig       # Buscador de archivos
│   └── tasks/
│       ├── index.html.twig        # Gestión de tareas con cronómetro
│       └── pdf.html.twig          # Plantilla PDF de reportes
├── migrations/                     # Migraciones de base de datos
├── public/
│   └── index.php                  # Front controller
└── var/
    ├── cache/                     # Caché de Symfony
    └── log/                       # Logs de la aplicación
```

## 🚀 Uso

### 🔀 Rutas disponibles

#### Gestión de Archivos

| Ruta | Método | Descripción |
|------|--------|-------------|
| `/files` | GET | Dashboard principal con listado de archivos |
| `/files?path={path}` | GET | Navegar a una carpeta específica |
| `/files/recent` | GET | Ver archivos recientes con historial |
| `/files/search?q={query}` | GET | Buscar archivos por nombre |
| `/files/open?path={path}` | GET | Abrir/editar archivo (soporta .md, .txt, .pdf, etc) |
| `/files/create` | POST | Crear archivo o carpeta |
| `/files/delete` | POST | Eliminar archivo o carpeta |
| `/files/rename` | POST | Renombrar archivo o carpeta |
| `/files/move` | POST | Mover archivo o carpeta |
| `/files/copy` | POST | Copiar archivo o carpeta |
| `/files/save` | POST | Guardar contenido de archivo editado |
| `/files/download?path={path}` | GET | Descargar archivo |
| `/files/info?path={path}` | GET | Obtener información de archivo (JSON) |
| `/files/backup` | POST | Crear backup de base de datos MySQL |

#### Gestión de Tareas

| Ruta | Método | Descripción |
|------|--------|-------------|
| `/tasks` | GET | Lista de tareas con cronómetro y notas |
| `/tasks/create` | POST | Crear nueva tarea |
| `/tasks/update/{id}` | POST | Actualizar tarea existente |
| `/tasks/delete/{id}` | POST | Eliminar tarea |
| `/tasks/start/{id}` | POST | Iniciar cronómetro de tarea |
| `/tasks/pause/{id}` | POST | Pausar cronómetro de tarea |
| `/tasks/stop/{id}` | POST | Detener cronómetro de tarea |
| `/tasks/add-note/{id}` | POST | Añadir nota a tarea |
| `/tasks/pdf` | POST | Generar PDF de reporte de trabajo |

### 📂 Configurar directorio base

El directorio base determina dónde se gestionan los archivos. Por defecto:
```
/home/sdweb/elias_osorio_files
```

Para cambiar el directorio base, edita directamente en `src/Service/FileManager.php`:

```php
private const BASE_PATH = '/tu/nuevo/directorio';
```

**Ejemplos de configuración:**
```php
// Linux/Mac
private const BASE_PATH = '/home/usuario/mis-archivos';

// Producción
private const BASE_PATH = '/var/www/documentos';

// Con Docker (montando volumen)
private const BASE_PATH = '/app/files';
```

> **Nota**: Si usas Docker, asegúrate de montar el directorio como volumen en `docker-compose.yml`

### ☁️ Realizar Backup de Base de Datos

1. **Accede a la sección de Archivos**
2. **Haz clic en el botón de nube** (☁️) en la barra superior
3. **Personaliza el nombre del archivo** (por defecto: `dump-file_manager_YYYYMMDD.sql`)
4. **Haz clic en "Crear Backup"**
5. El archivo se guardará en el directorio actual que estés navegando

**Requisitos**: El contenedor Docker debe tener `mysqldump` instalado (ya incluido en el Dockerfile)

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

### 🎯 Características de la interfaz

#### 🗂️ Navegación Principal
- **Archivos**: Explorador de archivos con vista de tarjetas
- **Tareas**: Gestor de tareas con cronómetro
- **Recientes**: Historial de archivos accedidos

#### 📂 Gestión de Archivos
- **Clic izquierdo**: Abrir archivo o carpeta
- **Clic derecho**: Menú contextual (abrir, renombrar, descargar, eliminar)
- **Drag & Drop**: Arrastrar archivos a carpetas para moverlos
- **Botón "..."**: Menú de opciones adicionales
- **Crear archivo/carpeta**: Botones en la barra superior
- **Búsqueda**: Barra de búsqueda en tiempo real
- **Backup BD**: Botón de nube para exportar base de datos

#### 📝 Visualizador de Archivos
Soporte para múltiples formatos:

- **Archivos Markdown (.md, .markdown)**:
  - Vista previa renderizada con estilos
  - Modo edición con sintaxis
  - Toggle entre preview/edit
  - Soporte completo para dark mode
  - Renderizado con Marked.js (GFM, tablas, código)

- **Archivos PDF**:
  - Visualización en iframe embebido
  - Opción de descarga

- **Hojas de cálculo (.xlsx, .xls, .ods, .xlsm, .xlsb)**:
  - Visualización en tabla HTML con estilos
  - Soporte para múltiples hojas (selector)
  - Formato automático de números, fechas y booleanos
  - Información de filas, columnas y celdas totales
  - Scroll horizontal y vertical para grandes tablas
  - Soporte completo para dark mode
  - Renderizado con SheetJS (xlsx)

- **Archivos de texto** (.txt, .log, .json, etc):
  - Editor de texto plano
  - Guardado automático

#### ✅ Gestión de Tareas

1. **Crear tarea**:
   - Haz clic en "Nueva Tarea"
   - Define nombre y descripción
   - Añade notas durante el trabajo

2. **Control de tiempo**:
   - **Iniciar** ▶️: Comienza el cronómetro
   - **Pausar** ⏸️: Pausa temporal (mantiene tiempo acumulado)
   - **Detener** ⏹️: Finaliza la tarea

3. **Generar reporte de trabajo**:
   - Haz clic en "Generar Parte de Trabajo"
   - Introduce tu nombre y apellido
   - Define las próximas acciones
   - Opciones:
     - **Copiar**: Copia el reporte al portapapeles
     - **Email**: Abre Thunderbird con el reporte pre-cargado
   - Los datos se guardan automáticamente en localStorage

**Formato del reporte:**
```
Tareas realizadas:
• Tarea 1 (2h 30m)
  - Nota 1
  - Nota 2
• Tarea 2 (1h 15m)

Próximas acciones:
• Acción 1
• Acción 2

Saludo: Nombre Apellido
```

**Email automático:**
- Para: jchamorro@sdweb.es; jsanchez@sdweb.es
- Asunto: `Sdweb - Interno - Parte trabajo - Nombre Apellido - DD/MM/YYYY`

## 🎨 Personalización y Temas

### 🌓 Modo Oscuro

La aplicación soporta modo oscuro automático basado en las preferencias del sistema:

```css
/* Modo claro */
--bg-primary: #ffffff
--text-primary: #1f2937

/* Modo oscuro (automático con @media prefers-color-scheme: dark) */
--bg-primary: #1a202c
--text-primary: #e2e8f0
```

**Componentes con dark mode:**
- ✅ Explorador de archivos
- ✅ Editor/Visor de archivos
- ✅ Visualizador Markdown con prose styles
- ✅ Gestor de tareas
- ✅ Modales y menús contextuales
- ✅ Scrollbars personalizados

### 🖱️ Scrollbars Personalizados

**Modo claro:**
- Fondo: `#f1f5f9`
- Thumb: `#cbd5e1`

**Modo oscuro:**
- Fondo: `#1a202c`
- Thumb: `#4a5568`

### Colores del tema

```css
/* Color primario (gradiente púrpura) */
background: linear-gradient(135deg, #667eea 0%, #764ba2 100%)

/* Estados */
--color-success: #10b981  /* Verde */
--color-warning: #f59e0b  /* Naranja */
--color-danger: #ef4444   /* Rojo */
--color-info: #3b82f6     /* Azul */
```

### Tipografía

- **Fuente principal**: Inter (Google Fonts)
- **Monospace**: `ui-monospace, 'Cascadia Code', 'Source Code Pro', monospace`

Para cambiar:
1. Edita el `<link>` de Google Fonts en `templates/base.html.twig`
2. Actualiza `font-family` en los estilos CSS

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

### Esquema de tablas

#### `file_history`
Historial de acciones sobre archivos:

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | INT | ID autoincremental |
| `filename` | VARCHAR(255) | Nombre del archivo |
| `path` | VARCHAR(500) | Ruta relativa |
| `action` | VARCHAR(50) | Acción: `opened`, `created`, `deleted`, `renamed`, `moved`, `updated` |
| `file_type` | VARCHAR(50) | Extensión del archivo |
| `file_size` | INT | Tamaño en bytes |
| `created_at` | DATETIME | Fecha de la acción |

#### `task`
Tareas con control de tiempo:

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | INT | ID autoincremental |
| `name` | VARCHAR(255) | Nombre de la tarea |
| `description` | TEXT | Descripción detallada |
| `total_seconds` | INT | Tiempo total en segundos |
| `is_running` | BOOLEAN | Si está en ejecución |
| `started_at` | DATETIME | Inicio del cronómetro actual |
| `paused_seconds` | INT | Segundos acumulados en pausa |
| `created_at` | DATETIME | Fecha de creación |
| `updated_at` | DATETIME | Última actualización |

#### `note`
Notas asociadas a tareas:

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | INT | ID autoincremental |
| `task_id` | INT | Relación con tabla `task` |
| `content` | TEXT | Contenido de la nota |
| `created_at` | DATETIME | Fecha de creación |

### Consultas útiles

```bash
# Conectar al contenedor
docker-compose exec app bash

# Ver historial de archivos recientes
php bin/console dbal:run-sql "SELECT * FROM file_history ORDER BY created_at DESC LIMIT 20"

# Ver tareas activas
php bin/console dbal:run-sql "SELECT * FROM task WHERE is_running = 1"

# Ver tiempo total trabajado por tarea
php bin/console dbal:run-sql "SELECT name, total_seconds/3600 as hours FROM task ORDER BY total_seconds DESC"

# Limpiar historial antiguo (más de 30 días)
php bin/console dbal:run-sql "DELETE FROM file_history WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)"

# Backup manual de base de datos
docker-compose exec db mysqldump -u root -p file_manager > backup.sql
```

## 🐛 Solución de problemas

### 🐳 Problemas con Docker

#### Los contenedores no inician
```bash
# Ver logs
docker-compose logs -f

# Reiniciar contenedores
docker-compose down
docker-compose up -d

# Reconstruir desde cero
docker-compose down -v
docker-compose build --no-cache
docker-compose up -d
```

#### Error de conexión a base de datos
```bash
# Verificar que el contenedor de MySQL esté corriendo
docker-compose ps

# Verificar logs de MySQL
docker-compose logs db

# Reiniciar solo el servicio de BD
docker-compose restart db
```

#### Error: "mysqldump command not found"
**Causa**: El contenedor no tiene mysql-client instalado.
**Solución**:
```bash
# Reconstruir el contenedor con el Dockerfile actualizado
docker-compose build --no-cache app
docker-compose up -d
```

### 📂 Problemas con archivos

#### Error: "Access denied: path outside base directory"
**Causa**: Intentas acceder a un archivo fuera del directorio base.
**Solución**: Verifica la configuración de `BASE_PATH` en `src/Service/FileManager.php`

#### Error: "Permission denied" al crear/eliminar archivos
**Solución en Docker**:
```bash
# Ajustar permisos dentro del contenedor
docker-compose exec app chown -R www-data:www-data /home/sdweb/elias_osorio_files
```

**Solución sin Docker**:
```bash
chmod -R 755 /home/sdweb/elias_osorio_files
```

#### El backup de BD no se crea
1. Verifica que `mysqldump` esté disponible:
```bash
docker-compose exec app which mysqldump
```
2. Verifica permisos del directorio de destino
3. Revisa los logs de PHP para errores:
```bash
docker-compose logs app
```

### 🎨 Problemas de interfaz

#### Los iconos no aparecen
**Causa**: No hay conexión a Internet (Lucide Icons se carga via CDN).
**Solución**: Verifica tu conexión o descarga los iconos localmente.

#### El modo oscuro no funciona
**Solución**: Verifica las preferencias de tu sistema operativo. El dark mode se activa automáticamente con `prefers-color-scheme: dark`.

#### Los scrollbars no se ven personalizados
**Causa**: Algunos navegadores no soportan `::-webkit-scrollbar`.
**Compatibilidad**: Funciona en Chrome, Edge, Safari. Firefox usa `scrollbar-color`.

### ⚙️ Problemas con tareas

#### El cronómetro no se actualiza
1. Abre la consola del navegador (F12)
2. Busca errores JavaScript
3. Verifica que la tarea tenga `is_running = 1` en la base de datos

#### El reporte de trabajo no se copia al portapapeles
**Causa**: El navegador bloquea el acceso al portapapeles.
**Solución**: Permite el acceso al portapapeles en la configuración del navegador o usa HTTPS.

#### Thunderbird no se abre con el email
**Causa**: Thunderbird no está configurado como cliente de email predeterminado.
**Solución**: Configura Thunderbird como cliente predeterminado en tu sistema operativo.

### 🗃️ Problemas con migraciones

#### Error: "Database does not exist"
```bash
docker-compose exec app php bin/console doctrine:database:create
docker-compose exec app php bin/console doctrine:migrations:migrate
```

#### Error: "Migration already executed"
```bash
# Ver estado de migraciones
docker-compose exec app php bin/console doctrine:migrations:status

# Marcar migración como ejecutada manualmente
docker-compose exec app php bin/console doctrine:migrations:version --add --all
```

## � Características Implementadas

- ✅ **Gestión completa de archivos** (crear, editar, mover, eliminar, copiar)
- ✅ **Visualizador Markdown** con preview/edit mode
- ✅ **Visualizador de hojas de cálculo** (Excel/LibreOffice Calc)
- ✅ **Soporte para PDF** en iframe
- ✅ **Editor de texto** integrado
- ✅ **Sistema de tareas** con cronómetro y control de tiempo
- ✅ **Generación de reportes de trabajo** con formato personalizado
- ✅ **Integración con Thunderbird** para envío de emails
- ✅ **Backup de base de datos** con mysqldump
- ✅ **Modo oscuro completo** con scrollbars personalizados
- ✅ **Historial de archivos** con seguimiento de acciones
- ✅ **Búsqueda de archivos** en tiempo real
- ✅ **Drag & Drop** para mover archivos
- ✅ **Menú contextual** con clic derecho
- ✅ **Diseño responsivo** mobile-friendly
- ✅ **Persistencia de datos** con localStorage
- ✅ **Docker** con docker-compose completo

## 📝 Roadmap / Mejoras futuras

### 🔐 Seguridad y usuarios
- [ ] Sistema de autenticación con login
- [ ] Soporte para múltiples usuarios con roles
- [ ] Permisos por archivo/carpeta
- [ ] Logs de auditoría de acciones

### 📂 Gestión de archivos avanzada
- [ ] Vista previa de videos y audio
- [ ] Compresión/descompresión de archivos ZIP
- [ ] Subida de archivos mediante drag & drop desde escritorio
- [ ] Papelera de reciclaje temporal
- [ ] Favoritos/marcadores de archivos
- [ ] Etiquetas y categorías personalizadas
- [ ] Versionado de archivos
- [ ] Compartir archivos con enlaces públicos temporales

### 💻 Playground de código (Planificado)
- [ ] Editor HTML/CSS/JS con live preview
- [ ] Tres paneles con editores independientes
- [ ] Vista previa en tiempo real con iframe
- [ ] Persistencia con localStorage
- [ ] Opción de exportar snippets
- [ ] Integración con CodeMirror o Monaco Editor

### 📊 Reportes y análisis
- [ ] Dashboard con estadísticas de uso
- [ ] Gráficos de tiempo trabajado por proyecto
- [ ] Exportación de reportes a diferentes formatos
- [ ] Calendarios de actividad
- [ ] Comparativas mensuales/semanales

### 🎨 Interfaz y UX
- [ ] Temas de color personalizables
- [ ] Atajos de teclado avanzados
- [ ] Vista en lista vs cuadrícula
- [ ] Previsualización de archivos al hover
- [ ] Breadcrumbs mejorados con navegación rápida

### 🔧 Integraciones
- [ ] API REST para integraciones externas
- [ ] Webhooks para notificaciones
- [ ] Integración con servicios cloud (Google Drive, Dropbox)
- [ ] Sincronización con calendarios (Google Calendar, Outlook)
- [ ] Notificaciones push
- [ ] Exportación automática de reportes por email

## � Comandos útiles

### Docker
```bash
# Iniciar aplicación
docker-compose up -d

# Ver logs en tiempo real
docker-compose logs -f app

# Acceder al contenedor
docker-compose exec app bash

# Reiniciar servicios
docker-compose restart

# Detener aplicación
docker-compose down

# Limpiar todo (contenedores, volúmenes, imágenes)
docker-compose down -v --rmi all
```

### Symfony
```bash
# Limpiar caché
docker-compose exec app php bin/console cache:clear

# Ver rutas disponibles
docker-compose exec app php bin/console debug:router

# Crear nueva migración
docker-compose exec app php bin/console make:migration

# Ejecutar migraciones
docker-compose exec app php bin/console doctrine:migrations:migrate

# Ver estado de migraciones
docker-compose exec app php bin/console doctrine:migrations:status

# Crear nueva entidad
docker-compose exec app php bin/console make:entity

# Ver servicios disponibles
docker-compose exec app php bin/console debug:container
```

### Base de datos
```bash
# Backup manual
docker-compose exec db mysqldump -u root -proot file_manager > backup_$(date +%Y%m%d).sql

# Restaurar backup
docker-compose exec -T db mysql -u root -proot file_manager < backup.sql

# Acceder a MySQL CLI
docker-compose exec db mysql -u root -proot file_manager

# Ver tablas
docker-compose exec db mysql -u root -proot -e "SHOW TABLES" file_manager

# Resetear base de datos
docker-compose exec app php bin/console doctrine:schema:drop --force
docker-compose exec app php bin/console doctrine:migrations:migrate
```

## 📚 Recursos y documentación

- **Symfony**: https://symfony.com/doc
- **Doctrine ORM**: https://www.doctrine-project.org/projects/doctrine-orm/en/current/index.html
- **Twig**: https://twig.symfony.com/doc/
- **Tailwind CSS**: https://tailwindcss.com/docs
- **Lucide Icons**: https://lucide.dev/
- **Marked.js**: https://marked.js.org/
- **Docker**: https://docs.docker.com/

## 📄 Licencia

Proyecto de uso interno para Sdweb. Desarrollado para gestión de archivos y seguimiento de trabajo.

## 👨‍💻 Autor

Desarrollado con ❤️ por **Elías Osorio** usando:
- Symfony 6
- Tailwind CSS
- Docker
- MySQL
- JavaScript ES6+

---

## 📞 Soporte

Para dudas o problemas:
1. Revisa la sección de **Solución de problemas**
2. Consulta los logs: `docker-compose logs -f`
3. Verifica la documentación oficial de Symfony

**Versión**: 1.0.0
**Última actualización**: Octubre 2025
