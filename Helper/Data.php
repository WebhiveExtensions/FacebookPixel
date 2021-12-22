<?php

namespace WebhiveExtensions\FacebookPixel\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;

class Data extends AbstractHelper
{

	const XML_PATH_PIXEL = 'facebook_pixel_section/fields_config/';
	const XML_PATH_PIXEL2 = 'facebook_pixel_section/fields_developer_config/';
	const XML_PATH_PIXEL3 = 'facebook_pixel_section/events_config/';
    protected $scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Context $context
    )
    {
        $this->scopeConfig=$scopeConfig;
        parent::__construct($context);
    }
	public function getConfigValue($field, $storeId = null)
	{
		return $this->scopeConfig->getValue(
			$field, ScopeInterface::SCOPE_STORE, $storeId
		);
	}

	public function getGeneralConfig($code, $storeId = null)
	{
		return $this->getConfigValue(self::XML_PATH_PIXEL . $code, $storeId);
	}

	public function getDeveloperConfig($code, $storeId = null)
	{
		return $this->getConfigValue(self::XML_PATH_PIXEL2 . $code, $storeId);
	}

	public function getEventConfig($code, $storeId = null)
	{
		return $this->getConfigValue(self::XML_PATH_PIXEL3 . $code, $storeId);
	}

}
