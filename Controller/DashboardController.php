<?php

namespace Oliverde8\PhpEtlSyliusAdminBundle\Controller;

use Oliverde8\PhpEtlBundle\Entity\EtlExecution as BaseEtlExecution;
use Oliverde8\PhpEtlSyliusAdminBundle\Services\DashboardService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class DashboardController extends AbstractController
{
    private DashboardService $dashboardService;

    public function __construct(
        DashboardService $dashboardService
    )
    {
        $this->dashboardService = $dashboardService;
    }

    public function indexAction(): Response
    {
        $totalEtl = $this->dashboardService->getAllEtExecutions();
        $totalEtlFailure = $this->dashboardService->getEtlExecutionByStatus([BaseEtlExecution::STATUS_FAILURE]);
        $totalEtlSuccess = $this->dashboardService->getEtlExecutionByStatus([BaseEtlExecution::STATUS_SUCCESS]);
        $totalEtlWaiting = $this->dashboardService->getEtlExecutionByStatus([BaseEtlExecution::STATUS_WAITING]);

        return $this->render('@Oliverde8PhpEtlSyliusAdmin/dashboard/index.html.twig', [
            'totalEtl' => count($totalEtl),
            'totalEtlFailure' => count($totalEtlFailure),
            'totalEtlSuccess' => count($totalEtlSuccess),
            'totalEtlWaiting' => count($totalEtlWaiting),
            'middleSuccessRunTime' => $this->dashboardService->getMiddleRunTimeFromStatuses([BaseEtlExecution::STATUS_SUCCESS])
        ]);
    }
}
