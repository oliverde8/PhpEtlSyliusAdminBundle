<?php

namespace Oliverde8\PhpEtlSyliusAdminBundle\Form\Type\Etl;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class EtlExecutionType extends AbstractType
{
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

        $builder->get('inputData')
            ->addModelTransformer(new CallbackTransformer(
                function ($tagsAsArray) {
                    return json_encode($tagsAsArray);
                },
                function ($tagsAsString) {
                    return json_decode($tagsAsString, true);
                }
            ))
        ;

        $builder->get('inputOptions')
            ->addModelTransformer(new CallbackTransformer(
                function ($tagsAsArray) {
                    return json_encode($tagsAsArray);
                },
                function ($tagsAsString) {
                    return json_decode($tagsAsString, true);
                }
            ))
        ;
    }
}
