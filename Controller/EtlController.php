<?php

namespace Oliverde8\PhpEtlSyliusAdminBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Oliverde8\PhpEtlBundle\Entity\EtlExecution;
use Oliverde8\PhpEtlBundle\Message\EtlExecutionMessage;
use Symfony\Component\HttpFoundation\Response;
use Oliverde8\PhpEtlSyliusAdminBundle\Repository\Etl\EtlExecutionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Oliverde8\PhpEtlBundle\Services\ExecutionContextFactory;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class EtlController extends AbstractController
{
    private EtlExecutionRepository $etlExecutionRepository;
    private ExecutionContextFactory $executionContextFactory;
    private EntityManagerInterface $em;
    private MessageBusInterface $messageBus;
    private TranslatorInterface $translator;

    public function __construct(
        EtlExecutionRepository $etlExecutionRepository,
        ExecutionContextFactory $executionContextFactory,
        EntityManagerInterface $em,
        MessageBusInterface $messageBus,
        TranslatorInterface $translator
    )
    {
        $this->etlExecutionRepository = $etlExecutionRepository;
        $this->executionContextFactory = $executionContextFactory;
        $this->em = $em;
        $this->messageBus = $messageBus;
        $this->translator = $translator;
    }
    /**
     * @return Response
     */
    public function ExecuteAction(string $name, ?string $definition = null, ?array $inputData = null, ?array $inputOptions = null): Response
    {
        $execution = new EtlExecution(
            $name,
            $definition ?? '',
            $inputData ?? [[]],
            $inputOptions ?? []
        );

        $execution->setStatus($execution::STATUS_QUEUED);
        $this->em->persist($execution);
        $this->em->flush();

        /** @var int $executionId */
        $executionId = $execution->getId();
        $this->messageBus->dispatch(new EtlExecutionMessage($executionId));

        $this->addFlash(
            'success',
            $this->translator->trans('app.ui.flash.success')
        );

        return $this->redirectToRoute("app_admin_etl_execution_index");
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
        if (!is_null($etl)) {
            $context = $this->executionContextFactory->get(['etl' => ['execution' => $etl]]);

            foreach ($context->getFileSystem()->listContents("/") as $file) {
                $pathInfo = pathinfo($file);
                if (isset($pathInfo['extension'])) {
                    $urls[] = [
                        'id' => $etl->getId(),
                        'filename' => $pathInfo['filename'],
                        'filetype' => $pathInfo['extension'] == 'log' ? 'log' : 'result'
                    ];
                }
            }
        }

        return $this->render('@Oliverde8PhpEtlSyliusAdmin/etl/show/show.html.twig', [
            'etl' => $etl,
            'urls' => $urls
        ]);
    }
}
