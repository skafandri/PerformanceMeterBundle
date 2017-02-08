<?php

namespace Skafandri\PerformanceMeterBundle;

use Skafandri\PerformanceMeterBundle\DependencyInjection\Compiler\SetSQLLoggerPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class PerformanceMeterBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new SetSQLLoggerPass(), PassConfig::TYPE_AFTER_REMOVING);
    }

}
