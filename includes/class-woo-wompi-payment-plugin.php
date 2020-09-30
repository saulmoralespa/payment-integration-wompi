<?php


class Woo_Wompi_Payment
{
    /**
     * Filepath of main plugin file.
     *
     * @var string
     */
    public $file;
    /**
     * Plugin version.
     *
     * @var string
     */
    public $version;
    /**
     * Absolute plugin path.
     *
     * @var string
     */
    public $plugin_path;
    /**
     * Absolute plugin URL.
     *
     * @var string
     */
    public $plugin_url;
    /**
     * assets plugin.
     *
     * @var string
     */
    public $assets;
    /**
     * Absolute path to plugin includes dir.
     *
     * @var string
     */
    public $includes_path;
    /**
     * Absolute path to plugin lib dir
     *
     * @var string
     */
    public $lib_path;
    /**
     * @var bool
     */
    private $_bootstrapped = false;

    public function __construct($file, $version)
    {
        $this->file = $file;
        $this->version = $version;

        $this->plugin_path   = trailingslashit( plugin_dir_path( $this->file ) );
        $this->plugin_url    = trailingslashit( plugin_dir_url( $this->file ) );
        $this->includes_path = $this->plugin_path . trailingslashit( 'includes' );
        $this->assets = $this->plugin_url . trailingslashit( 'assets' );
        $this->logger = new WC_Logger();
    }

    public function run_wompi()
    {
        try{
            if ($this->_bootstrapped){
                throw new Exception( 'Addi Woocommerce Credit can only be called once');
            }
            $this->_run();
            $this->_bootstrapped = true;
        }catch (Exception $e){
            if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
                add_action('admin_notices', function() use($e) {
                    woo_wompi_payment_wwp_notices($e->getMessage());
                });
            }
        }
    }

    protected function _run()
    {
        require_once ($this->includes_path . 'class-gateway-wompi-wwp.php');
        add_filter( 'plugin_action_links_' . plugin_basename( $this->file), array( $this, 'plugin_action_links' ) );
        add_filter( 'woocommerce_payment_gateways', array($this, 'woocommerce_wompi_add_gateway'));
    }

    public function plugin_action_links($links)
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

    public function log($message)
    {
        if (is_array($message) || is_object($message))
            $message = print_r($message, true);
        $logger = new WC_Logger();
        $logger->add('wompi-wwp', $message);
    }

    public function enqueue_scripts()
    {
        if(is_checkout()){
            wp_enqueue_script( 'woo-wompi-payment', 'https://checkout.wompi.co/widget.js', array( 'jquery' ), false, true );
        }
    }
}