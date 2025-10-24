<?php

namespace App\Controller;

use App\Entity\Task;
use App\Repository\TaskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Dompdf\Dompdf;
use Dompdf\Options;

#[Route('/tasks')]
class TaskController extends AbstractController
{
    public function __construct(
        private TaskRepository $taskRepository
    ) {
    }

    #[Route('', name: 'app_tasks_index', methods: ['GET'])]
    public function index(): Response
    {
        $tasksGrouped = $this->taskRepository->findGroupedByDay();
        $pendingCount = $this->taskRepository->countPending();

        // Calculate total hours per day
        $totalHoursByDay = [];
        foreach (array_keys($tasksGrouped) as $day) {
            $totalSeconds = $this->taskRepository->getTotalHoursByDay($day);
            $hours = floor($totalSeconds / 3600);
            $minutes = floor(($totalSeconds % 3600) / 60);
            $totalHoursByDay[$day] = sprintf('%dh %dm', $hours, $minutes);
        }

        return $this->render('tasks/index.html.twig', [
            'tasksGrouped' => $tasksGrouped,
            'pendingCount' => $pendingCount,
            'totalHoursByDay' => $totalHoursByDay,
        ]);
    }

    #[Route('/create', name: 'app_tasks_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            $task = new Task();
            $task->setTitle($data['title'] ?? '');

            if (!empty($data['description'])) {
                $task->setDescription($data['description']);
            }

            if (!empty($data['date'])) {
                $task->setDate(new \DateTime($data['date']));
            }

            if (!empty($data['startTime'])) {
                $task->setStartTime(new \DateTime($data['startTime']));
            }

            if (!empty($data['endTime'])) {
                $task->setEndTime(new \DateTime($data['endTime']));
            }

            $this->taskRepository->save($task);

