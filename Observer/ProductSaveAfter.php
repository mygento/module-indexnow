<?php

/**
 * @author Mygento Team
 * @copyright 2026 Mygento (https://www.mygento.ru)
 * @package Mygento_IndexNow
 */

declare(strict_types=1);

namespace Mygento\IndexNow\Observer;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Mygento\IndexNow\Model\Service\IndexNowSender;
use Psr\Log\LoggerInterface;

class ProductSaveAfter implements ObserverInterface
{
    public function __construct(
        private UrlFinderInterface $urlFinder,
        private StoreManagerInterface $storeManager,
        private IndexNowSender $indexNowSender,
        private LoggerInterface $logger,
    ) {}

    public function execute(Observer $observer): void
    {
        $product = $observer->getEvent()->getProduct();
        if (!$product instanceof ProductInterface || !$product->getId()) {
            return;
        }

        $storeIds = $product->getStoreIds();
        if (empty($storeIds)) {
            $stores = $this->storeManager->getStores();
            $storeIds = array_map(fn($s) => (int) $s->getId(), $stores);
        }

        foreach ($storeIds as $storeId) {
            try {
                $rewrite = $this->urlFinder->findOneByData([
                    UrlRewrite::ENTITY_ID => $product->getId(),
                    UrlRewrite::ENTITY_TYPE => 'product',
                    UrlRewrite::STORE_ID => (int) $storeId,
                ]);

                if ($rewrite && $rewrite->getRequestPath()) {
                    $baseUrl = rtrim($this->storeManager->getStore($storeId)->getBaseUrl(), '/');
                    $requestPath = ltrim((string) $rewrite->getRequestPath(), '/');
                    $url = $baseUrl . '/' . $requestPath;

                    $this->indexNowSender->submitUrl($url);
                }
            } catch (\Throwable $e) {
                $this->logger->error(sprintf(
                    '[IndexNow] Error generating/sending the URL for product %s on store %d:%s',
                    (string) $product->getId(),
                    (int) $storeId,
                    $e->getMessage(),
                ));
            }
        }
    }
}
