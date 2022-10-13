<?php

namespace Oliverde8\PhpEtlSyliusAdminBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Oliverde8PhpEtlSyliusAdminExtension
 */
class Oliverde8PhpEtlSyliusAdminExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config/'));
        $loader->load('services.yaml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        foreach ($config as $key => $value) {
            $container->setParameter($this->getAlias() . $key, $value);
        }
    }

    public function getAlias(): string
    {
        return 'php_etl_sylius_admin';
    }
}
