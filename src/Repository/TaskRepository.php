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

    public function save(Task $task, bool $flush = true): void
    {
        $this->getEntityManager()->persist($task);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Task $task, bool $flush = true): void
    {
        $this->getEntityManager()->remove($task);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Find all active tasks (not completed)
     */
    public function findActiveTasks(?int $projectId = null): array
    {
        $qb = $this->createQueryBuilder('t')
            ->leftJoin('t.project', 'p')
            ->addSelect('p')
            ->where('t.status != :status')
            ->setParameter('status', 'completed')
            ->orderBy('p.name', 'ASC')
            ->addOrderBy('t.name', 'ASC');

        if ($projectId) {
            $qb->andWhere('p.id = :projectId')
                ->setParameter('projectId', $projectId);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Find tasks by project
     */
    public function findByProject(int $projectId): array
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.project', 'p')
            ->addSelect('p')
            ->where('p.id = :projectId')
            ->setParameter('projectId', $projectId)
            ->orderBy('t.status', 'ASC')
            ->addOrderBy('t.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Count tasks by status
     */
    public function countByStatus(string $status): int
    {
        return $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->where('t.status = :status')
            ->setParameter('status', $status)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Count pending tasks
     */
    public function countPending(): int
    {
        return $this->countByStatus('pending');
    }
}
