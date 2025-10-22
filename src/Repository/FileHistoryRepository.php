<?php

namespace App\Repository;

use App\Entity\FileHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FileHistory>
 */
class FileHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FileHistory::class);
    }

    /**
     * Get recent file history
     * @return FileHistory[]
     */
    public function findRecent(int $limit = 20): array
    {
        return $this->createQueryBuilder('f')
            ->orderBy('f.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find history by action type
     * @return FileHistory[]
     */
    public function findByAction(string $action, int $limit = 10): array
    {
        return $this->createQueryBuilder('f')
            ->where('f.action = :action')
            ->setParameter('action', $action)
            ->orderBy('f.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Search in file history
     * @return FileHistory[]
     */
    public function search(string $query): array
    {
        return $this->createQueryBuilder('f')
            ->where('f.filename LIKE :query OR f.path LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('f.createdAt', 'DESC')
            ->setMaxResults(50)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get paginated recent file history
     * @return array{items: FileHistory[], total: int, page: int, perPage: int, totalPages: int}
     */
    public function findRecentPaginated(int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;

        $qb = $this->createQueryBuilder('f');

        // Get total count
        $totalQuery = clone $qb;
        $total = (int) $totalQuery
            ->select('COUNT(f.id)')
            ->getQuery()
            ->getSingleScalarResult();

        // Get paginated results
        $items = $qb
            ->orderBy('f.createdAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($perPage)
            ->getQuery()
            ->getResult();

        $totalPages = (int) ceil($total / $perPage);

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => $totalPages,
        ];
    }
}
