<?php

/**
 * @author Mygento Team
 * @copyright 2026 Mygento (https://www.mygento.com)
 * @package Mygento_IndexNow
 */

declare(strict_types=1);

namespace Mygento\IndexNow\Model\Service;

use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mygento\IndexNow\Model\Config;
use Psr\Log\LoggerInterface;

class IndexNowSender
{
    private const array SERVICE_CODES = [
        'yandex',
        'bing',
    ];

    public function __construct(
        private StoreManagerInterface $storeManager,
        private CurlFactory $curlFactory,
        private Config $configHelper,
        private SerializerInterface $serializer,
        private LoggerInterface $logger,
    ) {}

    public function submitUrl(array $urlList): void
    {
        $urlList = array_unique($urlList);
        foreach (self::SERVICE_CODES as $code) {
            if (!$this->configHelper->getEndpointUrl($code)) {
                $this->log('[IndexNow] No endpoint available for ' . $code);
                continue;
            }

            $this->request(
                $code,
                [
                    'host' => $this->getHost(),
                    'key' => $this->configHelper->getApiKey($code),
                    'urlList' => $urlList,
                    'keyLocation' => $this->configHelper->getKeyLocation($code),
                ],
            );
        }
    }

    private function getHost(): string
    {
        $baseUrl = $this->storeManager
            ->getStore()
            ->getBaseUrl(UrlInterface::URL_TYPE_WEB);

        return (string) parse_url($baseUrl, PHP_URL_HOST);
    }

    private function log(string $message, ?string $level = 'info', ?\Throwable $e = null): void
    {
        $debugMode = $this->configHelper->isDebugEnabled();
        if ($debugMode || $level === 'error') {
            $this->logger->log($level, $message, ['exception' => $e]);
        }
    }

    private function request(string $code, array $payload): void
    {
        /** @var Curl $curl */
        $curl = $this->curlFactory->create();

        try {
            $urlList = implode(',', $payload['urlList']);
            $payload = $this->serializer->serialize($payload);
            $this->log("[IndexNow] Sending to: {$code}, urlList {$urlList} ");
            $curl->addHeader('Content-Type', 'application/json');
            $curl->post($this->configHelper->getEndpointUrl($code), $payload);

            $statusCode = $curl->getStatus();
            $responseBody = $curl->getBody();

            if ($statusCode >= 200 && $statusCode < 300) {
                $this->log("[IndexNow] URL submitted successfully: {$urlList}");

                return;
            }

            $this->log("[IndexNow] Failed to submit urlList: {$urlList}. Status: {$statusCode}. Response: {$responseBody}", 'error');
        } catch (\Throwable $e) {
            $this->log("[IndexNow] Exception during URL submission to {$code} : {$e->getMessage()}", 'error', $e);
        }
    }
}
