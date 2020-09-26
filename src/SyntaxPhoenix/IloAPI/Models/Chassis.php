<?php

namespace src\SyntaxPhoenix\IloAPI\Models;

use src\SyntaxPhoenix\IloAPI\Models\Status;
use src\SyntaxPhoenix\IloAPI\Models\DataModel;
use src\SyntaxPhoenix\IloAPI\Requests\IloRequest;

class Chassis extends DataModel
{

    /** @var IloRequest */
    private $request;

    /** @var array */
    private $data;

    public function __construct(IloRequest $request, int $chassis, bool $useCache = true)
    {
        $this->request = $request;
        $this->data = $this->request->get('/redfish/v1/chassis/' . $chassis . '/', $useCache)['body'];
        parent::__construct($this->data);
    }

    public function getFullData(): array
    {
        return $this->data;
    }

    public function getChassisType(): string
    {
        return $this->data['ChassisType'];
    }

    public function getIndicatorLED(): ?string
    {
        return $this->getStringData('IndicatorLED');
    }

    public function getManufacturer(): string
    {
        return $this->data['Manufacturer'];
    }

    public function getModel(): string
    {
        return $this->data['Model'];
    }

    public function getPartNumber(): ?string
    {
        return $this->getStringData('PartNumber');
    }

    public function getSKU(): string
    {
        return $this->data['SKU'];
    }

    public function getSerialNumber(): string
    {
        return $this->data['SerialNumber'];
    }

    public function getStatus(): Status
    {
        return new Status($this->data['Status']);
    }

    public function getVersion(): ?string
    {
        return $this->getStringData('Version');
    }

}