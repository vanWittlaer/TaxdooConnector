<?php


namespace VanWittlaerTaxdooConnector\Structs;


use JsonSerializable;

class SupplementaryUnit implements JsonSerializable
{
    /**
     * @var float
     */
    private $value;

    /**
     * @var string
     */
    private $unit;

    /**
     * @return float
     */
    public function getValue(): float
    {
        return $this->value;
    }

    /**
     * @param float $value
     */
    public function setValue(float $value): void
    {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getUnit(): string
    {
        return $this->unit;
    }

    /**
     * @param string $unit
     */
    public function setUnit(string $unit): void
    {
        $this->unit = $unit;
    }

    public function jsonSerialize()
    {
        return [
            'value' => $this->value,
            'unit' => $this->unit,
        ];
    }
}