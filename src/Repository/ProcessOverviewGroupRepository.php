<?php

namespace App\Repository;

use App\Entity\ProcessOverviewGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProcessOverviewGroup>
 */
class ProcessOverviewGroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProcessOverviewGroup::class);
    }

    public function findPublished(array $criteria = [], ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        // @todo Use a query for this …
        $items = $this->findBy($criteria, $orderBy, $limit, $offset);

        return array_filter($items, fn (ProcessOverviewGroup $item) => $item->isPublished());
    }
}
