<?php

namespace VanWittlaerTaxdooConnector\Structs;

use DateTimeInterface;

class Order extends TaxdooElement
{
    use TraitTaxdooHelper;

    /**
     * @var string
     */
    private $type = 'Sale';

    /**
     * @var Platform
     */
    private $channel;

    /**
     * @var Platform
     */
    private $source;

    /**
     * @var DateTimeInterface
     */
    private $paymentDate;

    /**
     * @var DateTimeInterface
     */
    private $sentDate;

    /**
     * @var DateTimeInterface
     */
    private $arrivalDate;

    /**
     * @var Address
     */
    private $deliveryAddress;

    /**
     * @var Address
     */
    private $billingAddress;

    /**
     * @var SenderAddress
     */
    private $senderAddress;

    /**
     * @var string
     */
    private $buyerVatNumber;

    /**
     * @var array
     */
    private $items = [];

    /**
     * @var string
     */
    private $transactionCurrency;

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
    private $totalDiscount = 0.0;

    /**
     * @var float
     */
    private $adjustmentAmount = 0.0;

    /**
     * @var string
     */
    private $invoiceNumber;

    /**
     * @var DateTimeInterface
     */
    private $invoiceDate;

    /**
     * @var string
     */
    private $invoiceUrl;

    /**
     * @var string
     */
    private $paymentChannel;

    /**
     * @var string
     */
    private $paymentNumber;

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
     * @return Platform
     */
    public function getChannel(): Platform
    {
        return $this->channel;
    }

    /**
     * @param Platform $channel
     */
    public function setChannel(Platform $channel): void
    {
        $this->channel = $channel;
    }

    /**
     * @return Platform
     */
    public function getSource(): Platform
    {
        return $this->source;
    }

    /**
     * @param Platform $source
     */
    public function setSource(Platform $source): void
    {
        $this->source = $source;
    }

    /**
     * @return DateTimeInterface
     */
    public function getPaymentDate(): DateTimeInterface
    {
        return $this->paymentDate;
    }

    /**
     * @param DateTimeInterface $paymentDate
     */
    public function setPaymentDate(DateTimeInterface $paymentDate): void
    {
        $this->paymentDate = $paymentDate;
    }

    /**
     * @return DateTimeInterface
     */
    public function getSentDate(): DateTimeInterface
    {
        return $this->sentDate;
    }

    /**
     * @param DateTimeInterface $sentDate
     */
    public function setSentDate(DateTimeInterface $sentDate): void
    {
        $this->sentDate = $sentDate;
    }

    /**
     * @return DateTimeInterface
     */
    public function getArrivalDate(): DateTimeInterface
    {
        return $this->arrivalDate;
    }

    /**
     * @param DateTimeInterface $arrivalDate
     */
    public function setArrivalDate(DateTimeInterface $arrivalDate): void
    {
        $this->arrivalDate = $arrivalDate;
    }

    /**
     * @return Address
     */
    public function getDeliveryAddress(): Address
    {
        return $this->deliveryAddress;
    }

    /**
     * @param Address $deliveryAddress
     */
    public function setDeliveryAddress(Address $deliveryAddress): void
    {
        $this->deliveryAddress = $deliveryAddress;
    }

    /**
     * @return Address
     */
    public function getBillingAddress(): Address
    {
        return $this->billingAddress;
    }

    /**
     * @param Address $billingAddress
     */
    public function setBillingAddress(Address $billingAddress): void
    {
        $this->billingAddress = $billingAddress;
    }

    /**
     * @return SenderAddress
     */
    public function getSenderAddress(): SenderAddress
    {
        return $this->senderAddress;
    }

    /**
     * @param SenderAddress $senderAddress
     */
    public function setSenderAddress(SenderAddress $senderAddress): void
    {
        $this->senderAddress = $senderAddress;
    }

    /**
     * @return string
     */
    public function getBuyerVatNumber(): string
    {
        return $this->buyerVatNumber;
    }

    /**
     * @param string $buyerVatNumber
     */
    public function setBuyerVatNumber(string $buyerVatNumber): void
    {
        $this->buyerVatNumber = $buyerVatNumber;
    }

    /**
     * @return array
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param array $items
     */
    public function setItems(array $items): void
    {
        $this->items = $items;
    }

