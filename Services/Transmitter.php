<?php


namespace VanWittlaerTaxdooConnector\Services;


use Exception;
use VanWittlaerTaxdooConnector\Components\TaxdooClient;
use VanWittlaerTaxdooConnector\Structs\Order;
use VanWittlaerTaxdooConnector\Structs\TaxdooElement;

class Transmitter
{
    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var TaxdooClient
     */
    private $client;

    /**
     * @var string
     */
    private $endpoint;

    /**
     * @var TaxdooElement[]
     */
    private $taxdooElements = [];

    /**
     * @var int
     */
    private $insertedRows = 0;

    /**
     * Transmitter constructor.
     * @param Configuration $configuration
     * @param TaxdooClient $client
     */
    public function __construct(Configuration $configuration, TaxdooClient $client)
    {
        $this->configuration = $configuration;
        $this->client = $client;
    }

    /**
     * @param string $endpoint
     */
    public function setEndpoint(string $endpoint)
    {
        $this->endpoint = strtolower($endpoint);
    }

    /**
     * @param TaxdooElement|null $taxdooElement
     * @throws Exception
     */
    public function add(?TaxdooElement $taxdooElement)
    {
        if ($taxdooElement instanceof TaxdooElement && $this->shouldBeTransmitted($taxdooElement)) {
            $this->taxdooElements[] = $taxdooElement;
            if (count($this->taxdooElements) >= $this->configuration->get('apiChunkSize')) {
                $this->flush();
            }
        }
    }

    /**
     * @return int
     * @throws Exception
     */
    public function flush(): int
    {
        $count = count($this->taxdooElements);

        if ($count <= 0) {

            return $this->insertedRows;
        }
        $apiTaxdooElements = [];
        for ($i = 0; $i < min($this->configuration->get('apiChunkSize'), $count); $i++) {
            $apiTaxdooElements[] = array_shift($this->taxdooElements);
        }

        $response = $this->client->post($this->endpoint, (object)[$this->endpoint => $apiTaxdooElements]);

        if ($response['status'] === 'success') {
            $this->insertedRows += $response['insertedRows'];
        }

        return $this->insertedRows;
    }

    /**
     * @param TaxdooElement $taxdooElement
     * @return bool
     */
    private function shouldBeTransmitted(TaxdooElement $taxdooElement): bool
    {
        if ($taxdooElement instanceof Order &&
            in_array($taxdooElement->getChannel()->getIdentifier(),
                $this->configuration->get('pluginConfig')['taxdooExcludedChannels'])) {

            return false;
        }

        return true;
    }
}