<?php
// YM
namespace WebhiveExtensions\FacebookPixel\Observer;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use WebhiveExtensions\FacebookPixel\Block\FacebookPixel;
class AfterCustomerRegister implements ObserverInterface
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
        if($this->fbPixel->isEnabled() && $this->fbPixel->isRegisterationEnable()){
        $customer = $observer->getEvent()->getCustomer();
        $email=$this->fbPixel->getEmail();
        $name=$customer->getFirstname().' '.$customer->getLastname();
        $this->facebookConversionPixel($email,$name);
        }
    }
    /**
     *Facebook Pixel Code for Server Event to track Customer Subscribtion
     */
    function facebookConversionPixel($email,$name){
        $ip=$this->fbPixel->getClientIp();
        $facebook_pixel_id=$this->fbPixel->getFacebookPixelId();
        $access_token=$this->fbPixel->getAccessToken();
        $currentUrl=$this->fbPixel->getCurrentUrl();
        $test_event_code=$this->fbPixel->getTestEventCode();
        $currency=$this->fbPixel->getCurrency();

        $pixelContent = array (
            'data' =>
                array (
                    0 =>
                        array (
                            'event_name' => 'CompleteRegistration',
                            'event_time' => time(),
                            'event_id' => 'customer_registration_event'.time(),
                            'event_source_url' => $currentUrl,
                            'action_source' => 'website',
                            "user_data" =>  [
                                "em" => [$email],
                                "client_ip_address" => $ip,
                                "client_user_agent" => null,
                            ],
                            'custom_data' =>
                                array (
                                    'content_name' => $name,
                                    'currency' => $currency,
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
