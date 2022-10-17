<?php

namespace Oliverde8\PhpEtlSyliusAdminBundle\Repository\Etl;

use Oliverde8\PhpEtlBundle\Entity\EtlExecution as BaseEtlExecution;
use Oliverde8\PhpEtlSyliusAdminBundle\Repository\Etl\EtlExecutionRepository;

class EtlExecutionStatRepository
{
    protected EtlExecutionRepository $etlExecutionRepository;

    public function __construct(
        EtlExecutionRepository $etlExecutionRepository
    )
    {
        $this->etlExecutionRepository = $etlExecutionRepository;
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

    public function getMiddleSuccessRunTime(): string
    {
        $etls = $this->etlExecutionRepository->getEtlExecutionByStatus([BaseEtlExecution::STATUS_SUCCESS]);

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
