<?php

declare(strict_types=1);

namespace Oliverde8\PhpEtlSyliusAdminBundle\Entity\Etl;

use Oliverde8\PhpEtlBundle\Entity\EtlExecution as BaseEtlExecution;
use Sylius\Component\Resource\Model\ResourceInterface;

/**
 * This class should nevery be used, it's only necessery to create a sylius resource.
 */
class EtlExecution extends BaseEtlExecution implements ResourceInterface
{
    public function __construct()
    {
        parent::__construct('', '', [], []);
    }
}
