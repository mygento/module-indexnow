<?php

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
    private $page;

    public function __construct(
        private StoreManagerInterface $storeManager,
        private ScopeConfigInterface $scopeConfig,
        private IndexNowSender $indexNowSender,
        private LoggerInterface $logger,
    ) {}

    public function execute(Observer $observer): void
    {
        $page= $observer->getEvent()->getObject();
        if (!$this->page instanceof Page || !$this->page->getPageId()) {
            return;
        }

        $identifier = $this->page->getIdentifier();
        if (!$identifier) {
            return;
        }

        $storeIds = $this->page->getStoreId();
        if ($storeIds === null || in_array(0, (array) $storeIds, true)) {
            $stores = $this->storeManager->getStores();
            $storeIds = array_map(fn($s) => (int) $s->getId(), $stores);
        }

        foreach ($storeIds as $storeId) {
            try {
                $baseUrl = rtrim($this->storeManager->getStore($storeId)->getBaseUrl(), '/');

                $suffix = (string) $this->scopeConfig->getValue(
                    'cms/page/url_suffix',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $storeId,
                );

                if ($identifier === 'home') {
                    $url = $baseUrl . '/';
                } else {
                    $url = $baseUrl . '/' . ltrim($identifier, '/') . $suffix;
                }

                $this->indexNowSender->submitUrl($url);
            } catch (\Throwable $e) {
                $this->logger->error(sprintf(
                    '[IndexNow] Error sending CMS page %s to store %d: %s',
                    (string) $this->page->getId(),
                    (int) $storeId,
                    $e->getMessage(),
                ));
            }
        }
    }
}
