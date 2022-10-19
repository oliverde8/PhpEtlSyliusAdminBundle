<?php

namespace Oliverde8\PhpEtlSyliusAdminBundle\Form\Type\Etl;

use Oliverde8\PhpEtlSyliusAdminBundle\Form\DataTransformer\JsonTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class EtlExecutionType extends AbstractType
{
    protected JsonTransformer $jsonTransformer;

    public function __construct(
        JsonTransformer $jsonTransformer
    )
    {
        $this->jsonTransformer = $jsonTransformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class)
            ->add('username', TextType::class, [
                'required' => false,
            ])
            ->add('inputData', TextareaType::class, [
                'required' => false,
            ])
            ->add('inputOptions', TextareaType::class, [
                'required' => false,
            ])
            ->add('definition', TextareaType::class);

        $builder->get('inputData')->addModelTransformer($this->jsonTransformer);
        $builder->get('inputOptions')->addModelTransformer($this->jsonTransformer);
    }
}
