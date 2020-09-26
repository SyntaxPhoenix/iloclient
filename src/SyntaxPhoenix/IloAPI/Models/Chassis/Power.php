<?php

namespace src\SyntaxPhoenix\IloAPI\Models\Chassis;

use src\SyntaxPhoenix\IloAPI\Models\Status;
use src\SyntaxPhoenix\IloAPI\Models\DataModel;
use src\SyntaxPhoenix\IloAPI\Requests\IloRequest;
use src\SyntaxPhoenix\IloAPI\Models\Chassis\Power\PowerMetrics;

class Power extends DataModel
{

    /** @var IloRequest */
    private $request;

    /** @var array */
    private $data;

    public function __construct(IloRequest $request, int $chassis, bool $useCache = true)
    {
        $this->request = $request;
        $this->data = $this->request->get('/redfish/v1/chassis/' . $chassis . '/Power/', $useCache)['body'];
        parent::__construct($this->data);
    }

    public function getFullData(): array
    {
        return $this->data;
    }

    public function getPowerMetrics(): PowerMetrics
    {
        $interval = $this->data['PowerMetrics']['IntervalInMin'];
        $averageWatts = $this->data['PowerMetrics']['AverageConsumedWatts'];
        $maxWatts = $this->data['PowerMetrics']['MaxConsumedWatts'];
        $minWatts = $this->data['PowerMetrics']['MinConsumedWatts'];

        $metrics = new PowerMetrics($interval, $averageWatts, $maxWatts, $minWatts);
        return $metrics;
    }

}