<?php

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

class Wompi_Payment_Blocks_Support extends AbstractPaymentMethodType
{

    protected $name = 'wompi_wwp';

    public function initialize(): void
    {
        $this->settings = get_option( "woocommerce_{$this->name}_settings", array() );
    }

    public function is_active(): bool
    {
        return !empty( $this->settings[ 'enabled' ] ) && 'yes' === $this->settings[ 'enabled' ];
    }

    public function get_payment_method_script_handles(): array
    {

        wp_register_script(
            'wc-wompi-blocks-integration',
            plugin_dir_url( __DIR__ ) . 'assets/js/checkout.js',
            array(
                'wc-blocks-registry',
                'wc-settings',
                'wp-element',
                'wp-html-entities',
                'wp-i18n',
            ),
            null, // or time() or filemtime( ... ) to skip caching
            true
        );

        return array( 'wc-wompi-blocks-integration' );

    }

    public function get_payment_method_data(): array
    {
        return array(
            'title'        => $this->get_setting( 'title' ),
            // almost the same way:
            // 'title'     => isset( $this->settings[ 'title' ] ) ? $this->settings[ 'title' ] : 'Default value';
            'description'  => $this->get_setting( 'description' ),
            'icon'         => plugin_dir_url( __DIR__ ) . 'assets/img/logo.png'
            // if $this->gateway was initialized on line 15
            // 'supports'  => array_filter( $this->gateway->supports, [ $this->gateway, 'supports' ] ),

            // example of getting a public key
            // 'publicKey' => $this->get_publishable_key(),
        );
    }

}