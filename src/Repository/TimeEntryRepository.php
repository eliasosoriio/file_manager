<?php

namespace App\Repository;

use App\Entity\TimeEntry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TimeEntry>
 */
class TimeEntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TimeEntry::class);
    }

    public function save(TimeEntry $timeEntry, bool $flush = true): void
    {
        $this->getEntityManager()->persist($timeEntry);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(TimeEntry $timeEntry, bool $flush = true): void
    {
        $this->getEntityManager()->remove($timeEntry);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Find entries grouped by day
     */
    public function findGroupedByDay(): array
    {
        $entries = $this->createQueryBuilder('te')
            ->leftJoin('te.task', 't')
            ->addSelect('t')
            ->orderBy('te.date', 'DESC')
            ->addOrderBy('te.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        $grouped = [];
        foreach ($entries as $entry) {
            $dateKey = $entry->getDate()->format('Y-m-d');
            if (!isset($grouped[$dateKey])) {
                $grouped[$dateKey] = [];
            }
            $grouped[$dateKey][] = $entry;
        }

        return $grouped;
    }

    /**
     * Get total hours for a specific day
     */
    public function getTotalHoursByDay(string $date): int
    {
        $result = $this->createQueryBuilder('te')
            ->select('SUM(te.durationSeconds)')
            ->where('te.date = :date')
            ->setParameter('date', $date)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) ($result ?? 0);
    }

    /**
     * Count pending entries
     */
    public function countPending(): int
    {
        return $this->createQueryBuilder('te')
            ->select('COUNT(te.id)')
            ->leftJoin('te.task', 't')
            ->where('t.status != :status')
            ->setParameter('status', 'completed')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
