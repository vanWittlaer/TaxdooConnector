<?php


namespace VanWittlaerTaxdooConnector\Components\Converter;


use DateTime;
use Exception;
use Shopware\Models\Order\Billing;
use Shopware\Models\Order\Detail;
use Shopware\Models\Order\Document\Document as OrderDocument;
use Shopware\Models\Order\Order as OrderModel;
use Shopware\Models\Order\Shipping;
use Shopware\Models\Payment\Payment;
use stdClass;
use VanWittlaerTaxdooConnector\Services\Configuration;
use VanWittlaerTaxdooConnector\Structs\Address;
use VanWittlaerTaxdooConnector\Structs\Order;
use VanWittlaerTaxdooConnector\Structs\OrderItem;
use VanWittlaerTaxdooConnector\Structs\Platform;
use VanWittlaerTaxdooConnector\Structs\SenderAddress;

class ShopwareConverter implements ConverterInterface
{
    const ITEM_PRODUCT = 0;
    const ITEM_PREMIUM_PRODUCT = 1;
    const ITEM_VOUCHER = 2;
    const ITEM_REBATE = 3;
    const ITEM_SURCHARGE_DISCOUNT = 4;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * ShopwareConverter constructor.
     * @param Configuration $configuration
     */
    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @inheritDoc
     */
    public function convertOrder(stdClass $order, Order $taxdooOrder): void
    {
        /** @var OrderModel $orderModel */
        $orderModel = $order->shopware;

        $this->setSentDate($order, $taxdooOrder);
        $this->setPaidDate($order, $taxdooOrder);

        $taxdooOrder->setChannel($this->getChannel($order));
        $taxdooOrder->setSource(new Platform($this->configuration->get('defaultSourceCode'), $order->number));
        $taxdooOrder->setDeliveryAddress($this->getDeliveryAddress($order));
        $taxdooOrder->setBillingAddress($this->getBillingAddress($order));
        $taxdooOrder->setSenderAddress($this->getSenderAddress($order));
        $taxdooOrder->setTransactionCurrency($orderModel->getCurrency());
        empty($orderModel->getBilling()->getVatId()) ?: $taxdooOrder->setBuyerVatNumber($orderModel->getBilling()->getVatId());
        $taxdooOrder->setShipping($orderModel->getInvoiceShipping());

        $this->setInvoiceDocData($order, $taxdooOrder);
        $this->setPaymentData($order, $taxdooOrder);

        $this->setItems($order, $taxdooOrder);
    }

    /**
     * @inheritDoc
     */
    public function convertRefund(stdClass $order, Order $taxdooRefund): void
    {
        $taxdooRefund->flip2Refund();
        $this->setRefundDate($order, $taxdooRefund);
        $this->setRefundDocData($order, $taxdooRefund);
    }

    /**
     * @param stdClass $order
     * @return Address
     */
    private function getDeliveryAddress(stdClass $order): Address
    {
        $address = new Address();

        /** @var Shipping $shopware */
        $shopware = $order->shopware->getShipping();

        $this->fillAddress($address, $shopware);

        return $address;
    }

    /**
     * @param stdClass $order
     * @return Address
     */
    private function getBillingAddress(stdClass $order): Address
    {
        $address = new Address();

        /** @var Billing $shopware */
        $shopware = $order->shopware->getBilling();

        $this->fillAddress($address, $shopware);

        return $address;
    }

    /**
     * @param stdClass $order
     * @return SenderAddress
     * @throws Exception
     */
    private function getSenderAddress(stdClass $order): SenderAddress
    {
        $address = new SenderAddress();

        $shopId = $order->shopware->getShop()->getId();
        $identifier = $this->configuration->getDefaultWarehouseId($shopId);
        $address->setId($identifier);

        return $address;
    }

    /**
     * @param Address $address
     * @param Billing|Shipping $shopware
     */
    private function fillAddress(Address $address, $shopware)
    {
        $fullName = [];
        if ($shopware->getCompany() !== '') {
            $fullName[] = $shopware->getCompany();
            $fullName[] = '/';
        }
        if (!empty($shopware->getTitle())) {
            $fullName[] = $shopware->getTitle();
        }
        $fullName[] = $shopware->getFirstName();
        $fullName[] = $shopware->getLastName();

        $address->setFullName(implode(' ', $fullName));
        $address->setStreet($shopware->getStreet());
        $address->setZip($shopware->getZipCode());
        $address->setCity($shopware->getCity());
        $shopware->getState() === null ?: $address->setState($shopware->getState()->getName());
        $address->setCountry($shopware->getCountry()->getIso());
    }

