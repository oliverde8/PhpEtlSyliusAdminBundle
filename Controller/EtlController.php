<?php

namespace Oliverde8\PhpEtlSyliusAdminBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Monolog\Level;
use Oliverde8\Component\PhpEtl\ChainBuilder;
use Oliverde8\Component\PhpEtl\Model\ExecutionContext;
use Oliverde8\Component\PhpEtl\Output\MermaidRunOutput;
use Oliverde8\Component\PhpEtl\Output\MermaidStaticOutput;
use Oliverde8\PhpEtlBundle\Entity\EtlExecution as BaseEtlExecution;
use Oliverde8\PhpEtlBundle\Message\EtlExecutionMessage;
use Oliverde8\PhpEtlBundle\Services\ChainProcessorsManager;
use Oliverde8\PhpEtlBundle\Entity\EtlExecution;
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
        $hasLogs = false;
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

        return $this->render('@Oliverde8PhpEtlSyliusAdmin/etl/show/show.html.twig', [
            'etl' => $etl,
            'urls' => $urls,
            'graph' => $this->getGraph($etl),
            'continueUpdate' => ($etl->getStatus() == BaseEtlExecution::STATUS_RUNNING || $etl->getStatus() == BaseEtlExecution::STATUS_WAITING) ? 'true' : 'false',
            'refreshInterval' => $etl->getStatus() == BaseEtlExecution::STATUS_RUNNING ? 5 : 5,
            'logs' => $this->getLogs($context),
        ]);
    }

    public function getGraphAction(string $id)
    {
        $etl = $this->etlExecutionRepository->findOneBy(['id' => $id]);
        if (!$etl) {
            throw $this->createNotFoundException();
        }

        return $this->json([
            'graph' => $this->getGraph($etl),
            'continueUpdate' => ($etl->getStatus() == BaseEtlExecution::STATUS_RUNNING || $etl->getStatus() == BaseEtlExecution::STATUS_WAITING) ? 'true' : 'false',
            'refreshInterval' => $etl->getStatus() == BaseEtlExecution::STATUS_RUNNING ? 5 : 5,
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

    protected function getGraph($etl) : string
    {
        if ($etl->getStepStats()) {
            return (new MermaidRunOutput())->generateGrapText($etl->getStepStats());
        }

        $chainProcessor = $this->chainBuilder->buildChainProcessor(Yaml::parse($etl->getDefinition()));
        return (new MermaidStaticOutput())->generateGrapText($chainProcessor);
    }

    protected function getLogs(ExecutionContext $context): array
    {
        $colors = [
            Level::Debug->value => "gray",
            Level::Info->value => "blue",
            Level::Notice->value => "olive",
            Level::Alert->value => "violet",
            Level::Warning->value => "yellow",
            Level::Critical->value => "orange",
            Level::Error->value => "red",
        ];

        $logs = [];
        if ($context->getFileSystem()->fileExists("execution.log")) {
            $file = $context->getFileSystem()->readStream("execution.log");
            $i = 0;
            while ($i < 250 && $line = fgets($file)) {
                $logs[] = [
                    'color' => $colors[$this->getType($line)->value] ?? '',
                    'message' => $line
                ];
                $i++;
            }
            fclose($file);
        }

        return $logs;
    }

    protected function getType(string $log): Level
    {
        if (str_contains($log, "] etl.INFO")) {
            return Level::Info;
        } else if (str_contains($log, "] etl.ALERT")) {
            return Level::Alert;
        } else if (str_contains($log, "] etl.CRITICAL")) {
            return Level::Critical;
        } else if (str_contains($log, "] etl.DEBUG")) {
            return Level::Debug;
        } else if (str_contains($log, "] etl.EMERGENCY")) {
            return Level::Emergency;
        } else if (str_contains($log, "] etl.NOTICE")) {
            return Level::Notice;
        } else if (str_contains($log, "] etl.WARNING")) {
            return Level::Warning;
        }
    }
}
