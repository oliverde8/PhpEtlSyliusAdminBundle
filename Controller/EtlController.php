<?php

namespace Oliverde8\PhpEtlSyliusAdminBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Oliverde8\PhpEtlBundle\Entity\EtlExecution as BaseEtlExecution;
use Oliverde8\PhpEtlBundle\Message\EtlExecutionMessage;
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
     * @param int $id
     * @return Response
     * @throws EtlExecutionException
     */
    public function ExecuteAction(int $id): Response
    {
        $etlExecution = $this->etlExecutionRepository->findOneBy(['id' => $id]);

        if ($etlExecution->getStatus() != BaseEtlExecution::STATUS_WAITING) {
            throw new EtlExecutionException('Etl execution has already been run "%s".', $etlExecution->getId());
        }

        $etlExecution->setStatus($etlExecution::STATUS_QUEUED);
        $this->em->persist($etlExecution);
        $this->em->flush();

        /** @var int $executionId */
        $executionId = $etlExecution->getId();
        $this->messageBus->dispatch(new EtlExecutionMessage($executionId));

        $this->addFlash(
            'success',
            $this->translator->trans('sylius.ui.flash.queued')
        );

        return $this->redirectToRoute("oliverde8_admin_etl_execution_index");
    }

    /**
     * @param string $id
     * @return Response
     */
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
                    'filename' => $pathInfo['filename'],
                    'filetype' => $pathInfo['extension'] == 'log' ? 'log' : 'result'
                ];
            }
        }

        return $this->render('@Oliverde8PhpEtlSyliusAdmin/etl/show/show.html.twig', [
            'etl' => $etl,
            'urls' => $urls
        ]);
    }

    /**
     * @param int $id
     * @param string $filename
     * @return Response
     */
    public function downloadAction(int $id, string $filename): Response
    {
        $etlExecution = $this->etlExecutionRepository->findOneBy(['id' => $id]);
        $context = $this->executionContextFactory->get(['etl' => ['execution' => $etlExecution]]);

        foreach ($context->getFileSystem()->listContents("/") as $file) {
            $pathInfo = pathinfo($file);
            if ($pathInfo['filename'] == $filename) {

                $response = new StreamedResponse(function () use ($context, $file) {
                    $outputStream = fopen('php://output', 'wb');
                    $fileStream = $context->getFileSystem()->readStream($file);
                    if ($outputStream && $fileStream) {
                        stream_copy_to_stream($fileStream, $outputStream);
                    }
                });

                $disposition = HeaderUtils::makeDisposition(
                    HeaderUtils::DISPOSITION_ATTACHMENT,
                    "execution_{$etlExecution->getId()}_" . basename($file)
                );
                $response->headers->set('Content-Disposition', $disposition);

                return $response;
            }
        }

        $this->addFlash(
            'error',
            $this->translator->trans("sylius.ui.etl_execution.flash.download_error")
        );

        return $this->redirectToRoute(
            "oliverde8_admin_etl_execution_show",
            ['id' => $etlExecution->getId()]
        );
    }

    /**
     * @param int $id
     * @return Response
     * @throws EtlExecutionException
     */
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

    /**
     * @param int $id
     * @param Request $request
     * @return Response
     */
    public function editAction(int $id, Request $request): Response
    {
        $etlExecution = $this->etlExecutionRepository->findOneBy(['id' => $id]);

        $form = $this->createForm(EtlExecutionType::class, $etlExecution);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($etlExecution);
            $this->em->flush();

            $this->addFlash(
                'success',
                $this->translator->trans('sylius.ui.etl_execution.edit.flash.success')
            );

            return $this->redirectToRoute('oliverde8_admin_etl_execution_index');
        }

        return $this->render('@Oliverde8PhpEtlSyliusAdmin/etl/edit/edit.html.twig', [
            'form' => $form->createView(),
            'etl' => $etlExecution
        ]);
    }
}
