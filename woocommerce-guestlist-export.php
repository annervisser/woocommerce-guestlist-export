<?php
/**
 * Plugin Name: Guest list export for WooCommerce
 * Description: Export a csv file of all orders of a certain product
 * Version: 1.2
 * Author: Anner Visser
 */


if (!class_exists('GuestListCsvDownload')) {
    include_once(dirname(__FILE__) . '/admin/GuestListCsvDownload.php');
}

add_action('admin_menu', 'guestlist_add_menu');
function guestlist_add_menu()
{
    add_submenu_page('woocommerce',
        'WooCommerce Gastenlijst',
        'Gastenlijst',
        'export',
        GuestListCsvDownload::PAGE_NAME,
        'guestlist_admin_page');

}

function guestlist_admin_page()
{
    include_once(dirname(__FILE__) . '/admin/guestlist-export-page.php');
}


