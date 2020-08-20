<?php

namespace Payments;

use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;

use PayPal\Api\InputFields;
use PayPal\Api\WebProfile;

use PayPal\Api\Payer;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Details;
use PayPal\Api\Amount;
use PayPal\Api\Transaction;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Payment;

use PayPal\Api\PaymentExecution;


/**
 * Paypal Payment Gateway
 */
class Paypal extends AbstractGateway
{
    /**
     * PayPal Api context
     * @var ApiContext
     */
    private $api_context;

    /**
     * PayPal web view id
     * @var string|null|bool
     */
    private $web_view_id;


    /**
     * Init.
     */
    public function __construct()
    {
        $this->apiContext();
        $this->web_view_id = null;
    }


    /**
     * Get Create API Context
     * @return ApiContext
     */
    private function apiContext()
    {
        if (!$this->api_context) {
            $integrations = \Controller::model("GeneralData", "integrations");

            $client_id = $integrations->get("data.paypal.client_id");
            $client_secret = $integrations->get("data.paypal.client_secret");
            $mode = $integrations->get("data.paypal.environment") == "live"
                ? "live" : "sandbox";

            $oauth_credentials = new OAuthTokenCredential($client_id, $client_secret);

            $this->api_context = new ApiContext($oauth_credentials);
            $this->api_context->setConfig(["mode" => $mode]);
        }

        return $this->api_context;
    }

    /**
     * Place Order
     *
     * Generate payment page url here and return it
     * @return string URL of the payment page
     */
    public function placeOrder($params = [])
    {
        $Order = $this->getOrder();
        if (!$Order) {
            throw new \Exception('Set order before calling AbstractGateway::placeOrder()');
        }

        if ($Order->get("status") != "payment_processing") {
            throw new \Exception('Order status must be payment_processing to place it');
        }

        // Check if the order currency is zero decimal currency
        $iszdc = isZeroDecimalCurrency($Order->get("currency"));


        $Settings = \Controller::model("GeneralData", "settings");

        $sitecurrency = $Settings->get("data.currency");
        $transaction_currency = $sitecurrency;

        $supportedpaypalcurrencies = ["AUD", "BRL", "GBP", "CAD", "CZK", "DKK", "EUR",  "HKD", "HUF",  "ILS",  "JPY",  "MXN",  "TWD",  "NZD",  "NOK",  "PHP",  "PLN",   "RUB",   "SGD", "SEK", "CHF", "THB", "USD"];

        if (in_array($sitecurrency, $supportedpaypalcurrencies)){
            $transaction_currency = $sitecurrency;

        }else{

            $transaction_currency =  "USD";
        }




        $Payer = new Payer();
        $Payer->setPaymentMethod("paypal");

        $Item = new Item();
        $Item->setName(htmlchars($Order->get("data.package.title")))
            ->setDescription($Order->get("data.plan") == "annual" ? __("Annual Plan") : __("Monthly Plan"))
            ->setCurrency($transaction_currency)
            ->setQuantity(1)
            ->setPrice($iszdc ? round($Order->get("total")) : $Order->get("total"));

        //USe to diplay jso



        if(!$Order->get("data.grandtotaldiscounted")){


        }else{


            $discountNeg =  0-number_format($Order->get("data.grandtotaldiscounted"),2);

            $Itemdiscount = new Item();
            $Itemdiscount->setName(htmlchars($Order->get("data.package.title")) . "Coupon")
                ->setDescription($Order->get("data.plan") == "annual" ? __("Annual Plan") : __("Monthly Plan"))
                ->setCurrency($transaction_currency)
                ->setQuantity(1)
                ->setPrice($iszdc ? round($discountNeg) : $discountNeg);

        }






        $ItemList = new ItemList();
        if(!$Order->get("data.grandtotaldiscounted")){
            $ItemList->setItems(array($Item));
        }else{


            $ItemList->setItems(array($Item, $Itemdiscount));
        }

        $Details = new Details();
        if(!$Order->get("data.grandtotaldiscounted")){

            $Details->setSubtotal($iszdc ? round($Order->get("total")) : $Order->get("total"));

        }else{

            $newSubtotal =  $Order->get("total")  - (-1*$discountNeg);
            $Details
                ->setSubtotal($iszdc ? round($newSubtotal) : $newSubtotal);

        }

        $Details->setTax( $Order->get("data.tax"));
        $Amount = new Amount();



        if(!$Order->get("data.grandtotaldiscounted")){


            $Amount->setCurrency($transaction_currency)
                ->setTotal($iszdc ? round($Order->get("total")) : $Order->get("total") +  $Order->get("data.tax"))
                ->setDetails($Details);

        }else{

            $newTotal =  $Order->get("total") - (-1*$discountNeg);

            $Amount->setCurrency($transaction_currency)
                ->setTotal($iszdc ? round($newTotal) : $newTotal +  $Order->get("data.tax"))
                ->setDetails($Details);
        }

        // $ItemsTotal = $Item->getPrice()  + $Itemdiscount->getPrice();
        // throw new \Exception( "Items Sume". $ItemsTotal." Amount Total: ". $Amount->getTotal()."Subtotal: ". $Details->getSubtotal()   ." NegDiscount: " . $discountNeg .' Discount : '. number_format($Order->get("data.grandtotaldiscounted"),2) . " Total: " . number_format($Order->get("total"), 2) );


        $Transaction = new Transaction();
        $Transaction->setAmount($Amount)
            ->setItemList($ItemList)
            ->setDescription(__("Payment for Order #%s", $Order->get("id")))
            ->setInvoiceNumber(uniqid())
            ->setCustom(json_encode([
                "order_id" => $Order->get("id")
            ]));


        $RedirectUrls = new RedirectUrls();
        $RedirectUrls->setReturnUrl(APPURL . "/checkout/" . $Order->get("id") . "." . sha1($Order->get("id") . NP_SALT))
            ->setCancelUrl(APPURL . "/checkout/" . $Order->get("id") . "." . sha1($Order->get("id") . NP_SALT));

        $Payment = new Payment();
        $Payment->setIntent("sale")
            ->setPayer($Payer)
            ->setRedirectUrls($RedirectUrls)
            ->setTransactions(array($Transaction));

        if ($this->webViewId()) {
            $Payment->setExperienceProfileId($this->webViewId());
        }

        try {
            $Payment->create($this->apiContext());
            $url = $Payment->getApprovalLink();
        } catch (\Exception $e) {
            $Order->delete();
            $url = APPURL . "/checkout/error";
        }

        return $url;
    }

