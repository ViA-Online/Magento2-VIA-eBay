<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Service;

use GuzzleHttp\Psr7\Uri;
use Magento\Catalog\Api\ProductRepositoryInterface\Proxy as ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ProductRepository\Proxy as ProductRepository;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface\Proxy as StockItemRepositoryInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface\Proxy as StockRegistryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface\Proxy as CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\AddressFactory as CustomerAddressFactory;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Event\ManagerInterface\Proxy as ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\CookieManagerInterface\Proxy as CookieManagerInterface;
use Magento\Quote\Api\CartRepositoryInterface\Proxy as CartRepositoryInterface;
use Magento\Quote\Model\Cart\CurrencyFactory;
use Magento\Quote\Model\Quote\Address\Rate;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteRepository\Proxy as QuoteRepository;
use Magento\Sales\Api\OrderManagementInterface\Proxy as OrderManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface\Proxy as OrderRepositoryInterface;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\Order\AddressFactory as OrderAddressFactory;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\ItemFactory as OrderItemFactory;
use Magento\Sales\Model\Order\Payment\Repository\Proxy as OrderPaymentRepository;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\Order\Shipment\Track;
use Magento\Sales\Model\OrderFactory;
use Magento\Tax\Api\TaxCalculationInterface\Proxy as TaxCalculationInterfaceProxy;
use VIAeBay\Connector\Exception\Product as ProductException;
use VIAeBay\Connector\Exception\Products as ProductsException;
use VIAeBay\Connector\Helper\Configuration;
use VIAeBay\Connector\Helper\OData;
use VIAeBay\Connector\Logger\Logger;
use VIAeBay\Connector\Model\OrderFactory as VIAOrderFactory;
use VIAeBay\Connector\Model\OrderRepository as VIAOrderRepository;
use VIAeBay\Connector\OData\Client;
use VIAeBay\Connector\OData\Request;


class Order
{
    const ORDER_IMPORT_FAILED = '_ORDER_IMPORT_FAILED_';

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    /**
     * @var QuoteRepository
     */
    private $quoteRepository;

    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var Configuration
     */
    private $viaConfigurationHelper;

    /**
     * @var Rate
     */
    private $rate;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var Client
     */
    private $client;
    /**
     * @var OData
     */
    private $oData;

    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var OrderItemFactory
     */
    private $orderItemFactory;

    /**
     * @var OrderManagementInterface
     */
    private $orderManagement;

    /**
     * @var VIAOrderRepository
     */
    private $viaOrderRepository;

    /**
     * @var VIAOrderFactory
     */
    private $viaOrderFactory;

    /**
     * @var CurrencyFactory
     */
    private $currencyFactory;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var CustomerAddressFactory
     */
    private $customerAddressFactory;

    /**
     * @var OrderAddressFactory
     */
    private $orderAddressFactory;

    /**
     * @var OrderPaymentRepository
     */
    private $orderPaymentRepository;

    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @var StockItemRepositoryInterface
     */
    private $stockItemRepository;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var TaxCalculationInterfaceProxy
     */
    private $taxCalculation;

