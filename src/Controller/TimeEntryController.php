<?php

namespace App\Controller;

use App\Entity\TimeEntry;
use App\Entity\Task;
use App\Repository\TimeEntryRepository;
use App\Repository\TaskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Dompdf\Dompdf;
use Dompdf\Options;

#[Route('/time-entries')]
class TimeEntryController extends AbstractController
{
    public function __construct(
        private TimeEntryRepository $timeEntryRepository,
        private TaskRepository $taskRepository
    ) {
    }

    #[Route('', name: 'app_time_entries_index', methods: ['GET'])]
    public function index(): Response
    {
        // Render HTML directly
        $html = file_get_contents(__DIR__ . '/../../templates/time_entries/simple.html.twig');
        $response = new Response($html);
        $response->headers->set('Content-Type', 'text/html; charset=utf-8');
        return $response;
    }

    #[Route('/create', name: 'app_time_entries_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            $task = $this->taskRepository->find($data['taskId'] ?? 0);

            if (!$task) {
                return $this->json([
                    'success' => false,
                    'message' => 'Tarea no encontrada',
                ], 400);
            }

            $entry = new TimeEntry();
            $entry->setTask($task);

            if (!empty($data['description'])) {
                $entry->setDescription($data['description']);
            }

            if (!empty($data['date'])) {
                $entry->setDate(new \DateTime($data['date']));
            }

            if (!empty($data['startTime'])) {
                $entry->setStartTime(new \DateTime($data['startTime']));
            }

            if (!empty($data['endTime'])) {
                $entry->setEndTime(new \DateTime($data['endTime']));
            }

            $this->timeEntryRepository->save($entry);

