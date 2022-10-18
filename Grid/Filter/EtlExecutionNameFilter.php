<?php

namespace Oliverde8\PhpEtlSyliusAdminBundle\Grid\Filter;

use Sylius\Component\Grid\Data\DataSourceInterface;
use Sylius\Component\Grid\Filtering\FilterInterface;

class EtlExecutionNameFilter implements FilterInterface
{
    public function apply(DataSourceInterface $dataSource, string $name, $data, array $options): void
    {
        if ($data['name'] == 'all') {
            $dataSource->restrict($dataSource->getExpressionBuilder()->notLike('name', 'all'));
        } else {
            $dataSource->restrict($dataSource->getExpressionBuilder()->equals('name', $data['name']));
        }
    }
}
