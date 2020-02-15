<?php

class GuestListCsvDownload
{
    const PAGE_NAME = 'guestlist-export-woocommerce';
    const POST_NAME = 'guestlist-csv';

    /** @noinspection PhpUnused Is used in hook at bottom of file */
    static function hook_plugins_loaded()
    {
        global $pagenow;
        if ($pagenow == 'admin.php'
            && current_user_can('export') &&
            $_GET['page'] === self::PAGE_NAME
            && isset($_POST[self::POST_NAME])) {
            self::output_csv_file();
            exit();
        }
    }

    static function output_csv_file()
    {
        $product_id = $_POST['product'];
        /** @var WC_Product $product */
        $product = wc_get_product($product_id);
        $order_ids = self::retrieve_orders_ids_from_a_product_id($product_id);

        $csv_headers = ['Order#', 'Aantal', 'Naam', 'E-mail', 'Activiteit'];
        $attributes = array_keys($product->get_attributes('edit'));
        $csv_headers = array_merge($csv_headers, $attributes);
        $filename = 'gastenlijst' . '-' . $product->get_slug() . '.csv';

        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '";');
        header("Pragma: no-cache");
        header("Expires: 0");

        $f = fopen('php://output', 'w');
        fputcsv($f, $csv_headers, ',');

        foreach ($order_ids as $order_id) {
            $order = wc_get_order($order_id);
            foreach ($order->get_items() as $item) {
                if (!($item instanceof WC_Order_Item_Product)) {
                    continue;
                }

                if ($item->get_product_id() == $product_id) {
                    $row = [];
                    $row['order_id'] = $order->get_id();
                    $row['item_quantity'] = $item->get_quantity();
                    $row['customer_name'] = $order->get_formatted_billing_full_name();
                    $row['customer_email'] = $order->get_billing_email();
                    $row['item_name'] = $item->get_name();
                    foreach ($attributes as $attribute) {
                        $row[$attribute] = $item->get_meta($attribute);
                    }
                    fputcsv($f, $row, ',');
                }
            }
        }
    }

    static function retrieve_orders_ids_from_a_product_id($product_id)
    {
        global $wpdb;

        $orders_statuses = "'wc-completed'";

        # Get All defined statuses Orders IDs for a defined product ID (or variation ID)
        /** @noinspection SqlResolve */
        return $wpdb->get_col("
            SELECT DISTINCT woi.order_id
            FROM {$wpdb->prefix}woocommerce_order_itemmeta as woim, 
                 {$wpdb->prefix}woocommerce_order_items as woi, 
                 {$wpdb->prefix}posts as p
            WHERE  woi.order_item_id = woim.order_item_id
            AND woi.order_id = p.ID
            AND p.post_status IN ( $orders_statuses )
            AND woim.meta_key IN ( '_product_id', '_variation_id' )
            AND woim.meta_value LIKE '$product_id'
            ORDER BY woi.order_item_id DESC"
        );
    }

}

add_action('wp_loaded', [GuestListCsvDownload::class, 'hook_plugins_loaded']);
