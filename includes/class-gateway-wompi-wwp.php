<?php
class WC_Payment_Wompi_WWP extends WC_Payment_Gateway
{
    public $debug;

    public $isTest;

    public $public_key;

    public $events_key;


    public function __construct()
    {
        $this->id = 'wompi_wwp';
        $this->method_title = __('Wompi');
        $this->method_description = __('Con Wompi tienes diferentes alternativas de pagos');
        $this->description  = $this->get_option( 'description' );
        $this->order_button_text = __('Pagar');
        $this->supports = [
            'products'
        ];

        $this->title = $this->get_option('title');
        $this->debug = $this->get_option( 'debug' );
        $this->isTest = (bool)$this->get_option( 'environment' );

        if ($this->isTest){
            $this->public_key = $this->get_option( 'sandbox_public_key' );
            $this->events_key = $this->get_option( 'sandbox_events_key' );
        }else{
            $this->public_key = $this->get_option( 'public_key' );
            $this->events_key = $this->get_option( 'events_key' );
        }

        $this->init();

        add_action('woocommerce_api_'.strtolower(get_class($this)), array($this, 'confirmation_ipn'));
    }

    public function is_available()
    {
        return parent::is_available() &&
            !empty($this->public_key) &&
            !empty($this->events_key);
    }

    public function init()
    {
        // Load the settings API.
        $this->init_form_fields(); // This is part of the settings API. Override the method to add your own settings.
        $this->init_settings(); // This is part of the settings API. Loads settings you previously init.
        // Save settings in admin if you have any defined.
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
    }

    public function init_form_fields()
    {
        $this->form_fields = require( dirname( __FILE__ ) . '/admin/settings.php' );
    }

    public function admin_options()
    {
        ?>
        <h3><?php echo $this->title; ?></h3>
        <p><?php echo $this->method_description; ?></p>
        <table class="form-table">
            <?php $this->generate_settings_html(); ?>
        </table>
        <?php
    }

    public function process_payment($order_id)
    {
        $order = new WC_Order($order_id);
        $end_point = 'https://checkout.wompi.co/p/';

        $params = [
            'public-key' => $this->public_key,
            'currency' => $order->get_currency(),
            'amount-in-cents' => bcmul($order->get_total(), 100),
            'reference' => $order_id,
            'redirect-url' => $order->get_checkout_order_received_url()
        ];

        if ($this->debug === 'yes'){
            woo_wompi_payment_wwp()->log($params);
        }

        $url = $end_point . "?" . http_build_query($params);

        return [
            'result' => 'success',
            'redirect' => $url
        ];
    }

    public function confirmation_ipn()
    {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        woo_wompi_payment_wwp()->log('confirmation_ipn: ' . print_r($data, true));

        if (!isset($data['data']['transaction']['reference']) ||
            !isset($data['data']['transaction']['id']) ||
            !isset($data['data']['transaction']['status']) ||
            !isset($data['data']['transaction']['amount_in_cents']) ||
            !isset($data['signature']['checksum']) ||
            !isset($data['timestamp']))
            return;

        $signature = "{$data['data']['transaction']['id']}{$data['data']['transaction']['status']}{$data['data']['transaction']['amount_in_cents']}";
        $signature .= $data['timestamp'];
        $signature .= $this->events_key;

        $checksum = hash( 'sha256', $signature);

        if($data['signature']['checksum'] !== $checksum) return;

        $order_id = $data['data']['transaction']['reference'];
        $order = new WC_Order($order_id);
        $status = $data['data']['transaction']['status'];

        if ($status === 'APPROVED')
            $order->payment_complete();
        if ($status === 'VOIDED' || $status === 'DECLINED' || $status === 'ERROR')
            $order->update_status('failed');
    }
}