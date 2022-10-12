<?php

namespace Oliverde8\PhpEtlSyliusAdminBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Oliverde8\PhpEtlSyliusAdminBundle\Repository\Etl\EtlExecutionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class EtlController extends AbstractController
{
    private EtlExecutionRepository $etlExecutionRepository;

    public function __construct(
        EtlExecutionRepository $etlExecutionRepository
    )
    {
        $this->etlExecutionRepository = $etlExecutionRepository;
    }

    /**
     * @param string $id
     * @return Response
     */
    public function showAction(string $id)
    {
        $etl = $this->etlExecutionRepository->findOneBy(['id' => $id]);

        $this->denyAccessUnlessGranted('admin.index', $etl);

        $urls = [];
//        if (!is_null($etl)) {
//            $context = $this->executionContextFactory->get(['etl' => ['execution' => $etl]]);
//
//            foreach ($context->getFileSystem()->listContents("/") as $file) {
//                $pathInfo = pathinfo($file);
//                if (isset($pathInfo['extension'])) {
//                    $urls[] = [
//                        'id' => $etl->getId(),
//                        'filename' => $pathInfo['filename'],
//                        'filetype' => $pathInfo['extension'] == 'log' ? 'log' : 'result'
//                    ];
//                }
//            }
//        }

        return $this->render('@Oliverde8PhpEtlSyliusAdmin/etl/show.html.twig', [
            'etl' => $etl,
            'urls' => $urls
        ]);
    }
}
