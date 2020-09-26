<?php

namespace src\SyntaxPhoenix\IloAPI\Models\Chassis\Power;

class PowerMetrics 
{

    /** @var int */
    private $interval;

    /** @var int */
    private $averageWatts;

    /** @var int */
    private $maxWatts;

    /** @var int */
    private $minWatts;

    public function __construct(int $interval, int $averageWatts, int $maxWatts, int $minWatts)
    {
        $this->interval = $interval;
        $this->averageWatts = $averageWatts;
        $this->maxWatts = $maxWatts;
        $this->minWatts = $minWatts;
    }

    public function getInterval(): int
    {
        return $this->interval;
    }

    public function getAverageWatts(): int
    {
        return $this->averageWatts;
    }

    public function getMaxWatts(): int
    {
        return $this->maxWatts;
    }

    public function getMinWatts(): int
    {
        return $this->minWatts;
    }
}