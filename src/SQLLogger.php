<?php

namespace Skafandri\PerformanceMeterBundle;

use Psr\Log\LoggerInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class SQLLogger implements \Doctrine\DBAL\Logging\SQLLogger
{

    /** @var LoggerInterface */
    private $logger;

    /** @var Stopwatch */
    private $stopwatch;

    private $sql;
    private $params;

    /**
     * SQLLogger constructor.
     * @param LoggerInterface|null $logger
     */
    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
        $this->stopwatch = new Stopwatch();
    }

    /**
     * @inheritdoc
     */
    public function startQuery($sql, array $params = null, array $types = null)
    {
        $this->sql = $sql;
        $this->params = $params;
        $this->stopwatch->start('query');
    }

    /**
     * @inheritdoc
     */
    public function stopQuery()
    {
        if (!$this->logger) {
            return;
        }
        $duration = $this->stopwatch->stop('query')->getDuration();
        $context = array(
            'sql' => $this->sql,
            'params' => $this->params,
            'duration' => $duration
        );
        $this->logger->info('performance_meter.sql_query', $context);
    }
}