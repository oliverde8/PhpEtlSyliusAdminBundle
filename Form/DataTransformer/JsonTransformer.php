<?php

namespace Oliverde8\PhpEtlSyliusAdminBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

class JsonTransformer implements DataTransformerInterface
{

    public function transform($value)
    {
        if (is_null($value) || $value == '[]') {
            return '[]';
        }

        return json_encode($value);
    }

    public function reverseTransform($value)
    {
        if (is_null($value)) {
            return '[]';
        }

        return json_decode($value, true);
    }
}