    /**
     * Create PayPal webview and return it's id
     * @return [type] [description]
     */
    private function webViewId()
    {
        if (is_null($this->web_view_id)) {
            $InputFields = new InputFields();
            $InputFields
                ->setNoShipping(1); # hide shipping address on checkout page

            $WebProfile = new WebProfile();
            $WebProfile
                ->setName("Temporary webview " . uniqid())
                ->setTemporary(true)
                ->setInputFields($InputFields);

            try {
                $res = $WebProfile->create($this->apiContext());
                $this->web_view_id = $res->getId();
            } catch (Exception $e) {
                // Couldn't create web view
                $this->web_view_id = false;
            }
        }

        return $this->web_view_id;
    }

    /**
     * Payment callback
     * @return boolean [description]
     */
    public function callback($params = [])
    {
        if (empty($params["paymentId"])) {
            throw new \Exception(__('System detected logical error') . ": invalid_payment_id");
        }

        $paymentId = $params["paymentId"];

        $Order = $this->getOrder();
        if (!$Order) {
            throw new \Exception('Set order before calling AbstractGateway::placeOrder()');
        }

        try {
            $Payment = Payment::get($paymentId, $this->apiContext());
            $payment_custom_data = json_decode($Payment->transactions[0]->custom);
        } catch (\Exception $e) {
            throw new \Exception(__("Couldn't get payment information"));
        }


        if ($payment_custom_data->order_id != $Order->get("id")) {
            throw new \Exception(__('System detected logical error') . ": invalid_order_id");
        }


        if (!$Order->get("payment_id")) {
            // If payment_id is not empty
            // then it means that, order is already completed

            $Execution = new PaymentExecution();
            $Execution->setPayerId($Payment->payer->payer_info->payer_id);

            try {
                $PaymentResult = $Payment->execute($Execution, $this->apiContext());
            } catch (\Exception $e) {
                // Couldn't pay, removing order...
                $Order->remove();
                throw new \Exception(__("Couldn't complete the payment!"));
            }


            if ($PaymentResult->state != "approved") {
                // Couldn't pay, removing order...
                $Order->remove();
                throw new \Exception(__("Couldn't approve the payment!"));
            }


            if ($Order->get("status") == "payment_processing") {
                $Order->finishProcessing();

                // Updating order...
                $Order->set("status", "paid")
                    ->set("payment_id", $Payment->id)
                    ->set("paid", $Payment->transactions[0]->amount->total)
                    ->update();

                try {
                    // Send notification emails to admins
                    \Email::sendNotification("new-payment", ["order" => $Order]);
                } catch (\Exception $e) {
                    // Failed to send notification email to admins
                    // Do nothing here, it's not critical error
                }
            }
        }

        return true;
    }
}
