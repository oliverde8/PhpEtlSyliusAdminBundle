<?php

declare(strict_types=1);

namespace Oliverde8\PhpEtlSyliusAdminBundle\Etl;

use Oliverde8\PhpEtlBundle\Etl\ChainDefinitionInterface\ChainDefinitionInterface;

final class ChainDefinitionFormatter
{
    public const PHP_PREFIX = 'PHP: ';

    public static function format(string|ChainDefinitionInterface $definition): string
    {
        return $definition instanceof ChainDefinitionInterface ? self::PHP_PREFIX . $definition::class : $definition;
    }
}
