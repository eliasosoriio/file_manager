<?php

namespace App\Repository;

use App\Entity\Task;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Task>
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    /**
     * Get tasks grouped by day (date portion of createdAt), newest day first
     * Returns array where key is Y-m-d and value is array of Task
     */
    public function findGroupedByDay(): array
    {
        $qb = $this->createQueryBuilder('t')
            ->orderBy('t.date', 'DESC')
            ->addOrderBy('t.createdAt', 'DESC');

        $tasks = $qb->getQuery()->getResult();

        $grouped = [];

        foreach ($tasks as $task) {
            $day = $task->getDate()->format('Y-m-d');
            if (!isset($grouped[$day])) {
                $grouped[$day] = [];
            }
            $grouped[$day][] = $task;
        }

        return $grouped;
    }

    public function save(Task $task): void
    {
        $em = $this->getEntityManager();

        if (!$task->getId()) {
            $em->persist($task);
        }

        $em->flush();
    }

    public function delete(Task $task): void
    {
        $this->getEntityManager()->remove($task);
        $this->getEntityManager()->flush();
    }

    public function countPending(): int
    {
        return $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->where('t.status = :status')
            ->setParameter('status', 'pending')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Get total hours per day
     */
    public function getTotalHoursByDay(string $day): int
    {
        $qb = $this->createQueryBuilder('t')
            ->select('SUM(t.durationSeconds)')
            ->where('t.date = :date')
            ->setParameter('date', new \DateTime($day));

        $result = $qb->getQuery()->getSingleScalarResult();

        return (int) ($result ?? 0);
    }
}
