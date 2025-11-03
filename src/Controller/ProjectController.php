<?php

namespace App\Controller;

use App\Entity\Project;
use App\Repository\ProjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/projects')]
class ProjectController extends AbstractController
{
    public function __construct(
        private ProjectRepository $projectRepository
    ) {
    }

    #[Route('', name: 'app_projects_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        // If requesting JSON
        if ($request->query->get('format') === 'json' || $request->isXmlHttpRequest()) {
            $projects = $this->projectRepository->findAllOrdered();
            $data = [];
            foreach ($projects as $project) {
                $data[] = [
                    'id' => $project->getId(),
                    'name' => $project->getName(),
                    'description' => $project->getDescription(),
                    'color' => $project->getColor(),
                    'taskCount' => count($project->getTasks()),
                ];
            }
            return $this->json(['projects' => $data], 200, ['Content-Type' => 'application/json; charset=utf-8']);
        }

        // Otherwise render HTML directly
        $html = file_get_contents(__DIR__ . '/../../templates/projects/simple.html.twig');
        $response = new Response($html);
        $response->headers->set('Content-Type', 'text/html; charset=utf-8');
        return $response;
    }

    #[Route('/{id}', name: 'app_projects_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $project = $this->projectRepository->find($id);

        if (!$project) {
            return $this->json([
                'success' => false,
                'message' => 'Proyecto no encontrado',
            ], 404);
        }

        return $this->json([
            'project' => [
                'id' => $project->getId(),
                'name' => $project->getName(),
                'description' => $project->getDescription(),
                'color' => $project->getColor(),
                'createdAt' => $project->getCreatedAt()->format('Y-m-d H:i:s'),
            ]
        ]);
    }

    #[Route('/create', name: 'app_projects_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            $project = new Project();
            $project->setName($data['name'] ?? '');

            if (!empty($data['description'])) {
                $project->setDescription($data['description']);
            }

            if (!empty($data['color'])) {
                $project->setColor($data['color']);
            }

            $this->projectRepository->save($project);

            return $this->json([
                'success' => true,
                'message' => 'Proyecto creado',
                'project' => [
                    'id' => $project->getId(),
                    'name' => $project->getName(),
                    'color' => $project->getColor(),
                ],
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Error al crear el proyecto: ' . $e->getMessage(),
            ], 400);
        }
    }

    #[Route('/{id}/update', name: 'app_projects_update', methods: ['POST'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $project = $this->projectRepository->find($id);

        if (!$project) {
            return $this->json([
                'success' => false,
                'message' => 'Proyecto no encontrado',
            ], 404);
        }

        try {
            $data = json_decode($request->getContent(), true);

            if (isset($data['name'])) {
                $project->setName($data['name']);
            }

            if (isset($data['description'])) {
                $project->setDescription($data['description']);
            }

            if (isset($data['color'])) {
                $project->setColor($data['color']);
            }

            $this->projectRepository->save($project);

            return $this->json([
                'success' => true,
                'message' => 'Proyecto actualizado',
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Error al actualizar el proyecto: ' . $e->getMessage(),
            ], 400);
        }
    }

    #[Route('/{id}/delete', name: 'app_projects_delete', methods: ['DELETE', 'POST'])]
    public function delete(int $id): JsonResponse
    {
        $project = $this->projectRepository->find($id);

        if (!$project) {
            return $this->json([
                'success' => false,
                'message' => 'Proyecto no encontrado',
            ], 404);
        }

        try {
            // Check if project has tasks
            if ($project->getTasks()->count() > 0) {
                return $this->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el proyecto porque tiene tareas asociadas',
                ], 400);
            }

            $this->projectRepository->remove($project);

            return $this->json([
                'success' => true,
                'message' => 'Proyecto eliminado',
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Error al eliminar el proyecto: ' . $e->getMessage(),
            ], 400);
        }
    }
}
