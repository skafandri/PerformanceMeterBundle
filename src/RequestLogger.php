<?php

namespace Skafandri\PerformanceMeterBundle;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

class RequestLogger
{
    /** @var  LoggerInterface */
    private $logger;

    /**
     * RequestLogger constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    public function logRequest(Request $request, $duration)
    {
        if (!$this->logger) {
            return;
        }
        $context = array(
            'uri' => $request->getUri(),
            'duration' => $duration
        );
        $this->logger->info('performance_meter.request', $context);
    }
}
