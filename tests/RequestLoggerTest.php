<?php

namespace Skafandri\PerformanceMeterBundle\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Skafandri\PerformanceMeterBundle\RequestLogger;
use Symfony\Component\HttpFoundation\Request;

class RequestLoggerTest extends TestCase
{
    public function test_logs_request()
    {
        $mockLogger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $mockLogger->expects($this->once())
            ->method('info')
            ->with(
                'performance_meter.request',
                array('uri' => 'http://:/', 'duration' => 10)
            );

        $requestLogger = new RequestLogger($mockLogger);
        $requestLogger->logRequest(new Request(), 10);
    }

    public function test_doesnt_break_without_logger()
    {
        $requestLogger = new RequestLogger();
        $requestLogger->logRequest(new Request(), 10);
    }
}
