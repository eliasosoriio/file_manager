<?php

namespace App\Service;

use App\Entity\FileHistory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class FileManager
{
    private string $basePath;

    private Filesystem $filesystem;
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager, string $fileManagerBasePath = null)
    {
        $this->filesystem = new Filesystem();
        $this->entityManager = $entityManager;
        $this->basePath = $fileManagerBasePath ?? $_ENV['FILE_MANAGER_BASE_PATH'] ?? '/home/sdweb/elias_osorio_files';
    }

    public function getBasePath(): string
    {
        return $this->basePath;
    }    /**
     * List files and directories in a path
     */
    public function listFiles(string $relativePath = ''): array
    {
        $fullPath = $this->getFullPath($relativePath);

        if (!$this->filesystem->exists($fullPath)) {
            throw new \RuntimeException("Path does not exist: {$fullPath}");
        }

        $finder = new Finder();
        $finder->in($fullPath)->depth('== 0')->sortByName();

        $items = [];
        foreach ($finder as $file) {
            $items[] = [
                'name' => $file->getFilename(),
                'path' => $this->getRelativePath($file->getRealPath()),
                'type' => $file->isDir() ? 'directory' : 'file',
                'size' => $file->isFile() ? $file->getSize() : null,
                'modified' => $file->getMTime(),
                'extension' => $file->isFile() ? $file->getExtension() : null,
                'icon' => $this->getFileIcon($file),
            ];
        }

        return $items;
    }

    /**
     * Get root directories for shortcuts (sorted alphabetically)
     */
    public function getRootDirectories(): array
    {
        if (!$this->filesystem->exists($this->basePath)) {
            return [];
        }

        $finder = new Finder();
        $finder->in($this->basePath)->depth('== 0')->directories()->sortByName();

        $directories = [];
        foreach ($finder as $dir) {
            $directories[] = [
                'name' => $dir->getFilename(),
                'path' => $dir->getFilename(),
                'icon' => $this->getDirectoryIcon($dir->getFilename()),
                'color' => $this->getDirectoryColor($dir->getFilename()),
            ];
        }

        return $directories;
    }

    /**
     * Get icon for directory based on name
     */
    private function getDirectoryIcon(string $name): string
    {
        $name = strtolower($name);

        $iconMap = [
            'downloads' => 'download',
            'descargas' => 'download',
            'documents' => 'file-text',
            'documentos' => 'file-text',
            'images' => 'image',
            'imagenes' => 'image',
            'pictures' => 'image',
            'fotos' => 'camera',
            'photos' => 'camera',
            'videos' => 'video',
            'music' => 'music',
            'musica' => 'music',
            'code' => 'code',
            'projects' => 'code',
            'proyectos' => 'code',
            'work' => 'briefcase',
            'trabajo' => 'briefcase',
            'books' => 'book',
            'libros' => 'book',
            'archive' => 'archive',
            'archivos' => 'archive',
        ];

        foreach ($iconMap as $keyword => $icon) {
            if (strpos($name, $keyword) !== false) {
                return $icon;
            }
        }

        return 'folder';
    }

    /**
     * Get color for directory based on name
     */
    private function getDirectoryColor(string $name): string
    {
        $name = strtolower($name);

        $colorMap = [
            'downloads' => 'orange',
            'descargas' => 'orange',
            'documents' => 'green',
            'documentos' => 'green',
            'images' => 'purple',
            'imagenes' => 'purple',
            'pictures' => 'purple',
            'fotos' => 'pink',
            'photos' => 'pink',
            'videos' => 'red',
            'music' => 'pink',
            'musica' => 'pink',
            'code' => 'blue',
            'projects' => 'blue',
            'proyectos' => 'blue',
            'work' => 'indigo',
            'trabajo' => 'indigo',
            'books' => 'yellow',
            'libros' => 'yellow',
        ];

        foreach ($colorMap as $keyword => $color) {
            if (strpos($name, $keyword) !== false) {
                return $color;
            }
        }

        return 'blue';
    }

    /**
     * Create a new file or directory
     */
    public function create(string $relativePath, string $type = 'file'): void
    {
        $fullPath = $this->getFullPath($relativePath);

        if ($type === 'directory') {
            $this->filesystem->mkdir($fullPath);
        } else {
            $this->filesystem->touch($fullPath);
        }

        $this->logAction($relativePath, 'created', $type);
    }

    /**
     * Delete a file or directory
     */
    public function delete(string $relativePath): void
    {
        $fullPath = $this->getFullPath($relativePath);
        $this->filesystem->remove($fullPath);
        $this->logAction($relativePath, 'deleted');
    }

    /**
     * Rename a file or directory
     */
    public function rename(string $oldPath, string $newPath): void
    {
        $oldFullPath = $this->getFullPath($oldPath);
        $newFullPath = $this->getFullPath($newPath);

        $this->filesystem->rename($oldFullPath, $newFullPath);
        $this->logAction($newPath, 'renamed');
    }

    /**
     * Move a file or directory
     */
    public function move(string $sourcePath, string $destinationPath): void
    {
        $sourceFullPath = $this->getFullPath($sourcePath);
        $destFullPath = $this->getFullPath($destinationPath);

        // If destination is a directory, move the source INTO that directory
        if (is_dir($destFullPath)) {
            $sourceFilename = basename($sourceFullPath);
            $destFullPath = rtrim($destFullPath, '/') . '/' . $sourceFilename;
        }

        // If destination exists, generate a unique name
        $destFullPath = $this->getUniquePath($destFullPath);

        $this->filesystem->rename($sourceFullPath, $destFullPath);

        // Get the relative path for logging
        $finalRelativePath = $this->getRelativePath($destFullPath);
        $this->logAction($finalRelativePath, 'moved');
    }

    /**
     * Copy a file or directory
     */
    public function copy(string $sourcePath, string $destinationPath): void
    {
        $sourceFullPath = $this->getFullPath($sourcePath);
        $destFullPath = $this->getFullPath($destinationPath);

        // If destination exists, generate a unique name
        $destFullPath = $this->getUniquePath($destFullPath);

        if (is_dir($sourceFullPath)) {
            $this->filesystem->mirror($sourceFullPath, $destFullPath);
        } else {
            $this->filesystem->copy($sourceFullPath, $destFullPath);
        }

        // Get the relative path for logging
        $finalRelativePath = $this->getRelativePath($destFullPath);
        $this->logAction($finalRelativePath, 'copied');
    }

    /**
     * Generate a unique path if the destination already exists
     * Adds (1), (2), etc. before the extension
     */
    private function getUniquePath(string $path): string
    {
        if (!$this->filesystem->exists($path)) {
            return $path;
        }

        $directory = dirname($path);
        $filename = basename($path);

        // Check if it's a directory or file
        $isDirectory = is_dir($path);

        if ($isDirectory) {
            // For directories, just append the number
            $counter = 1;
            while ($this->filesystem->exists("{$directory}/{$filename} ({$counter})")) {
                $counter++;
            }
            return "{$directory}/{$filename} ({$counter})";
        } else {
            // For files, add counter before extension
            $pathInfo = pathinfo($filename);
            $basename = $pathInfo['filename'];
            $extension = isset($pathInfo['extension']) ? '.' . $pathInfo['extension'] : '';

            $counter = 1;
            while ($this->filesystem->exists("{$directory}/{$basename} ({$counter}){$extension}")) {
                $counter++;
            }
            return "{$directory}/{$basename} ({$counter}){$extension}";
        }
    }

    /**
     * Get file content
     */
    public function getFileContent(string $relativePath): string
    {
        $fullPath = $this->getFullPath($relativePath);

        if (!is_file($fullPath)) {
            throw new \RuntimeException("Not a file: {$fullPath}");
        }

        $this->logAction($relativePath, 'opened');
        return file_get_contents($fullPath);
    }

    /**
     * Save file content
     */
    public function saveFileContent(string $relativePath, string $content): void
    {
        $fullPath = $this->getFullPath($relativePath);
        $this->filesystem->dumpFile($fullPath, $content);
        $this->logAction($relativePath, 'updated');
    }

    /**
     * Search files by name
     */
    public function search(string $query, string $basePath = ''): array
    {
        $fullPath = $this->getFullPath($basePath);

        $finder = new Finder();
        $finder->in($fullPath)->name("*{$query}*")->sortByName();

        $results = [];
        foreach ($finder as $file) {
            $relPath = $this->getRelativePath($file->getRealPath());
            $results[] = [
                'name' => $file->getFilename(),
                'path' => $file->getRealPath(),
                'relativePath' => $relPath,
                'type' => $file->isDir() ? 'directory' : 'file',
                'isDirectory' => $file->isDir(),
                'size' => $file->isFile() ? $file->getSize() : null,
                'modified' => $file->getMTime(),
                'extension' => $file->isFile() ? $file->getExtension() : null,
                'icon' => $this->getFileIcon($file),
            ];
        }

        return $results;
    }

    /**
     * Get file info
     */
    public function getFileInfo(string $relativePath): array
    {
        $fullPath = $this->getFullPath($relativePath);

        if (!$this->filesystem->exists($fullPath)) {
            throw new \RuntimeException("Path does not exist: {$fullPath}");
        }

        $info = [
            'name' => basename($fullPath),
            'path' => $relativePath,
            'fullPath' => $fullPath,
            'type' => is_dir($fullPath) ? 'directory' : 'file',
            'size' => is_file($fullPath) ? filesize($fullPath) : null,
            'modified' => filemtime($fullPath),
            'permissions' => substr(sprintf('%o', fileperms($fullPath)), -4),
            'readable' => is_readable($fullPath),
            'writable' => is_writable($fullPath),
        ];

        if (is_file($fullPath)) {
            $info['extension'] = pathinfo($fullPath, PATHINFO_EXTENSION);
            $info['mimeType'] = mime_content_type($fullPath);
        }

        return $info;
    }

    private function getFullPath(string $relativePath): string
    {
        $fullPath = $this->basePath . '/' . ltrim($relativePath, '/');

        // Security: prevent path traversal
        $realBasePath = realpath($this->basePath);
        $realPath = realpath($fullPath) ?: $fullPath;

        if (strpos($realPath, $realBasePath) !== 0) {
            throw new \RuntimeException("Access denied: path outside base directory");
        }

        return $fullPath;
    }

    private function getRelativePath(string $fullPath): string
    {
        return str_replace($this->basePath . '/', '', $fullPath);
    }

    private function getFileIcon(\SplFileInfo $file): string
    {
        if ($file->isDir()) {
            return 'folder';
        }

        $ext = strtolower($file->getExtension());

        $iconMap = [
            'pdf' => 'file-text',
            'doc' => 'file-text', 'docx' => 'file-text',
            'xls' => 'file-spreadsheet', 'xlsx' => 'file-spreadsheet',
            'jpg' => 'image', 'jpeg' => 'image', 'png' => 'image', 'gif' => 'image', 'svg' => 'image',
            'mp4' => 'video', 'avi' => 'video', 'mov' => 'video',
            'mp3' => 'music', 'wav' => 'music',
            'zip' => 'archive', 'rar' => 'archive', 'tar' => 'archive', 'gz' => 'archive',
            'php' => 'code', 'js' => 'code', 'html' => 'code', 'css' => 'code', 'py' => 'code',
        ];

        return $iconMap[$ext] ?? 'file';
    }

    private function logAction(string $path, string $action, ?string $type = null): void
    {
        $fullPath = $this->getFullPath($path);

        $history = new FileHistory();
        $history->setFilename(basename($path));
        $history->setPath($path);
        $history->setAction($action);

        if (file_exists($fullPath) && is_file($fullPath)) {
            $history->setFileSize(filesize($fullPath));
            $history->setFileType(pathinfo($fullPath, PATHINFO_EXTENSION));
        } elseif ($type) {
            $history->setFileType($type);
        }

        $this->entityManager->persist($history);
        $this->entityManager->flush();
    }

    public function formatSize(?int $bytes): string
    {
        if ($bytes === null) return '-';

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
