<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// POST is handled by hook in GuestListCsvDownload.php
?>

<h1>Gastenlijst exporteren</h1>
<form method="post">
    <div>
        <label>Product:
            <select name="product">
                <?php
                /** @var WC_Product[] $products */
                $products = wc_get_products(['limit' => -1]);
                foreach ($products as $product): ?>
                    <option value="<?php echo $product->get_id() ?>"><?php echo $product->get_name(); ?></option>
                <?php endforeach; ?>
            </select>
        </label>
    </div>

    <br>

    <input name="<?php echo GuestListCsvDownload::POST_NAME; ?>" type="submit" class="button-primary"
           value="Gastenlijst ophalen">
</form>
