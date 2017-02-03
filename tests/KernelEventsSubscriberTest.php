<?php

namespace Skafandri\PerformanceMeterBundle\Tests;

use PHPUnit\Framework\TestCase;
use Skafandri\PerformanceMeterBundle\KernelEventsSubscriber;
use Skafandri\PerformanceMeterBundle\RequestLogger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class KernelEventsSubscriberTest extends TestCase
{
    public function test_logs_request_on_kernel_response()
    {
        $request = new Request();

        $mockGetResponseEvent = $this->getGetResponseEventMock();
        $mockGetResponseEvent->expects($this->any())
            ->method('getRequestType')
            ->willReturn(HttpKernelInterface::MASTER_REQUEST);

        $mockFilterResponseEvent = $this->getFilterResponseEventMock();
        $mockFilterResponseEvent->expects($this->any())
            ->method('getRequest')
            ->willReturn($request);

        $mockRequestLogger = $this->getMockBuilder(RequestLogger::class)->getMock();
        $mockRequestLogger->expects($this->exactly(2))
            ->method('logRequest')
            ->with($this->callback(function ($requestArgument) use ($request) {
                return $requestArgument === $request;
            }));

        $eventSubscriber = new  KernelEventsSubscriber();
        $eventSubscriber->addLogger($mockRequestLogger);
        $eventSubscriber->addLogger($mockRequestLogger);
        $eventSubscriber->onKernelRequest($mockGetResponseEvent);
        $eventSubscriber->onKernelResponse($mockFilterResponseEvent);
    }

    private function getGetResponseEventMock()
    {
        return $this
            ->getMockBuilder(GetResponseEvent::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getFilterResponseEventMock()
    {
        return $this
            ->getMockBuilder(FilterResponseEvent::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
