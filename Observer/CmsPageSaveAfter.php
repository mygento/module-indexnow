<?php

/**
 * @author Mygento Team
 * @copyright 2026 Mygento (https://www.mygento.ru)
 * @package Mygento_IndexNow
 */

declare(strict_types=1);

namespace Mygento\IndexNow\Observer;

use Magento\Cms\Model\Page;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mygento\IndexNow\Model\Service\IndexNowSender;
use Psr\Log\LoggerInterface;

class CmsPageSaveAfter implements ObserverInterface
{
    public function __construct(
        private StoreManagerInterface $storeManager,
        private ScopeConfigInterface $scopeConfig,
        private IndexNowSender $indexNowSender,
        private LoggerInterface $logger,
    ) {}

    public function execute(Observer $observer): void
    {
        $page = $observer->getEvent()->getObject();
        if (!$page instanceof Page || !$page->getPageId()) {
            return;
        }

        $identifier = $page->getIdentifier();
        if (!$identifier) {
            return;
        }

        $storeIds = $page->getStoreId();
        if ($storeIds === null || in_array(0, (array) $storeIds, true)) {
            $stores = $this->storeManager->getStores();
            $storeIds = array_map(fn($s) => (int) $s->getId(), $stores);
        }

        $urlList = [];

        try {
            foreach ($storeIds as $storeId) {
                $baseUrl = rtrim($this->storeManager->getStore($storeId)->getBaseUrl(), '/');
                $suffix = (string) $this->scopeConfig->getValue(
                    'cms/page/url_suffix',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $storeId,
                );

                if ($identifier === 'home') {
                    $urlList[] = $baseUrl . '/';
                } else {
                    $urlList[] = $baseUrl . '/' . ltrim($identifier, '/') . $suffix;
                }
            }
            $this->indexNowSender->submitUrl($urlList);
        } catch (\Throwable $e) {
            $this->logger->error(sprintf(
                '[IndexNow] Error sending CMS page %s: %s',
                (string) $page->getId(),
                $e->getMessage(),
            ));
        }
    }
}
