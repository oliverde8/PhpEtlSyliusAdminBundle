<?php

namespace Oliverde8\PhpEtlSyliusAdminBundle\Services;

use Oliverde8\PhpEtlBundle\Entity\EtlExecution as BaseEtlExecution;
use Oliverde8\PhpEtlSyliusAdminBundle\Repository\Etl\EtlExecutionRepository;

class DashboardService
{
    protected EtlExecutionRepository $etlExecutionRepository;

    public function __construct(
        EtlExecutionRepository $etlExecutionRepository
    )
    {
        $this->etlExecutionRepository = $etlExecutionRepository;
    }

    public function getEtlExecutionByStatus(array $statuses): array
    {
        return $this->etlExecutionRepository->getByStatus($statuses);
    }

    public function getAllEtExecutions(): array
    {
        return $this->etlExecutionRepository->findAll();
    }

    public function getMiddleRunTimeFromStatuses(array $statuses): string
    {
        $etls = $this->getEtlExecutionByStatus($statuses);

        if (empty($etls)) {
            return '00';
        }

        $totalRunTime = 0;
        foreach ($etls as $etl) {
            /** @var BaseEtlExecution $etl */
            $totalRunTime += $etl->getRunTime();
        }

        $middleRunTime = $totalRunTime / count($etls);
        return $middleRunTime == 0 ? '00' : number_format($middleRunTime, 2);
    }
}
