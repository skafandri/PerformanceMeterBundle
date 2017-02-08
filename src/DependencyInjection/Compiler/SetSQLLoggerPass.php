<?php

namespace Skafandri\PerformanceMeterBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class SetSQLLoggerPass implements CompilerPassInterface
{

    public function process(ContainerBuilder $container)
    {
        foreach ($container->getParameter('doctrine.connections') as $connection) {
            $container->getDefinition($connection . '.configuration')
                ->addMethodCall('setSQLLogger', array(new Reference('performance_meter.logger_chain')));
        }
    }
}