            return $this->json([
                'success' => true,
                'message' => 'Registro de tiempo creado',
                'entry' => [
                    'id' => $entry->getId(),
                    'task' => [
                        'id' => $task->getId(),
                        'name' => $task->getName(),
                        'status' => $task->getStatus(),
                    ],
                    'date' => $entry->getDate()->format('Y-m-d'),
                ],
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/{id}/status', name: 'app_time_entries_update_status', methods: ['POST'])]
    public function updateStatus(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $status = $data['status'] ?? '';

        try {
            $entry = $this->timeEntryRepository->find($id);

            if (!$entry) {
                return $this->json(['error' => 'Registro no encontrado'], 404);
            }

            // Actualizar el estado de la tarea asociada si es necesario
            if ($entry->getTask()) {
                $task = $entry->getTask();
                // Si la entrada se completa, podríamos marcar la tarea como completada
                if ($status === 'completed') {
                    $task->setStatus('completed');
                    $this->taskRepository->save($task);
                }
            }

            return $this->json([
                'success' => true,
                'message' => 'Estado actualizado',
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/{id}', name: 'app_time_entries_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        try {
            $entry = $this->timeEntryRepository->find($id);

            if (!$entry) {
                return $this->json(['error' => 'Registro no encontrado'], 404);
            }

            $this->timeEntryRepository->delete($entry);

            return $this->json([
                'success' => true,
                'message' => 'Registro eliminado',
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/{id}/update', name: 'app_time_entries_update', methods: ['POST'])]
    public function update(int $id, Request $request): JsonResponse
    {
        try {
            $entry = $this->timeEntryRepository->find($id);

            if (!$entry) {
                return $this->json(['error' => 'Registro no encontrado'], 404);
            }

            $data = json_decode($request->getContent(), true);

            if (isset($data['description'])) {
                $entry->setDescription(trim($data['description']));
            }

            if (isset($data['date'])) {
                $entry->setDate(new \DateTime($data['date']));
            }

            if (isset($data['startTime'])) {
                $entry->setStartTime(!empty($data['startTime']) ? new \DateTime($data['startTime']) : null);
            }

            if (isset($data['endTime'])) {
                $entry->setEndTime(!empty($data['endTime']) ? new \DateTime($data['endTime']) : null);
            }

            // Recalcular duración si hay startTime y endTime
            if ($entry->getStartTime() && $entry->getEndTime()) {
                $entry->calculateDuration();
            }

            $this->timeEntryRepository->save($entry);

            return $this->json([
                'success' => true,
                'message' => 'Registro actualizado',
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/{id}/start', name: 'app_time_entries_start', methods: ['POST'])]
    public function startTimer(int $id): JsonResponse
    {
        try {
            $entry = $this->timeEntryRepository->find($id);

            if (!$entry) {
                return $this->json(['error' => 'Registro no encontrado'], 404);
            }

            if ($entry->getStartedAt() !== null) {
                return $this->json(['error' => 'El registro ya está en progreso'], 400);
            }

            $entry->startTimer();

            // Actualizar estado de la tarea asociada a "in_progress"
            if ($entry->getTask()) {
                $task = $entry->getTask();
                $task->setStatus('in_progress');
                $this->taskRepository->save($task);
            }

            $this->timeEntryRepository->save($entry);

            return $this->json([
                'success' => true,
                'message' => 'Timer iniciado',
                'startedAt' => $entry->getStartedAt()->format('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/{id}/stop', name: 'app_time_entries_stop', methods: ['POST'])]
    public function stopTimer(int $id): JsonResponse
    {
        try {
            $entry = $this->timeEntryRepository->find($id);

            if (!$entry) {
                return $this->json(['error' => 'Registro no encontrado'], 404);
            }

            if ($entry->getStartedAt() === null) {
                return $this->json(['error' => 'El registro no está en progreso'], 400);
            }

            $entry->stopTimer();
            $this->timeEntryRepository->save($entry);

            return $this->json([
                'success' => true,
                'message' => 'Timer detenido',
                'durationSeconds' => $entry->getDurationSeconds(),
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/export-pdf', name: 'app_time_entries_export_pdf', methods: ['GET'])]
    public function exportPdf(Request $request): Response
    {
        $date = $request->query->get('date', date('Y-m-d'));

        try {
            // Get time entries for the specified date
            $qb = $this->timeEntryRepository->createQueryBuilder('e')
                ->leftJoin('e.task', 't')
                ->addSelect('t')
                ->where('e.date = :date')
                ->setParameter('date', new \DateTime($date))
                ->orderBy('e.startTime', 'ASC')
                ->addOrderBy('e.createdAt', 'ASC');

            $entries = $qb->getQuery()->getResult();

            // Calculate total hours
            $totalSeconds = $this->timeEntryRepository->getTotalHoursByDay($date);
            $totalHours = floor($totalSeconds / 3600);
            $totalMinutes = floor(($totalSeconds % 3600) / 60);

            // Render HTML for PDF
            $html = $this->renderView('time_entries/pdf.html.twig', [
                'entries' => $entries,
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
                    'Content-Disposition' => sprintf('attachment; filename="registro_horas_%s.pdf"', $date),
                ]
            );
        } catch (\Exception $e) {
            return new Response('Error al generar PDF: ' . $e->getMessage(), 500);
        }
    }

    #[Route('/export-txt', name: 'app_time_entries_export_txt', methods: ['GET'])]
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
                    'Content-Disposition' => sprintf('attachment; filename="registro_horas_%s.txt"', $date),
                ]
            );
        } catch (\Exception $e) {
            return new Response('Error al generar TXT: ' . $e->getMessage(), 500);
        }
    }

    #[Route('/get-txt-content', name: 'app_time_entries_get_txt_content', methods: ['POST'])]
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
        // Get time entries for the specified date
        $qb = $this->timeEntryRepository->createQueryBuilder('e')
            ->leftJoin('e.task', 't')
            ->addSelect('t')
            ->where('e.date = :date')
            ->setParameter('date', new \DateTime($date))
            ->orderBy('e.startTime', 'ASC')
            ->addOrderBy('e.createdAt', 'ASC');

        $entries = $qb->getQuery()->getResult();

        // Calculate total hours
        $totalSeconds = $this->timeEntryRepository->getTotalHoursByDay($date);
        $totalHours = floor($totalSeconds / 3600);
        $totalMinutes = floor(($totalSeconds % 3600) / 60);

        // Build TXT content
        $dateObj = new \DateTime($date);
        $content = "═══════════════════════════════════════════════════════════════════\n";
        $content .= "                   REGISTRO DE HORAS\n";
        $content .= "═══════════════════════════════════════════════════════════════════\n\n";
        $content .= "Fecha: " . $dateObj->format('l, d \d\e F \d\e Y') . "\n";
        $content .= "Generado: " . (new \DateTime())->format('d/m/Y H:i') . "\n";
        $content .= "Expedido por: FileManager\n\n";
        $content .= "───────────────────────────────────────────────────────────────────\n\n";

        if (count($entries) > 0) {
            foreach ($entries as $index => $entry) {
                $task = $entry->getTask();
                $taskName = $task ? $task->getName() : 'Sin tarea asignada';

                $content .= ($index + 1) . ". " . strtoupper($taskName) . "\n";
                $content .= str_repeat("─", 67) . "\n";

                if ($task && $task->getProject()) {
                    $content .= "   Proyecto: " . $task->getProject()->getName() . "\n";
                }

                if ($task && $task->getTicketNumber()) {
                    $content .= "   Ticket: " . $task->getTicketNumber() . "\n";
                }

                if ($entry->getDescription()) {
                    $content .= "   Descripción: " . $entry->getDescription() . "\n";
                }

                if ($entry->getStartTime() && $entry->getEndTime()) {
                    $content .= "   Horario: " . $entry->getStartTime()->format('H:i') . " - " . $entry->getEndTime()->format('H:i') . "\n";
                }

                if ($entry->getDurationSeconds() > 0) {
                    $content .= "   Duración: " . $entry->getFormattedDuration() . "\n";
                }

                $content .= "\n";
            }

            $content .= "═══════════════════════════════════════════════════════════════════\n";
            $content .= "TOTAL DEL DÍA: {$totalHours}h {$totalMinutes}m\n";
            $content .= "═══════════════════════════════════════════════════════════════════\n";
        } else {
            $content .= "No hay registros de horas para esta fecha.\n\n";
            $content .= "═══════════════════════════════════════════════════════════════════\n";
        }

        return $content;
    }
}
