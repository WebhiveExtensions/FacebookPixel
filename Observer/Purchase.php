<?php
// YM
namespace WebhiveExtensions\FacebookPixel\Observer;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use WebhiveExtensions\FacebookPixel\Block\FacebookPixel;
class Purchase implements ObserverInterface
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
        if($this->fbPixel->isEnabled() && $this->fbPixel->isPurchaseEnable()){
            $order=$observer->getEvent()->getOrder();
            $this->facebookConversionPixel($order);
        }
    }
    /**
     *Facebook Pixel Code for Server Event to track Customer Subscribtion
     */


    function facebookConversionPixel($order){
        $ip=$this->fbPixel->getClientIp();
        $facebook_pixel_id=$this->fbPixel->getFacebookPixelId();
        $access_token=$this->fbPixel->getAccessToken();
        $currentUrl=$this->fbPixel->getCurrentUrl();
        $test_event_code=$this->fbPixel->getTestEventCode();
        $currency=$this->fbPixel->getCurrency();
        $customerEmailHash = hash('sha256',$order->getCustomerEmail());


        $productPrice = 0;
        $itemCollection = $order->getItemsCollection();
        $product_ids=[];
        $productTotalPrice= $order->getGrandTotal();
        foreach ($itemCollection as $_items) {
            $product_ids[]= $_items->getSku();
         }

        $pixelContent = array (
            'data' =>
                array (
                    0 =>
                        array (
                            'event_name' => 'Purchase',
                            'event_time' => time(),
                            'event_id' => 'event_purchase_'.time(),
                            'event_source_url' => $currentUrl,
                            'action_source' => 'website',
                            "user_data" =>  [
                                "em" => [$customerEmailHash],
                                "client_ip_address" => $ip,
                                "client_user_agent" => 'Null'
                            ],
                            'custom_data' =>
                                array (
                                    'value' => $productTotalPrice,
                                    'currency' => $currency,
                                    'content_ids' => $product_ids,
                                    'content_type' => 'product_group'
                                ),
                            'opt_out' => false,
                        ),

                ),
                'test_event_code' => $test_event_code,
            );
            $url="https://graph.facebook.com/v12.0/".$facebook_pixel_id."/events?access_token=".$access_token;
            $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($pixelContent));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
        curl_exec($ch);
        curl_close($ch);
    }
}
