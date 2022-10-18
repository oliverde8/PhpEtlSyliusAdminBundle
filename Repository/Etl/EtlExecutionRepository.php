<?php

namespace Oliverde8\PhpEtlSyliusAdminBundle\Repository\Etl;

use App\Repository\SimpleSyliusRepository;
use \Oliverde8\PhpEtlBundle\Repository\EtlExecutionRepository as BaseEtlExecutionRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Sylius\Component\Resource\Repository\RepositoryInterface;

class EtlExecutionRepository extends BaseEtlExecutionRepository implements RepositoryInterface
{
    use SimpleSyliusRepository;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry);
    }

    public function getEtlExecutionNames(): array
    {
        $qb = $this->createQueryBuilder('ee');
        $qb
            ->select('ee.name')
            ->groupBy('ee.name');

        return $qb->getQuery()->getResult();
    }

    public function createAdminGridQueryBuilder(): QueryBuilder
    {
        $qb = $this->createQueryBuilder('ee');
        $qb
            ->orderBy('ee.startTime', 'desc');

        return $qb;
    }

    public function getEtlExecutionByStatus(array $statuses): array
    {
        $qb = $this->createQueryBuilder('ee');
        $qb = $this->filterByStatus($qb, $statuses);

        return $qb->getQuery()->getResult();
    }

    public function getNbEtlExecution(array $statuses = []): int
    {
        $qb = $this->createQueryBuilder('ee');
        $qb
            ->select('COUNT(DISTINCT ee.id) AS count');

        if (!empty($statuses)) {
            $qb = $this->filterByStatus($qb, $statuses);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    private function filterByStatus(QueryBuilder $qb, array $statuses): QueryBuilder
    {
        $qb
            ->andWhere('ee.status in (:statuses)')
            ->setParameter('statuses', $statuses);

        return $qb;
    }
}
