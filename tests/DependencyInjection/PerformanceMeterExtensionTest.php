<?php

namespace Skafandri\PerformanceMeterBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Skafandri\PerformanceMeterBundle\DependencyInjection\PerformanceMeterExtension;
use Skafandri\PerformanceMeterBundle\KernelEventsSubscriber;
use Skafandri\PerformanceMeterBundle\RequestLogger;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

class PerformanceMeterExtensionTest extends TestCase
{
    public function test_registers_request_loggers()
    {
        $container = $this->createContainer();
        $container->compile();

        foreach (array('logger1', 'logger2') as $logger) {
            $requestLogger1Definition = $container->getDefinition('performance_meter.request.' . $logger);
            $this->assertEquals(RequestLogger::class, $requestLogger1Definition->getClass());
            $this->assertEquals(
                array(
                    new Reference('logger', ContainerInterface::NULL_ON_INVALID_REFERENCE)
                ),
                $requestLogger1Definition->getArguments()
            );
        }
    }

    public function test_registers_kernel_event_subscriber()
    {
        $container = $this->createContainer();
        $container->compile();

        $eventSubscriberDefinition = $container->getDefinition('performance_meter.kernel_events_subscriber');

        $this->assertEquals(KernelEventsSubscriber::class, $eventSubscriberDefinition->getClass());
        $this->assertEquals(
            array(
                array(
                    'addLogger',
                    array(
                        new Reference('performance_meter.request.logger1')
                    )
                ),
                array(
                    'addLogger',
                    array(
                        new Reference('performance_meter.request.logger2')
                    )
                ),
            )
            ,
            $eventSubscriberDefinition->getMethodCalls()
        );
        $this->assertEquals(
            array('kernel.event_subscriber' => array(array())),
            $eventSubscriberDefinition->getTags()
        );
    }

    public function test_registers_nothing_when_disabled()
    {
        $container = $this->createContainer();

        $locator = new FileLocator(__DIR__ . '/Fixtures');
        $loader = new YamlFileLoader($container, $locator);
        $loader->load('disabled.yml');

        $container->compile();

        $this->assertFalse($container->has('performance_meter.request_logger'));
        $this->assertFalse($container->has('performance_meter.kernel_events_subscriber'));
    }

    private function createContainer()
    {
        $container = new ContainerBuilder();

        $container->registerExtension(new PerformanceMeterExtension());

        $locator = new FileLocator(__DIR__ . '/Fixtures');
        $loader = new YamlFileLoader($container, $locator);
        $loader->load('config.yml');


        $container->getCompilerPassConfig()->setOptimizationPasses(array());

        return $container;
    }
}
