<?php

namespace Oliverde8\PhpEtlSyliusAdminBundle\Repository\Etl;

use App\Repository\SimpleSyliusRepository;
use \Oliverde8\PhpEtlBundle\Repository\EtlExecutionRepository as BaseEtlExecutionRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Oliverde8\PhpEtlSyliusAdminBundle\Entity\Etl\EtlExecution;
use Sylius\Component\Resource\Repository\RepositoryInterface;

class EtlExecutionRepository extends BaseEtlExecutionRepository implements RepositoryInterface
{
    use SimpleSyliusRepository;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry);
    }

    public function createAdminGridQueryBuilder(): QueryBuilder
    {
        $qb = $this->createQueryBuilder('ee');
        $qb
            ->andWhere("ee.name in (:name)")
            ->setParameter('name', EtlExecution::EXPORT_NAMES)
            ->orderBy('ee.startTime', 'desc');

        return $qb;
    }

    public function getByStatus(array $statuses): array
    {
        $qb = $this->createQueryBuilder('ee');
        $qb
            ->andWhere('ee.status in (:statuses)')
            ->setParameter('statuses', $statuses);

        return $qb->getQuery()->getResult();
    }
}
