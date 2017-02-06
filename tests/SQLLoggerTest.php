<?php

namespace Skafandri\PerformanceMeterBundle\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Skafandri\PerformanceMeterBundle\SQLLogger;

class SQLLoggerTest extends TestCase
{
    public function test_logs_request()
    {
        $mockLogger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $mockLogger->expects($this->once())
            ->method('info')
            ->with(
                'performance_meter.sql_query',
                $this->callback(function ($context) {
                    return
                        'sql' === $context['sql'] &&
                        array('params') === $context['params'] &&
                        is_numeric($context['duration']);
                })
            );

        $sqlLogger = new SQLLogger($mockLogger);
        $sqlLogger->startQuery('sql', array('params'));
        $sqlLogger->stopQuery();
    }

    public function test_doesnt_break_without_logger()
    {
        $sqlLogger = new SQLLogger();
        $sqlLogger->startQuery('sql', array('params'));
        $sqlLogger->stopQuery();
    }
}
