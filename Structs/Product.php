<?php

namespace VanWittlaerTaxdooConnector\Structs;


class Product extends TaxdooElement
{
    use TraitTaxdooHelper;

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $productIdentifier;

    /**
     * @var string
     */
    private $commodityCode;

    /**
     * @var float
     */
    private $purchasePrice;

    /**
     * @var Weight
     */
    private $weight;

    /**
     * @var SupplementaryUnit
     */
    private $supplementaryUnit;

    /**
     * @var string
     */
    private $currency;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $countryOfOrigin;

    /**
     * @var bool
     */
    private $invalidCommodityCode;

    /**
     * @var string
     */
    private $updated;

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
     * @return string
     */
    public function getProductIdentifier(): string
    {
        return $this->productIdentifier;
    }

    /**
     * @param string $productIdentifier
     */
    public function setProductIdentifier(string $productIdentifier): void
    {
        $this->productIdentifier = $productIdentifier;
    }

    /**
     * @return string
     */
    public function getCommodityCode(): string
    {
        return $this->commodityCode;
    }

    /**
     * @param string $commodityCode
     */
    public function setCommodityCode(string $commodityCode): void
    {
        $this->commodityCode = $commodityCode;
    }

    /**
     * @return float
     */
    public function getPurchasePrice(): float
    {
        return $this->purchasePrice;
    }

    /**
     * @param float $purchasePrice
     */
    public function setPurchasePrice(float $purchasePrice): void
    {
        $this->purchasePrice = $purchasePrice;
    }

    /**
     * @return Weight
     */
    public function getWeight(): Weight
    {
        return $this->weight;
    }

    /**
     * @param Weight $weight
     */
    public function setWeight(Weight $weight): void
    {
        $this->weight = $weight;
    }

    /**
     * @return SupplementaryUnit
     */
    public function getSupplementaryUnit(): SupplementaryUnit
    {
        return $this->supplementaryUnit;
    }

    /**
     * @param SupplementaryUnit $supplementaryUnit
     */
    public function setSupplementaryUnit(SupplementaryUnit $supplementaryUnit): void
    {
        $this->supplementaryUnit = $supplementaryUnit;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     */
    public function setCurrency(string $currency): void
    {
        $this->currency = $currency;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getCountryOfOrigin(): string
    {
        return $this->countryOfOrigin;
    }

    /**
     * @param string $countryOfOrigin
     */
    public function setCountryOfOrigin(string $countryOfOrigin): void
    {
        $this->countryOfOrigin = $countryOfOrigin;
    }

    /**
     * @return bool
     */
    public function isInvalidCommodityCode(): bool
    {
        return $this->invalidCommodityCode;
    }

    /**
     * @param bool $invalidCommodityCode
     */
    public function setInvalidCommodityCode(bool $invalidCommodityCode): void
    {
        $this->invalidCommodityCode = $invalidCommodityCode;
    }

    /**
     * @return string
     */
    public function getUpdated(): string
    {
        return $this->updated;
    }

    /**
     * @param string $updated
     */
    public function setUpdated(string $updated): void
    {
        $this->updated = $updated;
    }

    public function jsonSerialize()
    {
        return $this->filter([
            'productIdentifier',
            'commodityCode',
            'purchasePrice',
            'weight',
            'supplementaryUnit',
            'currency',
            'description',
            'countryOfOrigin',
        ]);
    }
}