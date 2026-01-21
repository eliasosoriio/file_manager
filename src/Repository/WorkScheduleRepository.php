<?php

namespace App\Repository;

use App\Entity\WorkSchedule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WorkSchedule>
 */
class WorkScheduleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WorkSchedule::class);
    }

    public function save(WorkSchedule $entity): void
    {
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
    }

    public function remove(WorkSchedule $entity): void
    {
        $this->getEntityManager()->remove($entity);
        $this->getEntityManager()->flush();
    }

    /**
     * Get all active schedules ordered by day of week
     */
    public function findActiveSchedules(): array
    {
        return $this->createQueryBuilder('w')
            ->where('w.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('w.dayOfWeek', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get schedules for a specific day
     */
    public function findByDayOfWeek(int $dayOfWeek): array
    {
        return $this->createQueryBuilder('w')
            ->where('w.dayOfWeek = :day')
            ->andWhere('w.isActive = :active')
            ->setParameter('day', $dayOfWeek)
            ->setParameter('active', true)
            ->orderBy('w.startTime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Calculate total expected hours for a week
     */
    public function calculateWeeklyHours(): float
    {
        $schedules = $this->findActiveSchedules();
        $totalHours = 0.0;

        foreach ($schedules as $schedule) {
            $totalHours += $schedule->getTotalHours();
        }

        return $totalHours;
    }

    /**
     * Get expected hours for specific days (array of dayOfWeek numbers)
     */
    public function calculateHoursForDays(array $daysOfWeek): float
    {
        if (empty($daysOfWeek)) {
            return 0.0;
        }

        $schedules = $this->createQueryBuilder('w')
            ->where('w.dayOfWeek IN (:days)')
            ->andWhere('w.isActive = :active')
            ->setParameter('days', $daysOfWeek)
            ->setParameter('active', true)
            ->getQuery()
            ->getResult();

        $totalHours = 0.0;
        foreach ($schedules as $schedule) {
            $totalHours += $schedule->getTotalHours();
        }

        return $totalHours;
    }
}
