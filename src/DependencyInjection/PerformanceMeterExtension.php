<?php

namespace Skafandri\PerformanceMeterBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class PerformanceMeterExtension extends Extension
{

    public function load(array $configs, ContainerBuilder $container)
    {
        $locator = new FileLocator(array(__DIR__ . '/../../Resources/config'));
        $loader = new XmlFileLoader($container, $locator);

        $config = $this->processConfiguration(new Configuration(), $configs);

        if ($this->isConfigEnabled($container, $config)) {
            $loader->load('services.xml');
        }
    }
}
