<?php

namespace Oliverde8\PhpEtlSyliusAdminBundle;

use Oliverde8\PhpEtlSyliusAdminBundle\DependencyInjection\Oliverde8PhpEtlSyliusAdminExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class Oliverde8PhpEtlSyliusAdminBundle extends Bundle
{
    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
    }

    public function getContainerExtension()
    {
        if (null === $this->extension) {
            $this->extension = new Oliverde8PhpEtlSyliusAdminExtension();
        }

        return $this->extension;
    }
}
