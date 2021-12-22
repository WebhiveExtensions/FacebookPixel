<?php
// YM
namespace WebhiveExtensions\FacebookPixel\Observer;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use WebhiveExtensions\FacebookPixel\Block\FacebookPixel;
class Addtocart implements ObserverInterface
{
    protected $fbPixel;

    public function __construct(
        FacebookPixel $fbPixel
        ){
            $this->fbPixel=$fbPixel;
        }
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {

        if($this->fbPixel->isEnabled() && $this->fbPixel->isAddtocartEnable()){
            $this->facebookConversionPixel($observer);
        }
    }
    /**
     *Facebook Pixel Code for Server Event to track Addtocart
     */
    function facebookConversionPixel($observer){
        unset($_SESSION['AddToCartFormSubmit']);
        $facebook_pixel_id=$this->fbPixel->getFacebookPixelId();
        $access_token=$this->fbPixel->getAccessToken();
        $currentUrl=$this->fbPixel->getCurrentUrl();
        $objectManager = $this->fbPixel->getObjectManager();
        $product= $observer->getEvent()->getProduct();
        $loginstatus = $this->fbPixel->getCustomer()->getEmail() ? true : false;
        $params = $observer->getEvent()->getinfo();
        $totalPrice=0;
        $pnames='';
        $prodIds = [];
        if(isset($params['super_group']) && is_array($params['super_group'])){
            foreach($params['super_group'] as $key => $pqty){
                $productCollection = $objectManager->create('Magento\Catalog\Model\Product')->load($key);
                $singleMsrp= $productCollection->getData($this->fbPixel->getMsrp()) ? $productCollection->getData($this->fbPixel->getMsrp()) : $productCollection->getFinalPrice();
                $productPrice = $loginstatus ? $productCollection->getPrice() :  $singleMsrp;
                $singlePrice = $productPrice * $pqty;
                if($pnames == '' && $singlePrice != 0){
                    $pnames = $productCollection->getName();
                }
                elseif($singlePrice != 0){
                    $pnames = $pnames .",". $productCollection->getName();
                }
                $totalPrice = $totalPrice + ($productPrice * $pqty);
            }
            $prodIds = array_keys(array_filter($params['super_group']));
        }
        else{
            $productCollection = $objectManager->create('Magento\Catalog\Model\Product')->load($params['product']);
            $pnames = $productCollection->getName();
            $singleMsrp= $productCollection->getData($this->fbPixel->getMsrp()) ? $productCollection->getData($this->fbPixel->getMsrp()) : $productCollection->getFinalPrice();
            $productPrice = $loginstatus ? $productCollection->getFinalPrice() : $singleMsrp;
            $totalPrice = $totalPrice + ($productPrice * $params['qty']);
            $prodIds[] = $params['product'];
        }
        $addToCart=array (
            'data' =>
                array (
                    0 =>
                        array (
                            'event_name' => 'AddToCart',
                            'event_time' => time(),
                            'event_id' => 'eventAddtocart'.time(),
                            'event_source_url' => $currentUrl,
                            'user_data' =>
                                array (
                                    "em" => [$this->fbPixel->getEmail()],
                                    'client_ip_address' => $this->fbPixel->getClientIp(),
                                    'client_user_agent' => 'afg',
                                ),
                            'custom_data' =>
                                array (
                                    'content_name' => "'".str_replace(["'",'"'],"",$pnames)."'",
                                    'content_category' => "'".implode(",",$product->getCategoryIds())."'",
                                    'content_ids' => $prodIds,
                                    'product_catalog_id' => "'".implode(",",($prodIds))."'",
                                    'content_type' => 'product',
                                    'value' => $totalPrice,
                                    'currency' => $this->fbPixel->getCurrency()
                                )
                        )
                ),
                'test_event_code' => $this->fbPixel->getTestEventCode()
            );




        $url="https://graph.facebook.com/v12.0/".$facebook_pixel_id."/events?access_token=".$access_token;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($addToCart));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
        curl_exec($ch);
        curl_close($ch);

        // session is for browser event it trigger after this on "Design/Magento_Categlog/templates/product/view/addtocart.phtml
        $_SESSION['AddToCartFormSubmit'] = ['name' => "'".str_replace(["'",'"'],"",$pnames)."'",'category'=>"'".implode(",",$product->getCategoryIds())."'", 'idsArr' =>json_encode($prodIds), 'productCatalogIds'=> "'".implode(",",($prodIds))."'",'totalPrice'=>$totalPrice];
    }
}
