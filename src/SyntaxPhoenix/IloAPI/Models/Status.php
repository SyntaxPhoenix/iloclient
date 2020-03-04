<?php

namespace src\SyntaxPhoenix\IloAPI\Models;

use src\SyntaxPhoenix\IloAPI\Models\DataModel;

class Status extends DataModel
{

    /** @var array */
    private $data;

    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->data = $data;
    }

    public function getHealth(): ?string
    {
        return $this->getStringData('Health');
    }

    public function getHealthRollUp(): ?string
    {
        return $this->getStringData('HealthRollUp');
    }

    public function getState(): ?string
    {
        return $this->getStringData('State');
    }

}