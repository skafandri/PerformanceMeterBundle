<?php

namespace Skafandri\PerformanceMeterBundle;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Stopwatch\Stopwatch;

class KernelEventsSubscriber implements EventSubscriberInterface
{
    /** @var  RequestLogger[] */
    private $requestLoggers = array();
    /** @var  Stopwatch */
    private $stopwatch;

    /**
     * KernelEventsSubscriber constructor.
     * @param RequestLogger $requestLogger
     */
    public function __construct()
    {
        $this->stopwatch = new Stopwatch();
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if ($event->getRequestType() === HttpKernelInterface::MASTER_REQUEST) {
            $this->stopwatch->start('request');
        }
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        $duration = $this->stopwatch->stop('request')->getDuration();
        foreach ($this->requestLoggers as $logger) {
            $logger->logRequest($event->getRequest(), $duration);
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => 'onKernelRequest',
            KernelEvents::RESPONSE => 'onKernelResponse'
        );
    }

    public function addLogger(RequestLogger $logger)
    {
        $this->requestLoggers[] = $logger;
    }
}
