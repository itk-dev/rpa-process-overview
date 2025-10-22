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
}
