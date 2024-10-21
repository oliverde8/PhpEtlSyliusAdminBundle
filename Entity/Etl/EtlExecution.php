<?php

namespace Oliverde8\PhpEtlSyliusAdminBundle\Entity\Etl;

use \Oliverde8\PhpEtlBundle\Entity\EtlExecution as BaseEtlExecution;
use Oliverde8\PhpEtlSyliusAdminBundle\Repository\Etl\EtlExecutionRepository;
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
