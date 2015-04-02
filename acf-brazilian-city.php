<?php
/*
Plugin Name: Advanced Custom Fields: Brazilian City
Description: Adiciona ao ACF a opção de campo de cidade considerando a seleção de Estado/Cidade.
Plugin URI: https://github.com/luizhguimaraes/acf-brazilian-city
Version: 1.0.0
Author: Luiz Henrique Guimarães
License: GPL
*/

load_plugin_textdomain( 'acf-brazilian-city-field', false, dirname( plugin_basename(__FILE__) ) . '/lang/' );

// ACF Version 4.*
function register_fields_brazilian_city() {
    include_once('acf-brazilian-city-field.php');
}
add_action('acf/register_fields', 'register_fields_brazilian_city');  

// Activate and deactivate hooks
//register_activation_hook( __FILE__, 'populate_db' );
//register_deactivation_hook( __FILE__, 'depopulate_db' );
/*
function populate_db() {
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    ob_start();
    require_once "lib/install-data.php";
    $sql = ob_get_clean();
    dbDelta( $sql );
}

function depopulate_db() {
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    ob_start();
    require_once "lib/drop-tables.php";
    $sql = ob_get_clean();
    dbDelta( $sql );
}
*/