    /**
     * @param OrderItem|null $item
     */
    public function addItem(?OrderItem $item): void
    {
        if ($item instanceof OrderItem) {
            $this->items[] = $item;
        }
    }

    /**
     * @return string
     */
    public function getTransactionCurrency(): string
    {
        return $this->transactionCurrency;
    }

    /**
     * @param string $transactionCurrency
     */
    public function setTransactionCurrency(string $transactionCurrency): void
    {
        $this->transactionCurrency = $transactionCurrency;
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
    public function getTotalDiscount(): float
    {
        return $this->totalDiscount;
    }

    /**
     * @param float $totalDiscount
     */
    public function setTotalDiscount(float $totalDiscount): void
    {
        $this->totalDiscount = $totalDiscount;
    }

    /**
     * @param float $discount
     */
    public function addDiscount(float $discount): void
    {
        $this->totalDiscount += $discount;
    }

    /**
     * @return float
     */
    public function getAdjustmentAmount(): float
    {
        return $this->adjustmentAmount;
    }

    /**
     * @param float $adjustmentAmount
     */
    public function setAdjustmentAmount(float $adjustmentAmount): void
    {
        $this->adjustmentAmount = $adjustmentAmount;
    }

    /**
     * @param float $adjustment
     */
    public function addAdjustment(float $adjustment): void
    {
        $this->adjustmentAmount += $adjustment;
    }

    /**
     * @return string
     */
    public function getInvoiceNumber(): string
    {
        return $this->invoiceNumber;
    }

    /**
     * @param string $invoiceNumber
     */
    public function setInvoiceNumber(string $invoiceNumber): void
    {
        $this->invoiceNumber = $invoiceNumber;
    }

    /**
     * @return DateTimeInterface
     */
    public function getInvoiceDate(): DateTimeInterface
    {
        return $this->invoiceDate;
    }

    /**
     * @param DateTimeInterface $invoiceDate
     */
    public function setInvoiceDate(DateTimeInterface $invoiceDate): void
    {
        $this->invoiceDate = $invoiceDate;
    }

    /**
     * @return string
     */
    public function getInvoiceUrl(): string
    {
        return $this->invoiceUrl;
    }

    /**
     * @param string $invoiceUrl
     */
    public function setInvoiceUrl(string $invoiceUrl): void
    {
        $this->invoiceUrl = $invoiceUrl;
    }

    /**
     * @return string
     */
    public function getPaymentChannel(): string
    {
        return $this->paymentChannel;
    }

    /**
     * @param string $paymentChannel
     */
    public function setPaymentChannel(string $paymentChannel): void
    {
        $this->paymentChannel = $paymentChannel;
    }

    /**
     * @return string
     */
    public function getPaymentNumber(): string
    {
        return $this->paymentNumber;
    }

    /**
     * @param string $paymentNumber
     */
    public function setPaymentNumber(string $paymentNumber): void
    {
        $this->paymentNumber = $paymentNumber;
    }

    /**
     *
     */
    public function flip2Refund(): void
    {
        $this->flipIfSet('shipping');
        $this->flipIfSet('giftWrap');
        $this->flipIfSet('totalDiscount');
        $this->flipIfSet('adjustmentAmount');

        unset($this->invoiceNumber, $this->invoiceDate, $this->invoiceUrl);

        $refundItems = [];
        /** @var OrderItem $item */
        foreach ($this->items as $item) {
            $refundItem = clone $item;
            $refundItem->flip2Refund();
            $refundItems[] = $refundItem;
        }
        $this->items = $refundItems;
    }

    /**
     * @return mixed|void
     */
    public function jsonSerialize()
    {

        return $this->filter([
            'type',
            'channel',
            'source',
            'paymentDate',
            'sentDate',
            'arrivalDate',
            'deliveryAddress',
            'billingAddress',
            'senderAddress',
            'buyerVatNumber',
            'items',
            'transactionCurrency',
            'shipping',
            'giftWrap',
            'totalDiscount',
            'adjustmentAmount',
            'invoiceNumber',
            'invoiceDate',
            'invoiceUrl',
            'paymentChannel',
            'paymentNumber',
        ]);
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