<?php

namespace Oliverde8\PhpEtlSyliusAdminBundle\Form\Type\Filter;

use Oliverde8\PhpEtlSyliusAdminBundle\Repository\Etl\EtlExecutionRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EtlExecutionNameFilterType extends AbstractType
{
    protected EtlExecutionRepository $etlExecutionRepository;

    public function __construct(
        EtlExecutionRepository $etlExecutionRepository
    )
    {
        $this->etlExecutionRepository = $etlExecutionRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name',ChoiceType::class, [
            'choice_loader' => new CallbackChoiceLoader(function () {
                $names = $this->etlExecutionRepository->getEtlExecutionNames();

                $choices = ["app.ui.etl_execution.all" => "all"];
                foreach ($names as $name) {
                    $choices[$name['name']] = $name['name'];
                }

                return $choices;
            }),
            'label' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'range' => [0, 10],
            ])
            ->setAllowedTypes('range', ['array'])
        ;
    }
}
