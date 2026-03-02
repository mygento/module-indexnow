<?php

/**
 * @author Mygento Team
 * @copyright 2026 Mygento (https://www.mygento.ru)
 * @package Mygento_IndexNow
 */

namespace Mygento\IndexNow\Model;

use Magento\Framework\App\Helper\AbstractHelper;

class Config extends AbstractHelper
{
    private const string XML_PATH_API_KEY = 'mygento_indexnow/%s/api_key';
    private const string XML_PATH_KEY_LOCATION = 'mygento_indexnow/%s/key_location';
    private const string XML_PATH_ENDPOINT_URL = 'mygento_indexnow/%s/endpoint_url';
    private const string XML_PATH_ENABLED = 'mygento_indexnow/general/enabled';
    private const string XML_PATH_DEBUG_ENABLED = 'mygento_indexnow/general/debug_enabled';
    private const string XML_PATH_ENABLED_FOR_ATTRIBUTES = 'mygento_indexnow/general/enabled_for_attributes';
    private const string XML_PATH_PRODUCT_ATTRIBUTES = 'mygento_indexnow/general/product_attributes';

    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED,
        );
    }

    public function isDebugEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_DEBUG_ENABLED,
        );
    }

    public function isEnabledForAttributes(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED_FOR_ATTRIBUTES,
        );
    }

    public function getApiKey(string $code): ?string
    {
        return $this->scopeConfig->getValue(
            sprintf(self::XML_PATH_API_KEY, $code),
        );
    }

    public function getKeyLocation(string $code): ?string
    {
        return $this->scopeConfig->getValue(
            sprintf(self::XML_PATH_KEY_LOCATION, $code),
        );
    }

    public function getEndpointUrl(string $code): string
    {
        return rtrim((string) $this->scopeConfig->getValue(
            sprintf(self::XML_PATH_ENDPOINT_URL, $code),
        ), '/');
    }

    public function getProductAttributes(): array
    {
        $value = (string) $this->scopeConfig->getValue(self::XML_PATH_PRODUCT_ATTRIBUTES);

        return array_filter(array_map('trim', explode(',', $value)));
    }
}
