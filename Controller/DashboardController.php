<?php

namespace Oliverde8\PhpEtlSyliusAdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class DashboardController extends AbstractController
{
    public function indexAction(): Response
    {
        return $this->render('@Oliverde8PhpEtlSyliusAdmin/dashboard/index.html.twig', []);
    }
}
