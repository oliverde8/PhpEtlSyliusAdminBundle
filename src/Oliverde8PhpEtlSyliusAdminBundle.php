<?php

namespace Oliverde8\PhpEtlSyliusAdminBundle;

use Oliverde8\PhpEtlSyliusAdminBundle\DependencyInjection\Oliverde8PhpEtlSyliusAdminExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class Oliverde8PhpEtlSyliusAdminBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
    }

    public function getContainerExtension(): ?ExtensionInterface
    {
        if (null === $this->extension) {
            $this->extension = new Oliverde8PhpEtlSyliusAdminExtension();
        }

        return $this->extension;
    }

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
