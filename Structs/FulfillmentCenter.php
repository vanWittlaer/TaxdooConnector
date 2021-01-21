<?php


namespace VanWittlaerTaxdooConnector\Structs;


class FulfillmentCenter implements \JsonSerializable
{
    use TraitTaxdooHelper;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $code;

    /**
     * FulfillmentCenter constructor.
     * @param string $type
     * @param string $code
     */
    public function __construct(string $type, string $code)
    {
        $this->type = $type;
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    public function jsonSerialize()
    {
        $data = [];
        $this->setIfNotNull($data, 'type');
        $this->setIfNotNull($data, 'code');

        return $data;
    }
}