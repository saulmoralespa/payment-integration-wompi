<?php
class Woo_Wompi_Payment
{
    /**
     * Absolute plugin path.
     *
     * @var string
     */
    public string $plugin_path;
    /**
     * Absolute plugin URL.
     *
     * @var string
     */
    public string $plugin_url;
    /**
     * assets plugin.
     *
     * @var string
     */
    public string $assets;
    /**
     * Absolute path to plugin includes dir.
     *
     * @var string
     */
    public string $includes_path;
    /**
     * @var bool
     */
    private bool $_bootstrapped = false;

    public function __construct(
        public string $file,
        protected string $version)
    {

        $this->plugin_path   = trailingslashit( plugin_dir_path( $this->file ) );
        $this->plugin_url    = trailingslashit( plugin_dir_url( $this->file ) );
        $this->includes_path = $this->plugin_path . trailingslashit( 'includes' );
        $this->assets = $this->plugin_url . trailingslashit( 'assets' );
    }

    public function run_wompi(): void
    {
        try{
            if ($this->_bootstrapped){
                throw new Exception( 'Payment Integration Wompi can only be called once');
            }
            $this->run();
            $this->_bootstrapped = true;
        }catch (Exception $e){
            if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
                add_action('admin_notices', function() use($e) {
                    woo_wompi_payment_wwp_notices($e->getMessage());
                });
            }
        }
    }

    protected function run(): void
    {
        require_once ($this->includes_path . 'class-gateway-wompi-wwp.php');
        require_once ($this->includes_path . 'class-wompi-blocks-support.php');
        add_filter( 'plugin_action_links_' . plugin_basename( $this->file), array( $this, 'plugin_action_links' ) );
        add_filter( 'woocommerce_payment_gateways', array($this, 'woocommerce_wompi_add_gateway'));
        add_action( 'woocommerce_blocks_loaded', array($this, 'register_wc_blocks') );

    }

    public function plugin_action_links($links): array
    {
        $plugin_links = array();
        $plugin_links[] = '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=wompi_wwp') . '">' . 'Configuraciones' . '</a>';
        return array_merge( $plugin_links, $links );
    }

    public function woocommerce_wompi_add_gateway($methods)
    {
        $methods[] = 'WC_Payment_Wompi_WWP';
        return $methods;
    }

    public function register_wc_blocks(): void
    {
        add_action(
            'woocommerce_blocks_payment_method_type_registration',
            function( Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry ) {
                $payment_method_registry->register( new Wompi_Payment_Blocks_Support );
            }
        );
    }

    public function log($message)
    {
        if (is_array($message) || is_object($message))
            $message = print_r($message, true);
        $logger = new WC_Logger();
        $logger->add('wompi-wwp', $message);
    }
}