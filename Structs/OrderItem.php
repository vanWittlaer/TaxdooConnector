<?php


namespace VanWittlaerTaxdooConnector\Structs;


use DateTime;
use JsonSerializable;

class OrderItem implements JsonSerializable
{
    use TraitTaxdooHelper;

    /**
     * @var int
     */
    private $quantity;

    /**
     * @var float
     */
    private $weight;

    /**
     * @var string
     */
    private $productIdentifier;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $commodityCode;

    /**
     * @var float
     */
    private $itemPrice;

    /**
     * @var float
     */
    private $shipping;

    /**
     * @var float
     */
    private $giftWrap;

    /**
     * @var float
     */
    private $discount;

    /**
     * @var string
     */
    private $trackingNumber;

    /**
     * @var string
     */
    private $sourceItemNumber;

    /**
     * @var string
     */
    private $channelItemNumber;

    /**
     * @var FulfillmentCenter
     */
    private $fulfillmentCenter;

    /**
     * @var float
     */
    private $purchasePrice;

    /**
     * @var DateTime
     */
    private $sentDate;

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     */
    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }

    /**
     * @return float
     */
    public function getWeight(): float
    {
        return $this->weight;
    }

    /**
     * @param float $weight
     */
    public function setWeight(float $weight): void
    {
        $this->weight = $weight;
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
    public function getItemPrice(): float
    {
        return $this->itemPrice;
    }

    /**
     * @param float $itemPrice
     */
    public function setItemPrice(float $itemPrice): void
    {
        $this->itemPrice = $itemPrice;
    }

    /**
     * @return float
     */
    public function getShipping(): float
    {
        return $this->shipping;
    }

    /**
     * @param float $shipping
     */
    public function setShipping(float $shipping): void
    {
        $this->shipping = $shipping;
    }

    /**
     * @return float
     */
    public function getGiftWrap(): float
    {
        return $this->giftWrap;
    }

    /**
     * @param float $giftWrap
     */
    public function setGiftWrap(float $giftWrap): void
    {
        $this->giftWrap = $giftWrap;
    }

    /**
     * @return float
     */
    public function getDiscount(): float
    {
        return $this->discount;
    }

    /**
     * @param float $discount
     */
    public function setDiscount(float $discount): void
    {
        $this->discount = $discount;
    }

    /**
     * @return string
     */
    public function getTrackingNumber(): string
    {
        return $this->trackingNumber;
    }

    /**
     * @param string $trackingNumber
     */
    public function setTrackingNumber(string $trackingNumber): void
    {
        $this->trackingNumber = $trackingNumber;
    }

    /**
     * @return string
     */
    public function getSourceItemNumber(): string
    {
        return $this->sourceItemNumber;
    }

    /**
     * @param string $sourceItemNumber
     */
    public function setSourceItemNumber(string $sourceItemNumber): void
    {
        $this->sourceItemNumber = $sourceItemNumber;
    }

    /**
     * @return string
     */
    public function getChannelItemNumber(): string
    {
        return $this->channelItemNumber;
    }

    /**
     * @param string $channelItemNumber
     */
    public function setChannelItemNumber(string $channelItemNumber): void
    {
        $this->channelItemNumber = $channelItemNumber;
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
     * @return DateTime
     */
    public function getSentDate(): DateTime
    {
        return $this->sentDate;
    }

    /**
     * @param DateTime $sentDate
     */
    public function setSentDate(DateTime $sentDate): void
    {
        $this->sentDate = $sentDate;
    }

    /**
     *
     */
    public function flip2Refund(): void
    {
        $this->flipIfSet('itemPrice');
        $this->flipIfSet('discount');
        $this->flipIfSet('shipping');
        $this->flipIfSet('giftWrap');

        unset($this->purchasePrice, $this->sentDate);
    }

    public function jsonSerialize()
    {
        $data = [
            'quantity' => $this->quantity,
            'productIdentifier' => $this->productIdentifier,
            'description' => $this->description,
            'itemPrice' => $this->itemPrice,
            'sourceItemNumber' => $this->sourceItemNumber,
            'channelItemNumber' => $this->channelItemNumber,
        ];

        $this->setIfNotNull($data, 'weight');
        $this->setIfNotNull($data, 'commodityCode');
        $this->setIfNotNull($data, 'shipping');
        $this->setIfNotNull($data, 'giftWrap');
        $this->setIfNotNull($data, 'discount');
        $this->setIfNotNull($data, 'trackingNumber');
        $this->setIfNotNull($data, 'sourceItemNumber');
        $this->setIfNotNull($data, 'channelItemNumber');
        $this->setIfNotNull($data, 'fulfillmentCenter');
        $this->setIfNotNull($data, 'purchasePrice');
        $this->setIfNotNull($data, 'sentDate');

        return $data;
    }

    /**
     * @param string $var
     */
    private function flipIfSet(string $var)
    {
        if (isset($this->$var) && is_float($this->$var)) {
            $this->$var = -1.0 * $this->$var;
        }
    }
}