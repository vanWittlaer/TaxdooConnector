<?php


namespace VanWittlaerTaxdooConnector\Structs;


use JsonSerializable;

class SenderAddress implements JsonSerializable
{
    use TraitTaxdooHelper;

    /**
     * @var int
     */
    private $id;

    /**
     * @var FulfillmentCenter
     */
    private $fulfillmentCenter;

    /**
     * @var boolean
     */
    private $isStandard;

    /**
     * @var string
     */
    private $fullName;

    /**
     * @var string
     */
    private $street;

    /**
     * @var string
     */
    private $zip;

    /**
     * @var string
     */
    private $city;

    /**
     * @var string
     */
    private $state;

    /**
     * @var string
     */
    private $country;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return FulfillmentCenter
     */
    public function getFulfillmentCenter(): FulfillmentCenter
    {
        return $this->fulfillmentCenter;
    }

    /**
     * @param FulfillmentCenter $fulfillmentCenter
     */
    public function setFulfillmentCenter(FulfillmentCenter $fulfillmentCenter): void
    {
        $this->fulfillmentCenter = $fulfillmentCenter;
    }

    /**
     * @return bool
     */
    public function isStandard(): bool
    {
        return $this->isStandard;
    }

    /**
     * @param bool $isStandard
     */
    public function setIsStandard(bool $isStandard): void
    {
        $this->isStandard = $isStandard;
    }

    /**
     * @return string
     */
    public function getFullName(): string
    {
        return $this->fullName;
    }

    /**
     * @param string $fullName
     */
    public function setFullName(string $fullName): void
    {
        $this->fullName = $fullName;
    }

    /**
     * @return string
     */
    public function getStreet(): string
    {
        return $this->street;
    }

    /**
     * @param string $street
     */
    public function setStreet(string $street): void
    {
        $this->street = $street;
    }

    /**
     * @return string
     */
    public function getZip(): string
    {
        return $this->zip;
    }

    /**
     * @param string $zip
     */
    public function setZip(string $zip): void
    {
        $this->zip = $zip;
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * @param string $city
     */
    public function setCity(string $city): void
    {
        $this->city = $city;
    }

    /**
     * @return string
     */
    public function getState(): string
    {
        return $this->state;
    }

    /**
     * @param string $state
     */
    public function setState(string $state): void
    {
        $this->state = $state;
    }

    /**
     * @return string
     */
    public function getCountry(): string
    {
        return $this->country;
    }

    /**
     * @param string $country
     */
    public function setCountry(string $country): void
    {
        $this->country = $country;
    }

    public function jsonSerialize()
    {
        $data = [];
        $this->setIfNotNull($data, 'id');
        $this->setIfNotNull($data, 'fullName');
        $this->setIfNotNull($data, 'street');
        $this->setIfNotNull($data, 'zip');
        $this->setIfNotNull($data, 'city');
        $this->setIfNotNull($data, 'state');
        $this->setIfNotNull($data, 'country');
        $this->setIfNotNull($data, 'fulfillmentCenter');
        $this->setIfNotNull($data, 'isStandard');

        return $data;
    }
}