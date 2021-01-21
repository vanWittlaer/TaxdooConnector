<?php

namespace VanWittlaerTaxdooConnector\Structs;

use JsonSerializable;

class Platform implements JsonSerializable
{
    use TraitTaxdooHelper;

    /**
     * @var string
     */
    private $identifier;

    /**
     * @var string
     */
    private $transactionNumber;

    public function __construct(?string $identifier = null, ?string $transactionNumber = null)
    {
        $this->setIdentifier($identifier);
        $this->setTransactionNumber($transactionNumber);
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @param string $identifier
     */
    public function setIdentifier(string $identifier): void
    {
        $this->identifier = $identifier;
    }

    /**
     * @return string
     */
    public function getTransactionNumber(): string
    {
        return $this->transactionNumber;
    }

    /**
     * @param string $transactionNumber
     */
    public function setTransactionNumber(string $transactionNumber): void
    {
        $this->transactionNumber = $transactionNumber;
    }

    public function jsonSerialize()
    {
        $data = [
            'identifier' => $this->identifier,
            'transactionNumber' => $this->transactionNumber,
        ];
        $this->setIfNotNull($data, 'refundNumber');
        $this->setIfNotNull($data, 'returnNumber');
        $this->setIfNotNull($data, 'itemNumber');
        $this->setIfNotNull($data, 'itemPosition');

        return $data;
    }
}