    /**
     * @param ResourceConnection $resourceConnection
     * @param OrderManagementInterface $orderManagement
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderFactory $orderFactory
     * @param OrderItemFactory $orderItemFactory
     * @param OrderAddressFactory $orderAddressFactory
     * @param CustomerAddressFactory $customerAddressFactory
     * @param ProductRepositoryInterface|ProductRepository $productRepository
     * @param StockRegistryInterface $stockRegistry
     * @param StockItemRepositoryInterface $stockItemRepository
     * @param CartRepositoryInterface|QuoteRepository $quoteRepository
     * @param QuoteFactory $quoteFactory
     * @param CustomerFactory $customerFactory ,
     * @param CustomerRepositoryInterface $customerRepository
     * @param CurrencyFactory $currencyFactory
     * @param Rate $shippingRate
     * @param CookieManagerInterface $cookieManager
     * @param ManagerInterface $eventManager
     * @param TaxCalculationInterfaceProxy $taxCalculation
     * @param OrderPaymentRepository $paymentRepository
     * @param VIAOrderRepository $viaOrderRepository
     * @param VIAOrderFactory $viaOrderFactory
     * @param OData $oData
     * @param Configuration $viaConfigurationHelper
     * @param Client $client
     * @param Logger $logger
     */
    public function __construct(ResourceConnection $resourceConnection,
                                OrderManagementInterface $orderManagement,
                                OrderRepositoryInterface $orderRepository,
                                OrderFactory $orderFactory,
                                OrderItemFactory $orderItemFactory,
                                OrderAddressFactory $orderAddressFactory,
                                CustomerAddressFactory $customerAddressFactory,
                                ProductRepositoryInterface $productRepository,
                                StockRegistryInterface $stockRegistry,
                                StockItemRepositoryInterface $stockItemRepository,
                                CartRepositoryInterface $quoteRepository,
                                QuoteFactory $quoteFactory,
                                CustomerFactory $customerFactory,
                                CustomerRepositoryInterface $customerRepository,
                                CurrencyFactory $currencyFactory,
                                Rate $shippingRate,
                                CookieManagerInterface $cookieManager,
                                ManagerInterface $eventManager,
                                TaxCalculationInterfaceProxy $taxCalculation,
                                OrderPaymentRepository $paymentRepository,
                                VIAOrderRepository $viaOrderRepository,
                                VIAOrderFactory $viaOrderFactory,
                                OData $oData,
                                Configuration $viaConfigurationHelper,
                                Client $client,
                                Logger $logger)
    {
        $this->cookieManager = $cookieManager;
        $this->eventManager = $eventManager;
        $this->resourceConnection = $resourceConnection;
        $this->orderFactory = $orderFactory;
        $this->orderItemFactory = $orderItemFactory;
        $this->orderManagement = $orderManagement;
        $this->orderAddressFactory = $orderAddressFactory;
        $this->orderPaymentRepository = $paymentRepository;
        $this->customerAddressFactory = $customerAddressFactory;
        $this->productRepository = $productRepository;
        $this->stockRegistry = $stockRegistry;
        $this->stockItemRepository = $stockItemRepository;
        $this->quoteRepository = $quoteRepository;
        $this->quoteFactory = $quoteFactory;
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->viaConfigurationHelper = $viaConfigurationHelper;
        $this->currencyFactory = $currencyFactory;
        $this->rate = $shippingRate;
        $this->taxCalculation = $taxCalculation;
        $this->logger = $logger;
        $this->client = $client;
        $this->oData = $oData;
        $this->viaOrderRepository = $viaOrderRepository;
        $this->viaOrderFactory = $viaOrderFactory;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Import order.
     *
     * @param array $ids
     */
    public function import(array $ids = null)
    {
        try {
            $this->logger->info('Starting VIAEbay Order Import ');

            $expand = ['Address', 'Buyer', 'SalesOrderItems/Product', 'SalesOrderItems/ProductVariation'];
            $force = false;
            $updateForeignOrderId = true;

            $filter = null;
            if (count($ids)) {
                $clauses = array();
                foreach ($ids as $id) {
                    $clauses [] = "(Id eq " . $id . ")";
                }

                $filter = join(' or ', $clauses);
                $force = true;
            } else {
                $filter = "(ForeignOrderId eq null) or (ForeignOrderId eq '')";

                // Allow cookie update
                $cookieFilter = $this->cookieManager->getCookie('viaebay_order_filter');
                if ($cookieFilter == "null") {
                    $filter = null;
                } else if (strlen($cookieFilter) > 0) {
                    $filter = $cookieFilter;
                }

                $cookieForce = $this->cookieManager->getCookie('viaebay_order_force');
                if ($cookieForce == "true") {
                    $force = true;
                }
            }

            $expandString = join(",", $expand);
            $filterString = $filter == null ? '' : '&$filter=' . $filter;

            $uri = new Uri('SalesOrders?$expand=' . $expandString . $filterString);

            $viaOrders = $this->client->send(new Request('GET', $uri));

            foreach ($viaOrders as $viaOrder) {
                $customOrder = $this->getOrCreateViaOrder($viaOrder);

                try {
                    if (!isset ($viaOrder ['Id'])) {
                        throw new LocalizedException (__("Orders without 'Id' cannot be processed "), $viaOrder);
                    }

                    $orderId = null;

                    if (empty ($viaOrder ['ForeignOrderId']) || $force) {
                        $this->logger->addInfo('Import order');
                        $order = $this->importNewOrder($viaOrder);
                        if ($order != null) {
                            $orderId = $order->getId();

                            $customOrder->setMagentoOrderId($order->getId());
                            $this->viaOrderRepository->save($customOrder);

                            if ($updateForeignOrderId && $orderId) {
                                $this->client->send($this->oData->updateObjectField($viaOrder, 'ForeignOrderId', $orderId));
                            }
                        }
                    } else {
                        $orderId = $viaOrder ['ForeignOrderId'];
                        $this->logger->addInfo(__('Skip known order'), ['magentoOrderId' => $viaOrder ['ForeignOrderId']]);
                    }

                    if ($viaOrder['MonetaryPaymentStatus'] == 1) {
                        $invoice = $this->createInvoice($viaOrder, $orderId);
                        if ($invoice != null) {
                            $this->logger->addInfo(__('Invoice created'), ['invoiceId' => $invoice->getId()]);
                        }
                    }
                } catch (ProductsException $ex) {
                    $this->logger->addError($ex->__toString());
                    if ($updateForeignOrderId) {
                        $this->client->send($this->oData->updateObjectField($viaOrder, 'ForeignOrderId', self::ORDER_IMPORT_FAILED));
                    }
                } catch (ProductException $ex) {
                    $this->logger->error($ex->__toString());
                    if ($updateForeignOrderId) {
                        $this->client->send($this->oData->updateObjectField($viaOrder, 'ForeignOrderId', self::ORDER_IMPORT_FAILED));
                    }
                } catch (\Exception $ex) {
                    $this->logger->error($ex->__toString());
                    if ($updateForeignOrderId) {
                        $this->client->send($this->oData->updateObjectField($viaOrder, 'ForeignOrderId', self::ORDER_IMPORT_FAILED));
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->addError($e->__toString());
        }

        $this->logger->addInfo(__('Completed VIAEbay Order Import'));
    }

    public function getOrCreateViaOrder(array $viaOrder)
    {
        $viaOrderId = $viaOrder ['Id'];
        $buyerName = isset ($viaOrder ['Buyer'] ['BuyerName']) ? $viaOrder ['Buyer'] ['BuyerName'] : '';
        $platformOrderId = $viaOrder ['PlatformOrderId'];
        $checkoutCompleteDate = $this->oData->parseDate('CheckoutCompletionDate', $viaOrder);

        try {
            return $this->viaOrderRepository->getById($viaOrderId);
        } catch (NoSuchEntityException $e) {
            $viaebayOrder = $this->viaOrderFactory->create();
            $viaebayOrder->setHasDataChanges(true);
            $viaebayOrder->setObjectNew(true);
            $viaebayOrder->setData('viaebay_order_id', $viaOrderId);
            $viaebayOrder->setData('buyer_name', $buyerName);
            $viaebayOrder->setData('platform_order_id', $platformOrderId);
            $viaebayOrder->setData('message', '');
            $viaebayOrder->setData('error', '');
            $viaebayOrder->setData('checkout_complete_date', $checkoutCompleteDate);
            $this->viaOrderRepository->save($viaebayOrder);
            return $viaebayOrder;
        }
    }

    /**
     * Create Order On Your Store
     *
     * @param array $viaOrder
     * @return \Magento\Sales\Model\Order
     * @throws \Exception
     */
    protected function importNewOrder(array $viaOrder)
    {
        $connection = $this->resourceConnection->getConnection('sales');
        //$connection->beginTransaction();

        try {
            $prefix = $viaOrder['Address']['Salutation'];
            $firstName = $viaOrder['Address']['Name'];
            $lastName = $viaOrder['Address']['Surname'];
            $email = $viaOrder['Address']['Email'];
            $checkoutCompleteDate = $this->oData->parseDate('CheckoutCompletionDate', $viaOrder);

            $store = $this->viaConfigurationHelper->getStore();

            $customer = $this->getOrCreateCustomer($email, $prefix, $firstName, $lastName);

            $currencyCode = $viaOrder['CurrencyCode'];

            $order = $this->orderFactory->create();
            $order->setStoreId($store->getStoreId());
            $order->setCustomerId($customer->getId());
            $order->setCustomerEmail($email);
            $order->setCustomerPrefix($prefix);
            $order->setCustomerFirstname($firstName);
            $order->setCustomerLastname($lastName);
            $order->setCustomerIsGuest(true);
            $order->setCustomerNoteNotify(false);

            $order->setStoreCurrencyCode($currencyCode);
            $order->setBaseCurrencyCode($currencyCode);
            $order->setOrderCurrencyCode($currencyCode);
            $order->setGlobalCurrencyCode($currencyCode);

            $totalQty = 0;
            $totalWeight = 0;

            //add items in quote
            foreach ($viaOrder ['SalesOrderItems'] as $viaItem) {
                $item = $this->buildOrderItem($viaItem);

                if ($item->getProductId()) {
                    $this->updateMagentoStock($item->getProductId(), $item->getQtyOrdered());
                }

                $totalQty += $item->getQtyOrdered();
                $totalWeight += $item->getRowWeight();

                $order->addItem($item);
            }

            //Set Address to quote
            $viaAddress = $this->convertAddress($viaOrder ['Address'], $email);
            if (isset ($viaOrder ['ShippingAddress'] ['Id'])) {
                $viaShippingAddress = $this->convertAddress($viaOrder ['ShippingAddress'], $email);
            } else {
                $viaShippingAddress = $viaAddress;
            }

            $billingAddress = $this->orderAddressFactory->create();
            $billingAddress->setData($viaAddress);
            $billingAddress->setCustomerId($customer->getId());
            $billingAddress->setAddressType(Address::TYPE_BILLING);
            $order->setBillingAddress($billingAddress);
            $order->getBillingAddress()->setEmail($email);

            $shippingAddress = $this->orderAddressFactory->create();
            $shippingAddress->setData($viaShippingAddress);
            $shippingAddress->setCustomerId($customer->getId());
            $shippingAddress->setAddressType(Address::TYPE_BILLING);
            $order->setShippingAddress($shippingAddress);
            $order->getShippingAddress()->setEmail($email);

            $payment = $this->orderPaymentRepository->create();
            $payment->setMethod('checkmo');

            $price = $viaOrder['TotalPrice'];
            $shippingCost = $viaOrder['ShippingServiceCost'];

            $order->setShippingMethod('freeshipping_freeshipping');
            $order->setShippingDescription('VIA-eBay Shipping');
            $order->setPayment($payment);
            $order->setEmailSent(0);
            $order->setIsVirtual(0);
            $order->setBaseDiscountAmount(0);
            $order->setBaseGrandTotal($price);
            $order->setBaseShippingAmount($shippingCost);
            $order->setBaseShippingInclTax($shippingCost);
            $order->setBaseShippingTaxAmount(0);
            $order->setBaseSubtotal($price);
            $order->setBaseSubtotalInclTax($price);
            $order->setBaseTaxAmount(0);
            $order->setBaseToGlobalRate(1);
            $order->setBaseToOrderRate(1);
            $order->setDiscountAmount(0);
            $order->setGrandTotal($price + $shippingCost);
            $order->setShippingAmount($shippingCost);
            $order->setShippingTaxAmount($shippingCost);
            $order->setStoreToBaseRate(0);
            $order->setStoreToOrderRate(0);
            $order->setSubtotal($price);
            $order->setTaxAmount(0);
            $order->setTotalQtyOrdered($totalQty);
            $order->setBaseShippingDiscountAmount(0);
            $order->setBaseTotalDue($price);
            $order->setShippingDiscountAmount(0);
            $order->setSubtotalInclTax($price);
            $order->setTotalDue($price);
            $order->setWeight($totalWeight);
            $order->setDiscountTaxCompensationAmount(0);
            $order->setBaseDiscountTaxCompensationAmount(0);
            $order->setShippingDiscountTaxCompensationAmount(0);
            $order->setShippingInclTax($shippingCost);
            $order->setCreatedAt($checkoutCompleteDate);

            $this->orderManagement->place($order);

            //$connection->commit();
        } catch (\Exception $e) {
            //$connection->rollBack();
            throw $e;
        }

        return $order;
    }

    /**
     * Update stock in magento.
     * @param $productId
     * @param $qty
     */
    private function updateMagentoStock($productId, $qty)
    {
        if ($productId > 0) {
            $websiteId = $this->viaConfigurationHelper->getStore()->getWebsiteId();
            $item = $this->stockRegistry->getStockItem($productId, $websiteId);
            if ($item != null && $item->getItemId()) {
                $item->setQty($item->getQty() - $qty);
                $this->stockItemRepository->save($item);
            }
        }
    }

    /**
     * Convert via address to magento address.
     *
     * @param array $viaAddress
     *            via address to convert.
     * @param string $email
     * @return array magento address array
     */
    protected function convertAddress(array $viaAddress, $email = '')
    {
        $countryId = strtoupper($viaAddress ['Country']);

        $address = array(
            'company' => $viaAddress ['Company'] == "-" ? "" : $viaAddress ['Company'],
            'firstname' => $viaAddress ['Name'],
            'lastname' => $viaAddress ['Surname'],
            'street' => $viaAddress ['Street'],
            'city' => $viaAddress ['Town'],
            'postcode' => $viaAddress ['PostalCode'],
            'telephone' => $viaAddress ['Phone'],
            'country_id' => $countryId,
            'region_id' => 0,
            'email' => strlen($viaAddress ['Email']) <= 0 ? $email : $viaAddress ['Email']
        );

        return $address;
    }

    /**
     * Create invoice for order.
     * @param array $viaOrder
     * @param int|null $orderId
     * @return Invoice
     * @throws LocalizedException
     */
    public function createInvoice(array $viaOrder, int $orderId = null)
    {
        if ($orderId == null) {
            $orderId = $viaOrder ['ForeignOrderId'];
        }

        try {
            $order = $this->orderRepository->get($orderId);
        } catch (NoSuchEntityException $e) {
            throw new LocalizedException (__("Cannot create invoice without Magento order for order %s", $viaOrder ['Id']), $e);
        }

        $captureCase = Invoice::CAPTURE_OFFLINE;

        switch ($order->getPayment()->getMethod()) {
            case 'viaebay_paypal' :
            case 'viaebay_cashondelivery' :
                if (strlen($viaOrder ['PaymentTransactionId']) > 0) {
                    $captureCase = Invoice::CAPTURE_ONLINE;
                } else {
                    return null; // Skip unpayed
                }
                break;
            default :
                // We don't create unpaid invoices
                return null; // break; if we want to create not captured invoices
        }

        if (!$order->canInvoice()) {
            return null;
        }

        $this->logger->info('Create invoice for m' . $order->getId());

        $invoice = $order->prepareInvoice();

        if (!$invoice->getTotalQty()) {
            throw new LocalizedException(__("Cannot create an invoice without products for order m%s", $order->getId()));
        }

        $invoice->setRequestedCaptureCase($captureCase);
        $invoice->register();

        $this->logger->addInfo(__('Created invoice'), ['invoiceId' => $invoice->getId(), 'orderId' => $order->getId()]);

        try {
            $invoice->setSendEmail(true);
        } catch (\Exception $e) {
            $this->logger->addError((string)$e);
        }

        $this->eventManager->dispatch('viaebay_order_invoice_new', [
            'order' => $order,
            'invoice' => $invoice
        ]);

        return $invoice;
    }

    /**
     * Add tracking number to shipment.
     * @param Track $track
     */
    public function addTrackingNumber(Track $track)
    {
        $shipment = $track->getShipment();
        $order = $shipment->getOrder();

        $uri = new Uri('SalesOrders?$expand=SalesOrderItems&$filter=' . "ForeignOrderId eq '" . $order->getId() . "'");
        $viaOrders = $this->client->send(new Request('GET', $uri));

        $trackingNumber = $track->getTrackNumber();
        $carrierUsed = $track->getCarrierCode(); //TODO: Add mapping

        foreach ($shipment->getItems() as $shipmentItem) {
            $productId = $shipmentItem->getProductId();
            $sku = $shipmentItem->getSku();
            if ($productId == null && $sku == null) {
                continue;
            }

            foreach ($viaOrders as $viaOrder) {
                foreach ($viaOrder ['SalesOrderItems'] as $salesOrderItem) {
                    $currentProductId = $this->resolveProductId($salesOrderItem);
                    $currentSku = $this->resolveSku($salesOrderItem);

                    if ($currentProductId != null && $productId == $currentProductId
                        || $currentSku != null && $sku == $currentSku
                    ) {
                        $trackingNumbers = explode(";", $salesOrderItem ['TrackingNumbers']);
                        $trackingNumbers [] = $trackingNumber;
                        $trackingNumbers = array_slice(array_unique(array_filter($trackingNumbers)), 0, 8);

                        if (!empty($trackingNumbers)) {
                            $delta = [];

                            $this->oData->updateDelta($delta, $salesOrderItem, 'CarrierUsed', $carrierUsed);
                            $this->oData->updateDelta($delta, $salesOrderItem, 'TrackingNumbers', join(";", $trackingNumbers));

                            if (!empty($delta)) {
                                $this->logger->addDebug(__('Set TrackingNumbers'), ['viaOrderId' => $viaOrder ['Id'], 'trackingNumbers' => $trackingNumbers]);
                                try {
                                    $this->client->send($this->oData->updateObject($salesOrderItem, $delta));
                                } catch (\Exception $e) {
                                    $this->logger->addError(__('Failed setting TrackingNumbers'), ['viaOrderId' => $viaOrder ['Id'], 'trackingNumbers' => $trackingNumbers]);
                                    $this->logger->addError($e->__toString());
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Update shipment status.
     * @param Shipment $shipment
     */
    public function updateShipmentStatus(Shipment $shipment)
    {
        $uri = new Uri('SalesOrders?$expand=SalesOrderItems&$filter=' . "ForeignOrderId eq '" . $shipment->getOrder()->getId() . "'");
        $viaOrders = $this->client->send(new Request('GET', $uri));

        foreach ($shipment->getItems() as $shipmentItem) {
            $productId = $shipmentItem->getProductId();
            $sku = $shipmentItem->getSku();
            if ($productId == null && $sku == null) {
                continue;
            }

            foreach ($viaOrders as $viaOrder) {
                foreach ($viaOrder ['SalesOrderItems'] as $salesOrderItem) {
                    if ($salesOrderItem ['ShippingStatus'] == 1) {
                        continue;
                    }

                    $currentProductId = $this->resolveProductId($salesOrderItem);
                    $currentSku = $this->resolveSku($salesOrderItem);

                    if ($currentProductId != null && $productId == $currentProductId
                        || $currentSku != null && $sku == $currentSku
                    ) {
                        try {
                            $this->client->send($this->oData->updateObjectField($salesOrderItem, 'ShippingStatus', '1'));
                            $this->logger->addInfo(__('VIA-eBay shipping status has been set'), ['via_ebay_order_item' => $salesOrderItem ['Id']]);
                        } catch (\Exception $e) {
                            $this->logger->addError(__('VIA-eBay shipping status has not been set'), ['via_ebay_order_item' => $salesOrderItem ['Id']]);
                            $this->logger->addError($e->__toString());
                        }
                    }
                }
            }
        }
    }

    /**
     * Update payment status.
     * @param Invoice $invoice
     */
    public function updatePaid(Invoice $invoice)
    {
        $amount = $invoice->getOrder()->getTotalPaid();
        $amount = strval($amount);

        try {
            $viaOrderReference = $this->viaOrderRepository->getByMagentoId($invoice->getOrderId());
            if ($viaOrderReference != null) {
                $this->client->send($this->oData->updateObjectField('SalesOrders(' . $viaOrderReference->getVIAeBayOrderId() . 'L)', 'PaidAmount', strval($amount)));
                $this->logger->addInfo(__('VIA-eBay payment status has been set'), ['viaOrderReferenceId' => $viaOrderReference->getId(), 'amount' => $amount]);
            }
        } catch (\Exception $e) {
            $this->logger->addError(__('VIA-eBay payment status has not been set'), ['orderId' => $invoice->getOrderId(), 'amount' => $amount]);
            $this->logger->addError($e->__toString());
        }
    }

    /**
     * @param $email
     * @param $prefix
     * @param $firstName
     * @param $lastName
     * @return Customer|CustomerInterface
     */
    protected function getOrCreateCustomer($email, $prefix, $firstName, $lastName)
    {
        $store = $this->viaConfigurationHelper->getStore();

        try {
            return $this->customerRepository->get($email);// load customer by email address
        } catch (NoSuchEntityException $e) {
            //If not available then create this customer
            $customer = $this->customerFactory->create();

            $customer->setWebsiteId($store->getWebsiteId())
                ->setStore($store)
                ->setPrefix($prefix)
                ->setFirstname($firstName)
                ->setLastname($lastName)
                ->setEmail($email)
                ->setPassword(sha1(uniqid()));
            $customer->save();
            //$this->_customerRepository->save($customer);
            return $customer;
        }
    }

    /**
     * Resolve productId from VIA-eBay item
     * @param array $viaItem
     * @return mixed|null
     */
    protected function resolveProductId(array $viaItem)
    {
        $productId = null;
        if (isset ($viaItem ['ProductVariation'])) {
            if (isset ($viaItem ['ProductVariation'] ['ForeignId'])
                && strlen($viaItem ['ProductVariation'] ['ForeignId']) > 0
            ) {
                $productId = $viaItem ['ProductVariation'] ['ForeignId'];
            }
        } elseif ($viaItem['Product']) {
            if (isset ($viaItem ['Product'] ['ForeignId'])
                && strlen($viaItem ['Product'] ['ForeignId']) > 0
            ) {
                $productId = $viaItem ['Product'] ['ForeignId'];
            }
        }

        if (empty($productId) && isset ($viaItem ['ProductVariationForeignId'])) {
            $productId = $viaItem ['ProductVariationForeignId'];
        }

        if (empty($productId) && isset ($viaItem ['ForeignId'])) {
            $productId = $viaItem ['ForeignId'];
        }

        return $productId;
    }

    /**
     * Resolve sku from VIA-eBay item.
     * @param array $viaItem
     * @return mixed|null
     */
    protected function resolveSku(array $viaItem)
    {
        $sku = null;
        if (isset ($viaItem ['ProductVariation'])) {
            if (isset ($viaItem ['ProductVariation'] ['Sku'])
                && strlen($viaItem ['ProductVariation'] ['Sku']) > 0
            ) {
                $sku = $viaItem ['ProductVariation'] ['Sku'];
            }
        } elseif ($viaItem['Product']) {
            if (isset ($viaItem ['Product'] ['Sku'])
                && strlen($viaItem ['Product'] ['Sku']) > 0
            ) {
                $sku = $viaItem ['Product'] ['Sku'];
            }
        }

        if (empty($sku) && isset ($viaItem ['ProductVariationSku'])) {
            $sku = $viaItem ['ProductVariationSku'];
        }

        return $sku;
    }

    /**
     * @param $viaItem
     * @return \Magento\Sales\Model\Order\Item
     */
    protected function buildOrderItem($viaItem)
    {
        $store = $this->viaConfigurationHelper->getStore();

        $productId = $this->resolveProductId($viaItem);
        $sku = $this->resolveSku($viaItem);

        $product = null;
        try {
            if ($productId > 0) {
                $product = $this->productRepository->getById($productId);
            }
        } catch (NoSuchEntityException $e) {
        }

        try {
            if ($product == null && strlen($sku) > 0) {
                $product = $this->productRepository->get($sku);
            }
        } catch (NoSuchEntityException $e) {
        }

        $name = $viaItem['Name'];
        $priceWithTax = $viaItem['Price'];
        $qty = $viaItem['Amount'];
        $rowTotalWithTax = $priceWithTax * $qty;

        $taxPercent = 0;

        if ($product) {
            $weight = $product->getWeight();
            $rowWeight = $weight * $qty;
            $productId = $product->getId();
            if (strlen($sku) == 0) {
                $sku = $product->getSku();
            }

            if ($taxAttribute = $product->getCustomAttribute('tax_class_id')) {
                $productRateId = $taxAttribute->getValue();
                $taxPercent = $this->taxCalculation->getCalculatedRate($productRateId, null, $store->getStoreId());
            }
        } else {
            $weight = 0;
            $rowWeight = 0;
            $this->logger->addWarning(__('Product not found'), ['name' => $name, 'product_id' => $productId, 'sku' => $sku]);
        }

        if ($taxPercent > 0) {
            $taxFactor = 1 + $taxPercent / 100;
            $priceWithoutTax = $priceWithTax / $taxFactor;
            $rowTotalWithoutTax = $rowTotalWithTax / $taxFactor;
        } else {
            $priceWithoutTax = $priceWithTax;
            $rowTotalWithoutTax = $rowTotalWithTax;
        }

        $taxAmount = $priceWithTax - $priceWithoutTax;

        $item = $this->orderItemFactory->create();

        $item->setStoreId($store->getStoreId());
        $item->setProductId($productId);
        $item->setSku($sku);
        $item->setName($name);
        $item->setPrice($priceWithoutTax);
        $item->setBasePrice($priceWithoutTax);
        $item->setOriginalPrice($priceWithoutTax);
        $item->setBaseOriginalPrice($priceWithoutTax);
        $item->setPriceInclTax($priceWithTax);
        $item->setBasePriceInclTax($priceWithTax);
        $item->setBaseRowTotalInclTax($rowTotalWithoutTax);
        $item->setDiscountTaxCompensationAmount(0);
        $item->setBaseDiscountTaxCompensationAmount(0);
        $item->setQtyOrdered($qty);
        $item->setProductType(Type::TYPE_SIMPLE);
        $item->setWeight($weight);
        $item->setIsQtyDecimal(false);
        $item->setTaxAmount($taxAmount);
        $item->setTaxPercent($taxPercent);
        $item->setRowTotal($rowTotalWithoutTax);
        $item->setBaseRowTotal($rowTotalWithoutTax);
        $item->setRowTotalInclTax($rowTotalWithTax);
        $item->setRowWeight($rowWeight);
        $item->setFreeShipping(0);
        $item->setGiftMessageAvailable(2);
        return $item;
    }
}
