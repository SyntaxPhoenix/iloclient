<?php

namespace src\SyntaxPhoenix\IloAPI\Models;

class DataModel
{

    /** @var array */
    private $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getStringData(string $path): ?string
    {
        if (isset($this->data[$path])) {
            return $this->data[$path];
        }
        return null;
    }

    public function getArrayData(string $path): ?array
    {
        if (isset($this->data[$path])) {
            return $this->data[$path];
        }
        return null;
    }

    public function getIntData(string $path): ?int
    {
        if (isset($this->data[$path])) {
            return $this->data[$path];
        }
        return null;
    }

}