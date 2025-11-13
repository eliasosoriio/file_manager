<?php

namespace App\Controller;

use App\Entity\Task;
use App\Repository\TaskRepository;
use App\Repository\ProjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/tasks-management')]
class TaskManagementController extends AbstractController
{
    public function __construct(
        private TaskRepository $taskRepository,
        private ProjectRepository $projectRepository
    ) {
    }

    #[Route('', name: 'app_tasks_management_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        // If requesting JSON
        if ($request->query->get('format') === 'json' || $request->isXmlHttpRequest()) {
            $qb = $this->taskRepository->createQueryBuilder('t');
            
            // Si viene del registro de tiempo (excludeCompleted=true), excluir completadas
            if ($request->query->get('excludeCompleted') === 'true') {
                $qb->where('t.status IN (:statuses)')
                   ->setParameter('statuses', ['pending', 'in_progress', 'recurring']);
            }
            
            $qb->orderBy('t.status', 'ASC')
               ->addOrderBy('t.name', 'ASC');
            
            $tasks = $qb->getQuery()->getResult();
            
            $data = [];
            foreach ($tasks as $task) {
                $data[] = [
                    'id' => $task->getId(),
                    'name' => $task->getName(),
                    'status' => $task->getStatus(),
                    'ticketNumber' => $task->getTicketNumber(),
                    'project' => [
                        'id' => $task->getProject()->getId(),
                        'name' => $task->getProject()->getName(),
                        'color' => $task->getProject()->getColor(),
                    ],
                ];
            }
            return $this->json(['tasks' => $data], 200, ['Content-Type' => 'application/json; charset=utf-8']);
        }

        // Otherwise render the Twig template
        return $this->render('tasks_management/simple.html.twig');
    }

    #[Route('/{id}', name: 'app_tasks_management_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $task = $this->taskRepository->find($id);

        if (!$task) {
            return $this->json([
                'success' => false,
                'message' => 'Tarea no encontrada',
            ], 404);
        }

        return $this->json([
            'task' => [
                'id' => $task->getId(),
                'name' => $task->getName(),
                'status' => $task->getStatus(),
                'ticketNumber' => $task->getTicketNumber(),
                'project' => [
                    'id' => $task->getProject()->getId(),
                    'name' => $task->getProject()->getName(),
                ],
                'createdAt' => $task->getCreatedAt()->format('Y-m-d H:i:s'),
            ]
        ]);
    }

    #[Route('/create', name: 'app_tasks_management_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            $project = $this->projectRepository->find($data['projectId'] ?? 0);

            if (!$project) {
                return $this->json([
                    'success' => false,
                    'message' => 'Proyecto no encontrado',
                ], 400);
            }

            $task = new Task();
            $task->setName($data['name'] ?? '');
            $task->setProject($project);
            $task->setStatus($data['status'] ?? 'pending');

            if (!empty($data['ticketNumber'])) {
                $task->setTicketNumber($data['ticketNumber']);
            }

            $this->taskRepository->save($task);

            return $this->json([
                'success' => true,
                'message' => 'Tarea creada',
                'task' => [
                    'id' => $task->getId(),
                    'name' => $task->getName(),
                    'status' => $task->getStatus(),
                    'ticketNumber' => $task->getTicketNumber(),
                    'project' => [
                        'id' => $project->getId(),
                        'name' => $project->getName(),
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Error al crear la tarea: ' . $e->getMessage(),
            ], 400);
        }
    }

    #[Route('/{id}/update-status', name: 'app_tasks_management_update_status', methods: ['POST'])]
    public function updateStatus(int $id, Request $request): JsonResponse
    {
        $task = $this->taskRepository->find($id);

        if (!$task) {
            return $this->json([
                'success' => false,
                'message' => 'Tarea no encontrada',
            ], 404);
        }

        try {
            $data = json_decode($request->getContent(), true);
            $task->setStatus($data['status'] ?? 'pending');

            $this->taskRepository->save($task);

            return $this->json([
                'success' => true,
                'message' => 'Estado actualizado',
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Error al actualizar el estado: ' . $e->getMessage(),
            ], 400);
        }
    }

    #[Route('/{id}/update', name: 'app_tasks_management_update', methods: ['POST'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $task = $this->taskRepository->find($id);

        if (!$task) {
            return $this->json([
                'success' => false,
                'message' => 'Tarea no encontrada',
            ], 404);
        }

        try {
            $data = json_decode($request->getContent(), true);

            if (isset($data['name'])) {
                $task->setName($data['name']);
            }

            if (isset($data['projectId'])) {
                $project = $this->projectRepository->find($data['projectId']);
                if ($project) {
                    $task->setProject($project);
                }
            }

            if (isset($data['status'])) {
                $task->setStatus($data['status']);
            }

            if (isset($data['ticketNumber'])) {
                $task->setTicketNumber($data['ticketNumber']);
            }

            $this->taskRepository->save($task);

            return $this->json([
                'success' => true,
                'message' => 'Tarea actualizada',
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Error al actualizar la tarea: ' . $e->getMessage(),
            ], 400);
        }
    }

    #[Route('/{id}/delete', name: 'app_tasks_management_delete', methods: ['DELETE', 'POST'])]
    public function delete(int $id): JsonResponse
    {
        $task = $this->taskRepository->find($id);

        if (!$task) {
            return $this->json([
                'success' => false,
                'message' => 'Tarea no encontrada',
            ], 404);
        }

        try {
            $this->taskRepository->remove($task);

            return $this->json([
                'success' => true,
                'message' => 'Tarea eliminada',
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Error al eliminar la tarea: ' . $e->getMessage(),
            ], 400);
        }
    }

    #[Route('/by-project/{projectId}', name: 'app_tasks_management_by_project', methods: ['GET'])]
    public function getByProject(int $projectId): JsonResponse
    {
        $tasks = $this->taskRepository->findActiveTasks($projectId);

        return $this->json([
            'success' => true,
            'tasks' => array_map(function (Task $task) {
                return [
                    'id' => $task->getId(),
                    'name' => $task->getName(),
                    'status' => $task->getStatus(),
                    'ticketNumber' => $task->getTicketNumber(),
                ];
            }, $tasks),
        ]);
    }
}
