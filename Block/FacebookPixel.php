<?php
namespace WebhiveExtensions\FacebookPixel\Block;
use WebhiveExtensions\FacebookPixel\Helper\Data;
use Magento\Framework\UrlInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Cart;
use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\Session;
class FacebookPixel extends \Magento\Framework\View\Element\Template
{
    protected $cart;
    protected $product;
    protected $helper;
    protected $_urlInterface;
    protected $_customerSession;
    protected $_checkoutSession;
    protected $_objectManager;


	public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        Data $helper,
        Product $product,
        Cart $cart,
        CustomerSession $customerSession,
        UrlInterface $urlInterface,
        Session $checkoutSession
        )
	{
        $this->cart=$cart;
        $this->_objectManager = $objectmanager;
        $this->_checkoutSession = $checkoutSession;
        $this->product=$product;
        $this->_customerSession = $customerSession;
        $this->_urlInterface=$urlInterface;
        $this->helper=$helper;
		parent::__construct($context);
	}

    /**
     * return boolean
     */
	public function isEnabled()
	{
        return $this->helper->getGeneralConfig('facebook_pixel_status');
    }
    /**
     * return facebook pixel id
     */
    public function getFacebookPixelId(){
        return $this->helper->getGeneralConfig('facebook_pixel_id');
    }
    /**
     * return facebook domain verification key
     */
    public function getFacebookDomainVerificationKey(){
        return $this->helper->getGeneralConfig('facebook_domain_verification');
    }
    /**
     * return current url
     */
    public function getCurrentUrl(){
        return $this->_urlInterface->getCurrentUrl();
    }
    /**
     * return access token for facebook pixel
     */
    public function getAccessToken(){
        return $this->helper->getGeneralConfig('access_token');
    }
    /**
     * return currency
     */
    public function getCurrency(){
        return $this->helper->getGeneralConfig('currency');
    }
    /**
     * return test event code
     */
    public function getTestEventCode(){
        return $this->helper->getGeneralConfig('test_event_code');
    }
    /**
     * return customer email
     */
    public function getEmail(){
        $email=$this->_customerSession->getCustomer()->getEmail();
        if($email){
            return hash('sha256',$email);
        }else{
            return hash('sha256','developercode6068@gmail.com');
        }
    }
    /**
     * return logged in customer
     */
    public function getCustomer(){
        return $this->_customerSession->getCustomer();
    }
    /**
     * return client IP address
     */
    public function getClientIp() {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if(isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }
    /**
     * return current cart
     */
    public function getQuote(){
        return $this->cart->getQuote();
    }
    /**
     * return product by id
     */
    public function getProductById($id){
        return $this->product->load($id);
    }
    /**
     * return last order
     */
    public function getLastOrder(){
        $order = $this->_checkoutSession->getLastRealOrder();
        return $order;
    }
     /**
     * return msrp field name by default its 'msrp'
     */
    public function getMsrp(){
        return $this->helper->getDeveloperConfig('msrp_custom');
    }
     /**
     * Page view Events status
     * return bool
     */
    public function isPageViewEnable(){
        return $this->helper->getEventConfig('pageview_event');
    }
     /**
     * CompleteRegisteration Events status
     * return bool
     */
    public function isRegisterationEnable(){
        return $this->helper->getEventConfig('complete_registeration_event');
    }
      /**
     * Addtocart Events status
     * return bool
     */
    public function isAddtocartEnable(){
        return $this->helper->getEventConfig('addtocart_event');
    }
     /**
     * InitialCheckout Events status
     * return bool
     */
    public function isInitialCheckoutEnable(){
        return $this->helper->getEventConfig('initial_checkout_event');
    }
     /**
     * Purchase Events status
     * return bool
     */
    public function isPurchaseEnable(){
        return $this->helper->getEventConfig('purchase_event');
    }
    /**
     * return instance of objectManager
     */
    public function getObjectManager(){
        return $this->_objectManager;
    }


}
