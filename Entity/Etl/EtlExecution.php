<?php

namespace Oliverde8\PhpEtlSyliusAdminBundle\Entity\Etl;

use \Oliverde8\PhpEtlBundle\Entity\EtlExecution as BaseEtlExecution;
use Oliverde8\PhpEtlSyliusAdminBundle\Repository\Etl\EtlExecutionRepository;
use Sylius\Component\Resource\Model\ResourceInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EtlExecutionRepository::class)
 * @ORM\Table(name="Etl_execution")
 */
class EtlExecution extends BaseEtlExecution implements ResourceInterface
{
    public function __construct()
    {
        parent::__construct('', '', [], []);
    }
}
