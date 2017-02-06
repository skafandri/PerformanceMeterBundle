<?php

namespace Skafandri\PerformanceMeterBundle\DependencyInjection;

use Skafandri\PerformanceMeterBundle\RequestLogger;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
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
            $this->registerRequestLoggers($config, $container);
        }
    }

    private function registerRequestLoggers(array $config, ContainerBuilder $container)
    {
        $loggerReference = new Reference('logger', ContainerInterface::NULL_ON_INVALID_REFERENCE);
        $eventSubscriberDefinition = $container->getDefinition('performance_meter.kernel_events_subscriber');
        foreach ($config['loggers'] as $name => $logger) {
            if ($logger['metric'] !== 'request') {
                continue;
            }
            $id = 'performance_meter.request.' . $name;
            $container->register($id, RequestLogger::class)->addArgument($loggerReference);
            $eventSubscriberDefinition->addMethodCall('addLogger', array(new Reference($id)));
        }
    }
}
