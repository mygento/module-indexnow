<?php

namespace Mygento\IndexNow\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    public const XML_PATH_API_KEY = 'mygento_indexnow/general/api_key';
    public const XML_PATH_KEY_LOCATION = 'mygento_indexnow/general/key_location';
    public const XML_PATH_DEBUG_ENABLED = 'mygento_indexnow/general/debug_enabled';
    public const XML_PATH_ENABLED = 'mygento_indexnow/general/enabled';
    public const XML_PATH_ENDPOINT_URL = 'mygento_indexnow/%s/endpoint_url';

    public function isEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId,
        );
    }

    public function isDebugEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_DEBUG_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId,
        );
    }

    public function getApiKey(?int $storeId = null): ?string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_API_KEY,
            ScopeInterface::SCOPE_STORE,
            $storeId,
        );
    }

    public function getKeyLocation(?int $storeId = null): ?string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_KEY_LOCATION,
            ScopeInterface::SCOPE_STORE,
            $storeId,
        );
    }

    public function getEndpointUrl(string $code, ?int $storeId = null): string
    {
        return rtrim((string) $this->scopeConfig->getValue(
            sprintf(self::XML_PATH_ENDPOINT_URL, $code),
            ScopeInterface::SCOPE_STORE,
            $storeId,
        ), '/');
    }
}