    /**
     * @param $order
     * @return Platform
     */
    private function getChannel($order): Platform
    {
        $channel = $this->configuration->get('defaultChannelCode') . '-' . $order->shopware->getShop()->getId();

        return new Platform($channel, $order->number);
    }

    /**
     * @param stdClass $order
     * @param Order $taxdoo
     * @throws Exception
     */
    private function setSentDate(stdClass $order, Order $taxdoo)
    {
        if ($order->sentState === '1' && $order->sentDate !== null) {
            $taxdoo->setSentDate(new DateTime($order->sentDate));
        }
    }

    /**
     * @param stdClass $order
     * @param Order $taxdoo
     * @throws Exception
     */
    private function setPaidDate(stdClass $order, Order $taxdoo)
    {
        if ($order->paidState === '1' && $order->paidDate !== null) {
            $taxdoo->setPaymentDate(new DateTime($order->paidDate));
        }
    }

    private function setRefundDate(stdClass $order, Order $taxdoo)
    {
        if ($order->refundDate !== null) {
            $taxdoo->setPaymentDate(new DateTime($order->refundDate));
        }
    }

    /**
     * @param stdClass $order
     * @param Order $taxdoo
     */
    private function setInvoiceDocData(stdClass $order, Order $taxdoo)
    {
        $this->setDocumentData($order, $taxdoo, $this->configuration->get('pluginConfig')['taxdooInvoiceDocTypes']);
    }

    /**
     * @param stdClass $order
     * @param Order $taxdoo
     */
    private function setRefundDocData(stdClass $order, Order $taxdoo)
    {
        $this->setDocumentData($order, $taxdoo, $this->configuration->get('pluginConfig')['taxdooRefundDocTypes']);
    }

    /**
     * @param stdClass $order
     * @param Order $taxdoo
     * @param array $docIds
     */
    private function setDocumentData(stdClass $order, Order $taxdoo, array $docIds)
    {
        $documents = $order->shopware->getDocuments();

        /** @var OrderDocument $document */
        foreach ($documents as $document) {
            if (in_array($document->getTypeId(), $docIds)) {
                $taxdoo->setInvoiceNumber($document->getDocumentId());
                $taxdoo->setInvoiceDate($document->getDate());

                break;
            }
        }
    }

    /**
     * @param stdClass $order
     * @param Order $taxdoo
     */
    private function setPaymentData(stdClass $order, Order $taxdoo)
    {
        /** @var Payment $payment */
        $payment = $order->shopware->getPayment();

        if ($payment instanceof Payment) {
            $taxdoo->setPaymentChannel($payment->getName());
            empty($order->shopware->getTransactionId()) ?: $taxdoo->setPaymentNumber($order->shopware->getTransactionId());
        }
    }

    /**
     * @param stdClass $order
     * @param Order $taxdoo
     */
    private function setItems(stdClass $order, Order $taxdoo)
    {
        $details = $order->shopware->getDetails();

        /** @var Detail $detail */
        foreach ($details as $detail) {
            switch ($detail->getMode()) {
                case self::ITEM_PRODUCT:
                    $taxdoo->addItem($this->setOrderItem($detail));
                    break;

                case self::ITEM_REBATE:
                case self::ITEM_VOUCHER:
                case self::ITEM_SURCHARGE_DISCOUNT:
                    $this->setDiscount($detail, $taxdoo);
                    break;

                case self::ITEM_PREMIUM_PRODUCT:
                    break;
            }
        }
    }

    /**
     * @param Detail $detail
     * @return OrderItem|null
     */
    private function setOrderItem(Detail $detail): ?OrderItem
    {
        $item = new OrderItem();

        $quantity = $detail->getQuantity();
        if ($quantity === 0 && $detail->getShipped() > 0) {
            $quantity = $detail->getShipped();
        }
        $item->setQuantity($quantity);
        $item->setProductIdentifier($detail->getArticleNumber());
        $item->setDescription($detail->getArticleName());
        $item->setItemPrice($detail->getPrice());
        $item->setSourceItemNumber($detail->getId());
        $item->setChannelItemNumber($detail->getId());

        return $item;
    }

    /**
     * @param Detail $detail
     * @param Order $taxdoo
     */
    private function setDiscount(Detail $detail, Order $taxdoo)
    {
        $price = $detail->getPrice();
        ($price > 0.0) ? $taxdoo->addAdjustment($price) : $taxdoo->addDiscount($price);
    }
}