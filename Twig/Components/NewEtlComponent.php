<?php
declare(strict_types=1);

namespace Oliverde8\PhpEtlSyliusAdminBundle\Twig\Components;

use Oliverde8\PhpEtlBundle\Services\ChainProcessorsManager;
use Oliverde8\PhpEtlSyliusAdminBundle\Entity\Etl\EtlExecution;
use Oliverde8\PhpEtlSyliusAdminBundle\Form\Type\Etl\EtlExecutionType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent(name: 'Oliverde8EtlNewForm', template: '@Oliverde8PhpEtlSyliusAdmin/components/newForm.html.twig')]
class NewEtlComponent extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    public function __construct(
        protected readonly ChainProcessorsManager $chainProcessorsManager,
    ){}


    #[LiveProp]
    public ?EtlExecution $initialData;

    protected function instantiateForm(): FormInterface
    {

        return $this->createForm(EtlExecutionType::class, $this->initialData);
    }

    public function getRawDefinition(): string
    {
        if (isset($this->formValues['name'])) {
            return $this->chainProcessorsManager->getRawDefinition($this->formValues['name']);
        }
        return "";
    }
}
