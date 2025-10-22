<?php

namespace App\Controller;

use App\Repository\FileHistoryRepository;
use App\Service\FileManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/files')]
class FileController extends AbstractController
{
    public function __construct(
        private FileManager $fileManager,
        private FileHistoryRepository $historyRepository,
        private array $shortcuts = []
    ) {
        // Obtener shortcuts de la configuración
        $this->shortcuts = $_ENV['FILE_MANAGER_SHORTCUTS'] ?? [];
    }

    #[Route('', name: 'app_files_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $path = $request->query->get('path', '');

        try {
            $files = $this->fileManager->listFiles($path);
            $recentHistory = $this->historyRepository->findRecent(5);

            // Get breadcrumbs
            $breadcrumbs = $this->getBreadcrumbs($path);

            // Get statistics only on root path
            $statistics = null;
            if (empty($path)) {
                $statistics = $this->fileManager->getStatistics();
            }

            return $this->render('files/index.html.twig', [
                'files' => $files,
                'currentPath' => $path,
                'breadcrumbs' => $breadcrumbs,
                'recentHistory' => $recentHistory,
                'statistics' => $statistics,
            ]);
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_files_index');
        }
    }

    #[Route('/recent', name: 'app_files_recent', methods: ['GET'])]
    public function recent(Request $request): Response
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $pagination = $this->historyRepository->findRecentPaginated($page, 20);

        return $this->render('files/recent.html.twig', [
            'recentHistory' => $pagination['items'],
            'pagination' => $pagination,
        ]);
    }

    #[Route('/search', name: 'app_files_search', methods: ['GET'])]
    public function search(Request $request): Response
    {
        $query = $request->query->get('q', '');

        if (empty($query)) {
            return $this->redirectToRoute('app_files_index');
        }

        $results = $this->fileManager->search($query);
        $historyResults = $this->historyRepository->search($query);

        // Si es una petición AJAX, devolver JSON
        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'success' => true,
                'query' => $query,
                'results' => array_map(function($file) {
                    return [
                        'name' => $file['name'],
                        'path' => $file['path'],
                        'relativePath' => $file['relativePath'],
                        'type' => $file['type'],
                        'isDirectory' => $file['isDirectory'],
                        'url' => $this->generateUrl('app_files_open', ['path' => $file['relativePath']]),
                    ];
                }, $results),
                'total' => count($results),
            ]);
        }

        return $this->render('files/search.html.twig', [
            'query' => $query,
            'results' => $results,
            'historyResults' => $historyResults,
        ]);
    }

    #[Route('/create', name: 'app_files_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $name = $data['name'] ?? '';
        $type = $data['type'] ?? 'file';
        $currentPath = $data['path'] ?? '';

        if (empty($name)) {
            return $this->json(['error' => 'Name is required'], 400);
        }

        try {
            $fullPath = $currentPath ? $currentPath . '/' . $name : $name;
            $this->fileManager->create($fullPath, $type);

            return $this->json([
                'success' => true,
                'message' => ucfirst($type) . ' created successfully',
                'path' => $fullPath,
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/delete', name: 'app_files_delete', methods: ['POST'])]
    public function delete(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $path = $data['path'] ?? '';

        if (empty($path)) {
            return $this->json(['error' => 'Path is required'], 400);
        }

        try {
            $this->fileManager->delete($path);

            return $this->json([
                'success' => true,
                'message' => 'Deleted successfully',
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/rename', name: 'app_files_rename', methods: ['POST'])]
    public function rename(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $oldPath = $data['oldPath'] ?? '';
        $newName = $data['newName'] ?? '';

        if (empty($oldPath) || empty($newName)) {
            return $this->json(['error' => 'Old path and new name are required'], 400);
        }

        try {
            $directory = dirname($oldPath);
            $newPath = ($directory !== '.' ? $directory . '/' : '') . $newName;

            $this->fileManager->rename($oldPath, $newPath);

            return $this->json([
                'success' => true,
                'message' => 'Renamed successfully',
                'newPath' => $newPath,
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/move', name: 'app_files_move', methods: ['POST'])]
    public function move(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $sourcePath = $data['source'] ?? '';
        $destinationPath = $data['destination'] ?? '';

        if (empty($sourcePath)) {
            return $this->json(['error' => 'Source is required'], 400);
        }

        try {
            // If destination is empty, use source (will trigger auto-rename if exists)
            if (empty($destinationPath)) {
                $destinationPath = $sourcePath;
            }

            $this->fileManager->move($sourcePath, $destinationPath);

            return $this->json([
                'success' => true,
                'message' => 'Moved successfully',
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/copy', name: 'app_files_copy', methods: ['POST'])]
    public function copy(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $sourcePath = $data['source'] ?? '';
        $destinationPath = $data['destination'] ?? '';

        if (empty($sourcePath)) {
            return $this->json(['error' => 'Source is required'], 400);
        }

        try {
            // If destination is empty, use source (will trigger auto-rename if exists)
            if (empty($destinationPath)) {
                $destinationPath = $sourcePath;
            }

            $this->fileManager->copy($sourcePath, $destinationPath);

            return $this->json([
                'success' => true,
                'message' => 'Copied successfully',
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/open', name: 'app_files_open', methods: ['GET'])]
    public function open(Request $request): Response
    {
        $path = $request->query->get('path', '');

        if (empty($path)) {
            return $this->redirectToRoute('app_files_index');
        }

        try {
            $fileInfo = $this->fileManager->getFileInfo($path);

            if ($fileInfo['type'] === 'directory') {
                return $this->redirectToRoute('app_files_index', ['path' => $path]);
            }

            $content = $this->fileManager->getFileContent($path);

            return $this->render('files/open.html.twig', [
                'file' => $fileInfo,
                'content' => $content,
            ]);
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_files_index');
        }
    }

    #[Route('/download', name: 'app_files_download', methods: ['GET'])]
    public function download(Request $request): Response
    {
        $path = $request->query->get('path', '');

        if (empty($path)) {
            throw $this->createNotFoundException('File not found');
        }

        try {
            $fullPath = $this->fileManager->getBasePath() . '/' . ltrim($path, '/');

            if (!file_exists($fullPath) || !is_file($fullPath)) {
                throw $this->createNotFoundException('File not found');
            }

            $response = new BinaryFileResponse($fullPath);

            // Forzar descarga en lugar de abrir en el navegador
            $response->setContentDisposition(
                \Symfony\Component\HttpFoundation\ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                basename($fullPath)
            );

            return $response;
        } catch (\Exception $e) {
            throw $this->createNotFoundException('File not found');
        }
    }

    #[Route('/view', name: 'app_files_view', methods: ['GET'])]
    public function view(Request $request): Response
    {
        $path = $request->query->get('path', '');

        if (empty($path)) {
            throw $this->createNotFoundException('File not found');
        }

        try {
            $fullPath = $this->fileManager->getBasePath() . '/' . ltrim($path, '/');

            if (!file_exists($fullPath) || !is_file($fullPath)) {
                throw $this->createNotFoundException('File not found');
            }

            $response = new BinaryFileResponse($fullPath);

            // Mostrar inline en el navegador (no forzar descarga)
            $response->setContentDisposition(
                \Symfony\Component\HttpFoundation\ResponseHeaderBag::DISPOSITION_INLINE,
                basename($fullPath)
            );

            return $response;
        } catch (\Exception $e) {
            throw $this->createNotFoundException('File not found');
        }
    }

    #[Route('/save', name: 'app_files_save', methods: ['POST'])]
    public function save(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $path = $data['path'] ?? '';
        $content = $data['content'] ?? '';

        if (empty($path)) {
            return $this->json(['error' => 'Path is required'], 400);
        }

        try {
            $this->fileManager->saveFileContent($path, $content);

            return $this->json([
                'success' => true,
                'message' => 'File saved successfully',
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/info', name: 'app_files_info', methods: ['GET'])]
    public function info(Request $request): JsonResponse
    {
        $path = $request->query->get('path', '');

        if (empty($path)) {
            return $this->json(['error' => 'Path is required'], 400);
        }

        try {
            $info = $this->fileManager->getFileInfo($path);
            $info['sizeFormatted'] = $this->fileManager->formatSize($info['size']);
            $info['modifiedFormatted'] = date('Y-m-d H:i:s', $info['modified']);

            return $this->json($info);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    private function getBreadcrumbs(string $path): array
    {
        if (empty($path)) {
            return [['name' => 'Home', 'path' => '']];
        }

        $parts = explode('/', trim($path, '/'));
        $breadcrumbs = [['name' => 'Home', 'path' => '']];
        $currentPath = '';

        foreach ($parts as $part) {
            $currentPath .= ($currentPath ? '/' : '') . $part;
            $breadcrumbs[] = ['name' => $part, 'path' => $currentPath];
        }

        return $breadcrumbs;
    }
}
