<?php
namespace WebhiveExtensions\FacebookPixel\Plugin;
use WebhiveExtensions\FacebookPixel\Block\FacebookPixel;

class InitiateCheckout
{

    protected $fbPixel;

    public function __construct(
        FacebookPixel $fbPixel
        ){
            $this->fbPixel=$fbPixel;
        }

	public function beforeExecute(\Magento\Checkout\Controller\Index\Index $subject)
	{
        if($this->fbPixel->isEnabled() && $this->fbPixel->isInitialCheckoutEnable()){
        $this->fbInitialCheckout();  // Initial Facebook Pixel Events
        }
	}

    // facebook pixel server events
    public function fbInitialCheckout(){
        $quote=$this->fbPixel->getQuote();
        $email=$this->fbPixel->getEmail();
        $ip=$this->fbPixel->getClientIp();
        $facebook_pixel_id=$this->fbPixel->getFacebookPixelId();
        $access_token=$this->fbPixel->getAccessToken();
        $currentUrl=$this->fbPixel->getCurrentUrl();
        $test_event_code=$this->fbPixel->getTestEventCode();
        $currency=$this->fbPixel->getCurrency();

        // retrieve quote items array
        $items = $quote->getAllItems();
        $ids=array();
        $content=array();
        $categoryIds = array();
        foreach($items as $key=>$item) {
            $product = $this->fbPixel->getProductById($item->getProductId());
            $ids[$key]= $item->getProductId();
            $content[$key]=['id'=>$item->getProductId(),'quantity'=>$item->getQty(),'item_price'=>$item->getPrice()];
            $categoryIds = array_merge($categoryIds,$product->getCategoryIds());
        }
        $categoryIds = implode(',',$categoryIds);
        $item_ids=json_encode($ids);
        $item_content=json_encode($content);
        $qty=$quote->getItemsCount();
        $total=$quote->getGrandTotal();

        $addToCart=array (
            'data' =>
                array (
                    0 =>
                        array (
                            'event_name' => 'InitiateCheckout',
                            'event_time' => time(),
                            'event_id' => 'event'.time(),
                            'event_source_url' => $currentUrl,
                            'user_data' =>
                                array (
                                    "em" => [$email],
                                    'client_ip_address' => $ip,
                                    'client_user_agent' => null,
                                ),
                            'custom_data' =>
                                array (
                                    'content_ids' => $ids,
                                    'content' => $content,
                                    'num_items' =>  $qty,
                                    'value' => $total,
                                    'content_type' => 'product',
                                    'product_catalog_id' =>  "'".$categoryIds."'",
                                    'currency' => $currency
                                )
                        )
                ),
            'test_event_code' => $test_event_code
        );
        $url="https://graph.facebook.com/v12.0/".$facebook_pixel_id."/events?access_token=".$access_token;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($addToCart));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
        $result=curl_exec($ch);
        curl_close($ch);
        return $result;
    }
      // facebook pixel server events
}

