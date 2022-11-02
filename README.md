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

2. Create EtlExecution table via migrations

3. Create messenger_messages table via migrations

4. Configure Etl execution grid:
```yml
# config/sylius_grids/etl_execution.yml
imports:
    - { resource: "@Oliverde8PhpEtlSyliusAdminBundle/Resources/config/sylius_grid.yaml" }
```

5. Configure Etl execution resource:
```yml
# config/sylius_resources/etl_execution.yml
imports:
    - { resource: "@Oliverde8PhpEtlSyliusAdminBundle/Resources/config/sylius_resources.yaml" }
```

6. Configure EtlExecution Message:
```yml
# config/packages/messenger.yml
framework:
    messenger:
        # Uncomment this (and the failed transport below) to send failed messages to this transport for later handling.
        failure_transport: failed

        transports:
            failed: 'doctrine://default?queue_name=failed'
            generic_with_retry:
                dsn: 'doctrine://default?queue_name=generic_with_retry'
                retry_strategy:
                    max_retries: 3
                    multiplier: 4
                    delay: 3600000 #1H first retry, 4H second retry, 16H third retry (see multiplier) 
            etl_async:
                dsn: 'doctrine://default?queue_name=etl_async'
                retry_strategy:
                    max_retries: 0

        routing:
            'Oliverde8\PhpEtlBundle\Message\EtlExecutionMessage': etl_async
```

7. Create an ETL chain to execute: see the doc from  [this page](https://github.com/oliverde8/phpEtlBundle#creating-an-etl-chain)
