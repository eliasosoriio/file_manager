<?php

namespace App\Controller;

use App\Entity\WorkSchedule;
use App\Repository\WorkScheduleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/work-schedule')]
class WorkScheduleController extends AbstractController
{
    public function __construct(
        private WorkScheduleRepository $workScheduleRepository
    ) {
    }

    #[Route('', name: 'app_work_schedule_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('work_schedule/index.html.twig');
    }

    #[Route('/list', name: 'app_work_schedule_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $schedules = $this->workScheduleRepository->findActiveSchedules();

        // Group schedules by day
        $groupedData = [];
        foreach ($schedules as $schedule) {
            $dayOfWeek = $schedule->getDayOfWeek();
            if (!isset($groupedData[$dayOfWeek])) {
                $groupedData[$dayOfWeek] = [];
            }
            $groupedData[$dayOfWeek][] = [
                'id' => $schedule->getId(),
                'dayOfWeek' => $dayOfWeek,
                'startTime' => $schedule->getStartTime()->format('H:i'),
                'endTime' => $schedule->getEndTime()->format('H:i'),
                'isActive' => $schedule->isActive(),
                'totalHours' => $schedule->getTotalHours(),
            ];
        }

        $weeklyHours = $this->workScheduleRepository->calculateWeeklyHours();

        return $this->json([
            'success' => true,
            'schedules' => $groupedData,
            'weeklyHours' => $weeklyHours,
        ]);
    }

    #[Route('/save', name: 'app_work_schedule_save', methods: ['POST'])]
    public function save(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            $dayOfWeek = $data['dayOfWeek'] ?? 0;

            // Always create a new schedule entry for multiple time slots per day
            $schedule = new WorkSchedule();
            $schedule->setDayOfWeek($dayOfWeek);

            $startTime = \DateTime::createFromFormat('H:i', $data['startTime']);
            $endTime = \DateTime::createFromFormat('H:i', $data['endTime']);

            if (!$startTime || !$endTime) {
                return $this->json([
                    'success' => false,
                    'message' => 'Formato de hora inválido',
                ], 400);
            }

            $schedule->setStartTime($startTime);
            $schedule->setEndTime($endTime);
            $schedule->setIsActive(true);

            $this->workScheduleRepository->save($schedule);

            return $this->json([
                'success' => true,
                'message' => 'Horario guardado exitosamente',
                'schedule' => [
                    'id' => $schedule->getId(),
                    'dayOfWeek' => $schedule->getDayOfWeek(),
                    'startTime' => $schedule->getStartTime()->format('H:i'),
                    'endTime' => $schedule->getEndTime()->format('H:i'),
                    'totalHours' => $schedule->getTotalHours(),
                ],
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Error al guardar: ' . $e->getMessage(),
            ], 500);
        }
    }

    #[Route('/{id}/delete', name: 'app_work_schedule_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        try {
            $schedule = $this->workScheduleRepository->find($id);

            if (!$schedule) {
                return $this->json([
                    'success' => false,
                    'message' => 'Horario no encontrado',
                ], 404);
            }

            // Instead of deleting, mark as inactive
            $schedule->setIsActive(false);
            $this->workScheduleRepository->save($schedule);

            return $this->json([
                'success' => true,
                'message' => 'Horario eliminado exitosamente',
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage(),
            ], 500);
        }
    }

    #[Route('/weekly-hours', name: 'app_work_schedule_weekly_hours', methods: ['POST'])]
    public function getWeeklyHours(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $dates = $data['dates'] ?? [];

        if (empty($dates)) {
            return $this->json([
                'success' => true,
                'expectedHours' => 0,
            ]);
        }

        // Convert dates to day of week numbers
        $daysOfWeek = [];
        foreach ($dates as $date) {
            $dateObj = new \DateTime($date);
            $dayOfWeek = (int)$dateObj->format('N'); // 1=Monday, 7=Sunday
            $daysOfWeek[] = $dayOfWeek;
        }

        $daysOfWeek = array_unique($daysOfWeek);
        $expectedHours = $this->workScheduleRepository->calculateHoursForDays($daysOfWeek);

        return $this->json([
            'success' => true,
            'expectedHours' => $expectedHours,
        ]);
    }
}
