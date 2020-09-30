<?php
/*
Plugin Name: Payment Integration Wompi
Description: Integration of Wompi for Woocommerce
Version: 2.0.1
Author: Saul Morales Pacheco
Author URI: https://saulmoralespa.com
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
WC tested up to: 4.0
WC requires at least: 3.6
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if(!defined('WOO_WOMPI_PAYMENT_WWP_VERSION')){
    define('WOO_WOMPI_PAYMENT_WWP_VERSION', '2.0.1');
}

add_action('plugins_loaded','woo_wompi_payment_wwp_init',0);

function woo_wompi_payment_wwp_init(){
    if (!requeriments_woo_wompi_payment_wwp())
        return;

    woo_wompi_payment_wwp()->run_wompi();
}

function woo_wompi_payment_wwp_notices( $notice ) {
    ?>
    <div class="error notice">
        <p><?php echo $notice; ?></p>
    </div>
    <?php
}

function requeriments_woo_wompi_payment_wwp(){

    if ( !in_array(
        'woocommerce/woocommerce.php',
        apply_filters( 'active_plugins', get_option( 'active_plugins' ) ),
        true
    ) ) {
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
            add_action(
                'admin_notices',
                function() {
                    woo_wompi_payment_wwp_notices('Woo Wompi Payment: Woocommerce debe estar instalado y activo');
                }
            );
        }
        return false;
    }

    $shop_currency = get_option('woocommerce_currency');

    if (!in_array($shop_currency, array('COP'))){
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
            $currency = __('Woo Wompi Payment: Requiere que se encuentre establecida la moneda COP ' )  . sprintf(__('%s' ), '<a href="' . admin_url() . 'admin.php?page=wc-settings&tab=general#s2id_woocommerce_currency">' . __('Haga clic aquí para configurar') . '</a>' );
            add_action(
                'admin_notices',
                function() use($currency) {
                    woo_wompi_payment_wwp_notices($currency);
                }
            );
        }
        return false;
    }

    return true;
}

function woo_wompi_payment_wwp(){
    static $plugin;
    if (!isset($plugin)){
        require_once ("includes/class-woo-wompi-payment-plugin.php");
        $plugin = new Woo_Wompi_Payment(__FILE__, WOO_WOMPI_PAYMENT_WWP_VERSION);
    }

    return $plugin;
}