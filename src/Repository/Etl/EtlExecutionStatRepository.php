<?php

declare(strict_types=1);

namespace Oliverde8\PhpEtlSyliusAdminBundle\Repository\Etl;

use Oliverde8\PhpEtlBundle\Entity\EtlExecution as BaseEtlExecution;

readonly class EtlExecutionStatRepository
{
    public function __construct(
        private EtlExecutionRepository $etlExecutionRepository
    ) {
    }

    public function getNbTotalEtl(): int
    {
        return $this->etlExecutionRepository->getNbEtlExecution();
    }

    public function getNbTotalEtlFailure(): int
    {
        return $this->etlExecutionRepository->getNbEtlExecution([BaseEtlExecution::STATUS_FAILURE]);
    }

    public function getNbTotalEtlSuccess(): int
    {
        return $this->etlExecutionRepository->getNbEtlExecution([BaseEtlExecution::STATUS_SUCCESS]);
    }

    public function getNbTotalEtlWaiting(): int
    {
        return $this->etlExecutionRepository->getNbEtlExecution([BaseEtlExecution::STATUS_WAITING]);
    }

    public function getAverageSuccessRunTime(): string
    {
        $executions = $this->etlExecutionRepository->getEtlExecutionTotalRunTimeByStatus([BaseEtlExecution::STATUS_SUCCESS]);

        if ($executions['totalExecutions'] === 0) {
            return '00';
        }

        $averageRunTime = (int) $executions['totalRunTime'] / $executions['totalExecutions'];

        return $averageRunTime === 0 ? '00' : number_format($averageRunTime, 2);
    }
}
