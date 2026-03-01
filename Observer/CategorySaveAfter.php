<?php

declare(strict_types=1);

namespace Mygento\IndexNow\Observer;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Mygento\IndexNow\Model\Service\IndexNowSender;
use Psr\Log\LoggerInterface;

class CategorySaveAfter implements ObserverInterface
{
    public function __construct(
        private UrlFinderInterface $urlFinder,
        private StoreManagerInterface $storeManager,
        private IndexNowSender $indexNowSender,
        private LoggerInterface $logger,
    ) {}

    public function execute(Observer $observer): void
    {
        $category = $observer->getEvent()->getCategory();
        if (!$category instanceof CategoryInterface || !$category->getId()) {
            return;
        }

        $storeIds = $category->getStoreIds();
        if (empty($storeIds)) {
            $stores = $this->storeManager->getStores();
            $storeIds = array_map(fn($s) => (int) $s->getId(), $stores);
        }

        foreach ($storeIds as $storeId) {
            try {
                $rewrite = $this->urlFinder->findOneByData([
                    UrlRewrite::ENTITY_ID => $category->getId(),
                    UrlRewrite::ENTITY_TYPE => 'category',
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
                    '[IndexNow] Error generating/sending the URL for category %s on store %d : %s',
                    (string) $category->getId(),
                    (int) $storeId,
                    $e->getMessage(),
                ));
            }
        }
    }
}
