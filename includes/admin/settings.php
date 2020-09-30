<?php

wc_enqueue_js( "
    jQuery( function( $ ) {
	
	let wompi_live_fields = '#woocommerce_wompi_wwp_public_key';
	
	let wompi_sandbox_fields = '#woocommerce_wompi_wwp_sandbox_public_key';

	$( '#woocommerce_wompi_wwp_environment' ).change(function(){

		$( wompi_sandbox_fields + ',' + wompi_live_fields ).closest( 'tr' ).hide();

		if ( '0' === $( this ).val() ) {
		    $( '#woocommerce_wompi_wwp_credentials, #woocommerce_wompi_wwp_credentials + p' ).show();    
		    
			$( '#woocommerce_wompi_wwp_sandbox_credentials, #woocommerce_wompi_wwp_sandbox_credentials + p' ).hide();
			$( wompi_live_fields ).closest( 'tr' ).show();
			
		}else{
		  $( '#woocommerce_wompi_wwp_sandbox_credentials, #woocommerce_wompi_wwp_sandbox_credentials + p' ).show();
		  
		  $( '#woocommerce_wompi_wwp_credentials, #woocommerce_wompi_wwp_credentials + p' ).hide(); 
		  $( wompi_sandbox_fields  ).closest( 'tr' ).show();
		}
	}).change();
});	
");

$webhook_url = '<a target="_blank" href="https://comercios.wompi.co/my-account/">' . __( 'Configurar URL de eventos') . '</a>';
$docs_url = '<a target="_blank" href="https://wordpress.org/plugins/payment-integration-wompi/">' . __( 'Ver documentación completa del plugin') . '</a>';

return apply_filters('payment_integration_wompi_settings',
    array(
    'docs'  => array(
        'title' => __( 'Documentación' ),
        'type'  => 'title',
        'description' => $docs_url
    ),
    'enabled' => array(
        'title' => __('Enable/Disable'),
        'type' => 'checkbox',
        'label' => __('Habilitar Wompi'),
        'default' => 'no'
    ),
    'title' => array(
        'title' => __('Title'),
        'type' => 'text',
        'description' => __('Corresponde al título que el usuario ve durante el pago'),
        'default' => __('Wompi'),
        'desc_tip' => true,
    ),
    'description' => array(
        'title' => __('Description'),
        'type' => 'textarea',
        'description' => __('Corresponde a la descripción que el usuario verá durante el pago'),
        'default' => __('Con Wompi tienes diferentes alternativas de pagos'),
        'desc_tip' => true,
    ),
    'debug' => array(
        'title' => __('Debug'),
        'type' => 'checkbox',
        'label' => __('Registros de depuración, se guarda en el registro de pagos'),
        'default' => 'no'
    ),
    'environment' => array(
        'title' => __('Environment'),
        'type'        => 'select',
        'class'       => 'wc-enhanced-select',
        'description' => __('Habilitar para ejecutar pruebas'),
        'desc_tip' => true,
        'default' => true,
        'options'     => array(
            false    => __( 'Producción' ),
            true => __( 'Pruebas' ),
        )
    ),
    'ipn' => array(
        'title' => $webhook_url,
        'type' => 'title',
        'description' => trailingslashit(get_bloginfo( 'url' )) . trailingslashit('wc-api') . strtolower(get_class($this))
    ),
    'sandbox_credentials' => array(
        'title'       => __( 'Credenciales de pruebas' ),
        'type'        => 'title'
    ),
    'sandbox_public_key' => array(
        'title' => __( 'Llave pública' ),
        'type'  => 'text',
        'description' => __( 'La llave pública del comercio para fines de pruebas' ),
        'desc_tip' => true
    ),
    'credentials' => array(
        'title'       => __( 'Credenciales de producción' ),
        'type'        => 'title'
    ),
    'public_key' => array(
        'title' => __( 'Llave pública' ),
        'type'  => 'text',
        'description' => __( 'La llave pública del comercio para fines de producción' ),
        'desc_tip' => true
    ))
);