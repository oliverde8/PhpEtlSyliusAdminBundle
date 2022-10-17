<?php

namespace Oliverde8\PhpEtlSyliusAdminBundle\Controller;

use Oliverde8\PhpEtlSyliusAdminBundle\Repository\Etl\EtlExecutionStatRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class DashboardController extends AbstractController
{
    private EtlExecutionStatRepository $etlExecutionStatRepository;

    public function __construct(
        EtlExecutionStatRepository $etlExecutionStatRepository
    )
    {
        $this->etlExecutionStatRepository = $etlExecutionStatRepository;
    }

    public function indexAction(): Response
    {
        return $this->render('@Oliverde8PhpEtlSyliusAdmin/dashboard/index.html.twig', [
            'totalEtl' => $this->etlExecutionStatRepository->getNbTotalEtl(),
            'totalEtlFailure' => $this->etlExecutionStatRepository->getNbTotalEtlFailure(),
            'totalEtlSuccess' => $this->etlExecutionStatRepository->getNbTotalEtlSuccess(),
            'totalEtlWaiting' => $this->etlExecutionStatRepository->getNbTotalEtlWaiting(),
            'middleSuccessRunTime' => $this->etlExecutionStatRepository->getMiddleSuccessRunTime()
        ]);
    }
}
