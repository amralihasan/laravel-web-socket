<?php

namespace App\WebSockets;

use BeyondCode\LaravelWebSockets\Statistics\Logger\StatisticsLogger;
use Ratchet\ConnectionInterface;

class NullStatisticsLogger implements StatisticsLogger
{
    public function webSocketMessage(ConnectionInterface $connection)
    {
        // Do nothing
    }

    public function apiMessage($appId)
    {
        // Do nothing
    }

    public function connection(ConnectionInterface $connection)
    {
        // Do nothing
    }

    public function disconnection(ConnectionInterface $connection)
    {
        // Do nothing
    }

    public function save()
    {
        // Do nothing
    }
}

