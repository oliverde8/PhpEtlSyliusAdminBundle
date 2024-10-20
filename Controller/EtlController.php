<?php

namespace Oliverde8\PhpEtlSyliusAdminBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Oliverde8\Component\PhpEtl\ChainBuilder;
use Oliverde8\Component\PhpEtl\Output\MermaidRunOutput;
use Oliverde8\Component\PhpEtl\Output\MermaidStaticOutput;
use Oliverde8\PhpEtlBundle\Entity\EtlExecution as BaseEtlExecution;
use Oliverde8\PhpEtlBundle\Message\EtlExecutionMessage;
use Oliverde8\PhpEtlBundle\Services\ChainProcessorsManager;
use Oliverde8\PhpEtlSyliusAdminBundle\Entity\Etl\EtlExecution;
use Oliverde8\PhpEtlSyliusAdminBundle\Exception\EtlExecutionException;
use Oliverde8\PhpEtlSyliusAdminBundle\Form\Type\Etl\EtlExecutionType;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Oliverde8\PhpEtlSyliusAdminBundle\Repository\Etl\EtlExecutionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Oliverde8\PhpEtlBundle\Services\ExecutionContextFactory;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\Translation\TranslatorInterface;

class EtlController extends AbstractController
{
    public function __construct(
        private readonly EtlExecutionRepository $etlExecutionRepository,
        private readonly ExecutionContextFactory $executionContextFactory,
        private readonly EntityManagerInterface $em,
        private readonly MessageBusInterface $messageBus,
        private readonly TranslatorInterface $translator,
        private readonly ChainBuilder $chainBuilder,
        private readonly ChainProcessorsManager $chainProcessorsManager,
    ) {}

    public function showAction(string $id): Response
    {
        $etl = $this->etlExecutionRepository->findOneBy(['id' => $id]);

        if (is_null($etl)) {
            return $this->redirectToRoute('oliverde8_admin_etl_execution_index');
        }

        $urls = [];
        $context = $this->executionContextFactory->get(['etl' => ['execution' => $etl]]);
        foreach ($context->getFileSystem()->listContents("/") as $file) {
            $pathInfo = pathinfo($file);
            if (isset($pathInfo['extension']) && !empty($pathInfo['extension'])) {
                $urls[] = [
                    'id' => $etl->getId(),
                    'filename' => $pathInfo['filename'] . "." . $pathInfo['extension'],
                    'filetype' => $pathInfo['extension'] == 'log' ? 'log' : 'result'
                ];
            }
        }

        if ($etl->getStepStats()) {
            $chainGraph = (new MermaidRunOutput())->generateGrapText($etl->getStepStats());
        } else {
            $chainProcessor = $this->chainBuilder->buildChainProcessor(Yaml::parse($etl->getDefinition()));
            $chainGraph = (new MermaidStaticOutput())->generateGrapText($chainProcessor);
        }

        return $this->render('@Oliverde8PhpEtlSyliusAdmin/etl/show/show.html.twig', [
            'etl' => $etl,
            'urls' => $urls,
            'graph' => $chainGraph,
        ]);
    }

    public function downloadAction(int $id, string $filename): Response
    {
        $execution = $this->etlExecutionRepository->findOneBy(['id' => $id]);
        $context = $this->executionContextFactory->get(['etl' => ['execution' => $execution]]);

        $file = $context->getFileSystem()->readStream($filename);
        $response = new StreamedResponse(function () use ($file) {
            $outputStream = fopen('php://output', 'wb');
            stream_copy_to_stream($file, $outputStream);
        });

        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            "execution-{$execution->getName()}-{$execution->getId()}-" . $filename
        );
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    public function deleteAction(int $id): Response
    {
        $etlExecution = $this->etlExecutionRepository->findOneBy(['id' => $id]);

        if ($etlExecution->getStatus() != BaseEtlExecution::STATUS_WAITING) {
            throw new EtlExecutionException('Etl execution has already been run "%s".', $etlExecution->getId());
        }

        $this->em->remove($etlExecution);
        $this->em->flush();

        $this->addFlash(
            'success',
            $this->translator->trans('sylius.ui.flash.delete')
        );

        return $this->redirectToRoute('oliverde8_admin_etl_execution_index');
    }

    public function newAction(Request $request): Response
    {
        $etlExecution = new EtlExecution();
        $etlExecution->setUsername($this->getUser()->getUserIdentifier());

        $form = $this->createForm(EtlExecutionType::class, $etlExecution);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $etlExecution->setDefinition($this->chainProcessorsManager->getRawDefinition($etlExecution->getName()));
            $this->em->persist($etlExecution);
            $this->em->flush();

            $executionId = $etlExecution->getId();
            $this->messageBus->dispatch(new EtlExecutionMessage($executionId));

            $this->addFlash(
                'success',
                $this->translator->trans('sylius.ui.etl_execution.edit.flash.success')
            );

            return $this->redirectToRoute('oliverde8_admin_etl_execution_show', ['id' => $etlExecution->getId()]);
        }

        return $this->render('@Oliverde8PhpEtlSyliusAdmin/etl/new/new.html.twig', [
            'form' => $form->createView(),
            'etl' => $etlExecution
        ]);
    }
}
