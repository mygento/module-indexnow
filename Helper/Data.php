<?php

/**
 * @author Mygento Team
 * @copyright 2026 Mygento (https://www.mygento.ru)
 * @package Mygento_IndexNow
 */

namespace Mygento\IndexNow\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    public const string XML_PATH_API_KEY = 'mygento_indexnow/general/api_key';
    public const string XML_PATH_KEY_LOCATION = 'mygento_indexnow/general/key_location';
    public const string XML_PATH_DEBUG_ENABLED = 'mygento_indexnow/general/debug_enabled';
    public const string XML_PATH_ENABLED = 'mygento_indexnow/general/enabled';
    public const string XML_PATH_ENDPOINT_URL = 'mygento_indexnow/%s/endpoint_url';

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

    public function getApiKey(): ?string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_API_KEY,
        );
    }

    public function getKeyLocation(): ?string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_KEY_LOCATION,
        );
    }

    public function getEndpointUrl(string $code): string
    {
        return rtrim((string) $this->scopeConfig->getValue(
            sprintf(self::XML_PATH_ENDPOINT_URL, $code),
        ), '/');
    }
}
