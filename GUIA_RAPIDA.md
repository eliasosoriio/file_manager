# 🚀 RESUMEN RÁPIDO: Desplegar File Manager en otro servidor

## ⚡ Pasos rápidos

### 1. En tu servidor actual (subir a Git)
```bash
git init
git add .
git commit -m "File Manager App"
git remote add origin https://github.com/tu-usuario/file-manager.git
git push -u origin main
```

### 2. En tu casa (clonar y configurar)
```bash
# Clonar
git clone https://github.com/tu-usuario/file-manager.git
cd file-manager

# Instalar
composer install

# Configurar
cp .env.example .env.local
nano .env.local  # Editar configuración
```

### 3. Configurar `.env.local`
```bash
# Cambiar estas dos líneas:
FILE_MANAGER_BASE_PATH=/ruta/completa/a/tus/archivos  # ⬅️ LO MÁS IMPORTANTE
APP_SECRET=$(openssl rand -hex 32)  # Genera uno aleatorio
```

### 4. Base de datos y permisos
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
chmod -R 775 var/
```

### 5. Iniciar
```bash
symfony server:start
# O: php -S localhost:8000 -t public/
```

**¡Listo!** Abre: `http://localhost:8000/files`

---

## 📁 ¿Dónde configurar el directorio de archivos?

**Archivo**: `.env.local` (en la raíz del proyecto)

```bash
FILE_MANAGER_BASE_PATH=/home/tu-usuario/tus-archivos
```

**Ejemplos:**
- Linux: `/home/usuario/documentos`
- Mac: `/Users/usuario/Documents`
- Windows WSL: `/mnt/c/Users/usuario/Documents`

---

## 🔖 Accesos directos en el sidebar

**✨ ¡AUTOMÁTICO!** Los accesos directos se generan automáticamente a partir de las carpetas en el directorio raíz de `FILE_MANAGER_BASE_PATH`.

**Características:**
- ✅ Se muestran todas las carpetas del directorio raíz
- ✅ Ordenadas alfabéticamente
- ✅ Iconos y colores automáticos según el nombre de la carpeta
- ✅ Se actualizan automáticamente al crear/eliminar carpetas

**Iconos automáticos:**
- 📥 `descargas/downloads` → icono de descarga (naranja)
- 📄 `documentos/documents` → icono de archivo (verde)
- 🖼️ `imagenes/images/fotos/photos` → icono de imagen/cámara (morado/rosa)
- 🎬 `videos` → icono de video (rojo)
- 🎵 `musica/music` → icono de música (rosa)
- 💻 `code/proyectos/projects` → icono de código (azul)
- 💼 `trabajo/work` → icono de maletín (índigo)
- 📚 `libros/books` → icono de libro (amarillo)
- 📂 Otras carpetas → icono de carpeta (azul)

**¡No necesitas configurar nada!** Solo crea carpetas en tu directorio base y aparecerán automáticamente en el sidebar.

---

**Ejemplo:**
Si tu directorio es `/home/usuario/archivos` y contiene:
```
/home/usuario/archivos/
  ├── descargas/
  ├── documentos/
  ├── fotos/
  ├── proyectos/
  └── videos/
```

El sidebar mostrará automáticamente 5 accesos directos con iconos apropiados.

---

## 📋 Checklist de despliegue

- [ ] Clonar repo
- [ ] `composer install`
- [ ] Configurar `FILE_MANAGER_BASE_PATH` en `.env.local`
- [ ] Crear BD: `php bin/console doctrine:database:create`
- [ ] Migrar: `php bin/console doctrine:migrations:migrate`
- [ ] Dar permisos: `chmod -R 775 var/`
- [ ] Iniciar servidor
- [ ] Probar: `http://localhost:8000/files`

---

📚 **Documentación completa**: Ver [CONFIGURACION.md](CONFIGURACION.md)
