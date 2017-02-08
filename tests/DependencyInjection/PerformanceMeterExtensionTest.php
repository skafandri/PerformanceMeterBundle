<?php

namespace Skafandri\PerformanceMeterBundle\Tests\DependencyInjection;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\DoctrineExtension;
use Doctrine\DBAL\Logging\LoggerChain;
use PHPUnit\Framework\TestCase;
use Skafandri\PerformanceMeterBundle\DependencyInjection\PerformanceMeterExtension;
use Skafandri\PerformanceMeterBundle\KernelEventsSubscriber;
use Skafandri\PerformanceMeterBundle\PerformanceMeterBundle;
use Skafandri\PerformanceMeterBundle\RequestLogger;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\DependencyInjection\Reference;

class PerformanceMeterExtensionTest extends TestCase
{
    public function test_registers_request_loggers()
    {
        $container = $this->createContainer();
        $container->compile();

        foreach (array('logger1', 'logger2') as $logger) {
            $requestLoggerDefinition = $container->getDefinition('performance_meter.request.' . $logger);
            $this->assertEquals(RequestLogger::class, $requestLoggerDefinition->getClass());
            $this->assertEquals(
                array(
                    new Reference('logger', ContainerInterface::NULL_ON_INVALID_REFERENCE)
                ),
                $requestLoggerDefinition->getArguments()
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

    public function test_registers_logger_chain()
    {
        $container = $this->createContainer();
        $container->compile();

        $loggerChainDefinition = $container->getDefinition('performance_meter.logger_chain');

        $this->assertEquals(LoggerChain::class, $loggerChainDefinition->getClass());
        $this->assertEquals(
            array(
                array(
                    'addLogger',
                    array(
                        new Reference('performance_meter.sql.logger3')
                    )
                ),
            )
            ,
            $loggerChainDefinition->getMethodCalls()
        );
    }

    public function test_calls_doctrine_connection_configuration_setSQLLogger_with_logger_chain()
    {
        $container = $this->createContainer();
        $container->compile();

        foreach (array('conn1', 'conn2') as $name) {
            $configurationDefinition = $container->getDefinition('doctrine.dbal.' . $name . '_connection.configuration');
            $this->assertEquals(
                array(
                    array(
                        'setSQLLogger',
                        array(
                            new Reference('performance_meter.logger_chain')
                        )
                    ),
                )
                ,
                $configurationDefinition->getMethodCalls()
            );
        }
    }

    private function createContainer()
    {
        $container = new ContainerBuilder(new ParameterBag(array('kernel.debug' => false)));

        $container->registerExtension(new DoctrineExtension());
        $container->registerExtension(new PerformanceMeterExtension());

        $locator = new FileLocator(__DIR__ . '/Fixtures');
        $loader = new YamlFileLoader($container, $locator);
        $loader->load('config.yml');

        $container->getCompilerPassConfig()->setOptimizationPasses(array());
        $performanceMeterBundle = new PerformanceMeterBundle();
        $performanceMeterBundle->build($container);

        return $container;
    }
}