            return $this->json([
                'success' => true,
                'message' => 'Tarea creada',
                'task' => [
                    'id' => $task->getId(),
                    'title' => $task->getTitle(),
                    'status' => $task->getStatus(),
                    'date' => $task->getDate()->format('Y-m-d'),
                ],
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/{id}/status', name: 'app_tasks_update_status', methods: ['POST'])]
    public function updateStatus(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $status = $data['status'] ?? '';

        try {
            $task = $this->taskRepository->find($id);

            if (!$task) {
                return $this->json(['error' => 'Tarea no encontrada'], 404);
            }

            $task->setStatus($status);
            $this->taskRepository->save($task);

            return $this->json([
                'success' => true,
                'message' => 'Estado actualizado',
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/{id}', name: 'app_tasks_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        try {
            $task = $this->taskRepository->find($id);

            if (!$task) {
                return $this->json(['error' => 'Tarea no encontrada'], 404);
            }

            $this->taskRepository->delete($task);

            return $this->json([
                'success' => true,
                'message' => 'Tarea eliminada',
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/{id}/update', name: 'app_tasks_update', methods: ['POST'])]
    public function update(int $id, Request $request): JsonResponse
    {
        try {
            $task = $this->taskRepository->find($id);

            if (!$task) {
                return $this->json(['error' => 'Tarea no encontrada'], 404);
            }

            $data = json_decode($request->getContent(), true);

            if (isset($data['title'])) {
                $task->setTitle(trim($data['title']));
            }

            if (isset($data['description'])) {
                $task->setDescription(trim($data['description']));
            }

            if (isset($data['date'])) {
                $task->setDate(new \DateTime($data['date']));
            }

            if (isset($data['startTime'])) {
                $task->setStartTime(!empty($data['startTime']) ? new \DateTime($data['startTime']) : null);
            }

            if (isset($data['endTime'])) {
                $task->setEndTime(!empty($data['endTime']) ? new \DateTime($data['endTime']) : null);
            }

            $this->taskRepository->save($task);

            return $this->json([
                'success' => true,
                'message' => 'Tarea actualizada',
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/{id}/start', name: 'app_tasks_start', methods: ['POST'])]
    public function startTimer(int $id): JsonResponse
    {
        try {
            $task = $this->taskRepository->find($id);

            if (!$task) {
                return $this->json(['error' => 'Tarea no encontrada'], 404);
            }

            if ($task->getStartedAt() !== null) {
                return $this->json(['error' => 'La tarea ya está en progreso'], 400);
            }

            $task->startTimer();
            $this->taskRepository->save($task);

            return $this->json([
                'success' => true,
                'message' => 'Timer iniciado',
                'startedAt' => $task->getStartedAt()->format('Y-m-d H:i:s'),
                'status' => $task->getStatus(),
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/{id}/stop', name: 'app_tasks_stop', methods: ['POST'])]
    public function stopTimer(int $id): JsonResponse
    {
        try {
            $task = $this->taskRepository->find($id);

            if (!$task) {
                return $this->json(['error' => 'Tarea no encontrada'], 404);
            }

            if ($task->getStartedAt() === null) {
                return $this->json(['error' => 'La tarea no está en progreso'], 400);
            }

            $task->stopTimer();
            $this->taskRepository->save($task);

            return $this->json([
                'success' => true,
                'message' => 'Timer detenido',
                'durationSeconds' => $task->getDurationSeconds(),
                'status' => $task->getStatus(),
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/export-pdf', name: 'app_tasks_export_pdf', methods: ['GET'])]
    public function exportPdf(Request $request): Response
    {
        $date = $request->query->get('date', date('Y-m-d'));

        try {
            // Get tasks for the specified date
            $qb = $this->taskRepository->createQueryBuilder('t')
                ->where('t.date = :date')
                ->setParameter('date', new \DateTime($date))
                ->orderBy('t.startTime', 'ASC')
                ->addOrderBy('t.createdAt', 'ASC');

            $tasks = $qb->getQuery()->getResult();

            // Calculate total hours
            $totalSeconds = $this->taskRepository->getTotalHoursByDay($date);
            $totalHours = floor($totalSeconds / 3600);
            $totalMinutes = floor(($totalSeconds % 3600) / 60);

            // Render HTML for PDF
            $html = $this->renderView('tasks/pdf.html.twig', [
                'tasks' => $tasks,
                'date' => new \DateTime($date),
                'totalHours' => $totalHours,
                'totalMinutes' => $totalMinutes,
            ]);

            // Configure Dompdf
            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);
            $options->set('defaultFont', 'DejaVu Sans');

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            // Output PDF
            return new Response(
                $dompdf->output(),
                Response::HTTP_OK,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => sprintf('attachment; filename="tareas_%s.pdf"', $date),
                ]
            );
        } catch (\Exception $e) {
            return new Response('Error al generar PDF: ' . $e->getMessage(), 500);
        }
    }

    #[Route('/export-txt', name: 'app_tasks_export_txt', methods: ['GET'])]
    public function exportTxt(Request $request): Response
    {
        $date = $request->query->get('date', date('Y-m-d'));

        try {
            $content = $this->generateTxtContent($date);

            // Output TXT
            return new Response(
                $content,
                Response::HTTP_OK,
                [
                    'Content-Type' => 'text/plain; charset=utf-8',
                    'Content-Disposition' => sprintf('attachment; filename="tareas_%s.txt"', $date),
                ]
            );
        } catch (\Exception $e) {
            return new Response('Error al generar TXT: ' . $e->getMessage(), 500);
        }
    }

    #[Route('/get-txt-content', name: 'app_tasks_get_txt_content', methods: ['POST'])]
    public function getTxtContent(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $date = $data['date'] ?? date('Y-m-d');

        try {
            $content = $this->generateTxtContent($date);

            return $this->json([
                'success' => true,
                'content' => $content,
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    private function generateTxtContent(string $date): string
    {
        // Get tasks for the specified date
        $qb = $this->taskRepository->createQueryBuilder('t')
            ->where('t.date = :date')
            ->setParameter('date', new \DateTime($date))
            ->orderBy('t.startTime', 'ASC')
            ->addOrderBy('t.createdAt', 'ASC');

        $tasks = $qb->getQuery()->getResult();

        // Calculate total hours
        $totalSeconds = $this->taskRepository->getTotalHoursByDay($date);
        $totalHours = floor($totalSeconds / 3600);
        $totalMinutes = floor(($totalSeconds % 3600) / 60);

        // Build TXT content
        $dateObj = new \DateTime($date);
        $content = "═══════════════════════════════════════════════════════════════════\n";
        $content .= "                      REPORTE DE TAREAS\n";
        $content .= "═══════════════════════════════════════════════════════════════════\n\n";
        $content .= "Fecha: " . $dateObj->format('l, d \d\e F \d\e Y') . "\n";
        $content .= "Generado: " . (new \DateTime())->format('d/m/Y H:i') . "\n";
        $content .= "Expedido por: FileManager\n\n";
        $content .= "───────────────────────────────────────────────────────────────────\n\n";

        if (count($tasks) > 0) {
            foreach ($tasks as $index => $task) {
                $content .= ($index + 1) . ". " . strtoupper($task->getTitle()) . "\n";
                $content .= str_repeat("─", 67) . "\n";

                if ($task->getDescription()) {
                    $content .= "   Descripción: " . $task->getDescription() . "\n";
                }

                $content .= "   Estado: ";
                if ($task->getStatus() === 'pending') {
                    $content .= "Pendiente";
                } elseif ($task->getStatus() === 'in_progress') {
                    $content .= "En progreso";
                } else {
                    $content .= "Completada";
                }
                $content .= "\n";

                if ($task->getStartTime() && $task->getEndTime()) {
                    $content .= "   Horario: " . $task->getStartTime()->format('H:i') . " - " . $task->getEndTime()->format('H:i') . "\n";
                }

                if ($task->getDurationSeconds() > 0) {
                    $content .= "   Duración: " . $task->getFormattedDuration() . "\n";
                }

                $content .= "\n";
            }

            $content .= "═══════════════════════════════════════════════════════════════════\n";
            $content .= "TOTAL DEL DÍA: {$totalHours}h {$totalMinutes}m\n";
            $content .= "═══════════════════════════════════════════════════════════════════\n";
        } else {
            $content .= "No hay tareas registradas para esta fecha.\n\n";
            $content .= "═══════════════════════════════════════════════════════════════════\n";
        }

        return $content;
    }
}
