<?php

namespace Oliverde8\PhpEtlSyliusAdminBundle\Form\Type\Etl;

use Oliverde8\PhpEtlBundle\Services\ChainProcessorsManager;
use Oliverde8\PhpEtlSyliusAdminBundle\Entity\Etl\EtlExecution;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class EtlExecutionType extends AbstractType
{
    public function __construct(
        protected readonly ChainProcessorsManager $chainProcessorsManager,
    ){}

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', ChoiceType::class, [
                'choices' => $this->getChainNames(),
            ])
            ->add('inputData', TextareaType::class, [
                'required' => false,
            ])
            ->add('inputOptions', TextareaType::class, [
                'required' => false,
            ]);

        $builder->get('inputData', TextareaType::class);

        $builder->get('inputOptions', TextareaType::class);

        $builder->get('name')->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $name = $event->getData();
            if($name && $name != "none") {
                $this->addDefinitionField($name, $event->getForm()->getParent());
            }
        });
        $builder->get('name')->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $name = $event->getData();
            if($name && $name != "none") {
                $this->addDefinitionField($name, $event->getForm()->getParent());
            }
        });
    }

    protected function getChainNames(): array
    {
        $names = ['' => 'none'];
        foreach ($this->chainProcessorsManager->getRewDefinitions() as $definitionName => $definition) {
            $names[$definitionName] = $definitionName;
        }

        return $names;
    }

    protected function addDefinitionField(string $name, Form $form)
    {
        /** @var EtlExecution $etlExecution */
        $etlExecution = $form->getData();
        $form->add('definition', TextareaType::class, [
            'required' => false,
        ]);
    }
}
