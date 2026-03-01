<?php

declare(strict_types=1);

namespace Mygento\IndexNow\Model\Service;

use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\HTTP\Client\CurlFactory;
use Mygento\IndexNow\Helper\Data as ConfigHelper;
use Psr\Log\LoggerInterface;

class IndexNowSender
{
    private const array SERIVICE_CODES = [
        'yandex',
        'bing',
    ];

    public function __construct(
        private CurlFactory $curlFactory,
        private ConfigHelper $configHelper,
        private LoggerInterface $logger,
    ) {}

    public function submitUrl(string $url): void
    {
        if (!$this->configHelper->isEnabled()) {
            $this->log('[IndexNow] IndexNow service disabled');

            return;
        }
        $apiKey = $this->configHelper->getApiKey();
        $keyLocation = $this->configHelper->getKeyLocation();

        foreach (self::SERIVICE_CODES as $code) {
            if (!$this->configHelper->getEndpointUrl($code)) {
                $this->log('[IndexNow] No endpoint available for ' . $code);
                continue;
            }
            $this->request(
                [
                    'host' => $this->configHelper->getEndpointUrl($code),
                    'key' => $apiKey,
                    'urlList' => [$url],
                    'keyLocation' => $keyLocation,
                ],
            );
        }
    }

    private function log(string $message, ?string $level = 'info'): void
    {
        $debugMode = $this->configHelper->isDebugEnabled();
        if ($debugMode || $level === 'error') {
            $this->logger->log($level, $message);
        }
    }

    private function request(array $payload): void
    {
        $url = implode(', ', $payload['urlList']);
        /** @var Curl $curl */
        $curl = $this->curlFactory->create();

        try {
            $curl->addHeader('Content-Type', 'application/json');
            $curl->post($payload['host'], json_encode($payload));

            $statusCode = $curl->getStatus();
            $responseBody = $curl->getBody();

            if ($statusCode >= 200 && $statusCode < 300) {
                $this->log("[IndexNow] URL submitted successfully: {$url}");

                return;
            }

            $this->log("[IndexNow] Failed to submit URL: {$url}. Status: {$statusCode}. Response: {$responseBody}", 'error');
        } catch (\Throwable $e) {
            $this->log("[IndexNow] Exception during URL submission: {$e->getMessage()}", 'error');
        }
    }
}
