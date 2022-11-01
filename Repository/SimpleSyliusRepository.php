<?php

namespace Oliverde8\PhpEtlSyliusAdminBundle\Repository;

use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Sylius\Component\Resource\Model\ResourceInterface;

trait SimpleSyliusRepository
{
    public function add(ResourceInterface $resource): void
    {
        $this->_em->persist($resource);
        $this->_em->flush();
    }

    public function remove(ResourceInterface $resource): void
    {
        if (null !== $this->find($resource->getId())) {
            $this->_em->remove($resource);
            $this->_em->flush();
        }
    }

    public function createPaginator(array $criteria = [], array $sorting = []): iterable
    {
        $resources = $this->findAll();

        return new Pagerfanta(new ArrayAdapter($resources));
    }
}
