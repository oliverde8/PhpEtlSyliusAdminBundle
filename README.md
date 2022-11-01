# Php Etl Sylius Admin Bundle

The Php Etl Sylius Admin Bundle allows the usage of [Oliver's PHP Etl Bundle](https://github.com/oliverde8/phpEtlBundle) library in a Sylius environment.

## Dependencies

```
composer require oliverde8/php-etl-sylius-admin-bundle
```

## PhpEtlSyliusAdminBundle config

1. Install this module:
```php
# config/bundles.php

return [
    [...]
    Oliverde8\PhpEtlSyliusAdminBundle\Oliverde8PhpEtlSyliusAdminBundle::class => ['all' => true],
];
```

2. Create Etl_execution table via migrations

3. Configure Etl execution grid:
```yml
# config/sylius_grids/etl_execution.yml
imports:
    - { resource: "@Oliverde8PhpEtlSyliusAdminBundle/Resources/config/sylius_grid.yaml" }
```

4Configure Etl execution resource:
```yml
# config/sylius_resources/etl_execution.yml
imports:
    - { resource: "@Oliverde8PhpEtlSyliusAdminBundle/Resources/config/sylius_resources.yaml" }
```

