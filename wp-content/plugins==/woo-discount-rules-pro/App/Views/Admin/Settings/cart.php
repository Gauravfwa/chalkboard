<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>
<tr>
    <td scope="row">
        <label for="free_shipping_title" class="awdr-left-align"><?php esc_html_e('Free shipping title', 'woo-discount-rules-pro') ?></label>
        <span class="wdr_desc_text awdr-clear-both"><?php esc_html_e('Title for free shipping', 'woo-discount-rules-pro'); ?></span>
    </td>
    <td>
        <input type="text" name="free_shipping_title"
               value="<?php echo esc_attr($configuration->getConfig('free_shipping_title', 'Free shipping')); ?>">
    </td>
</tr>

<tr>
    <td scope="row">
        <label for="hide_other_shipping" class="awdr-left-align"><?php esc_html_e('Hide other shipping methods', 'woo-discount-rules-pro') ?></label>
        <span class="wdr_desc_text awdr-clear-both"><?php esc_html_e('Hide other shipping methods when free shipping rule is applied.', 'woo-discount-rules-pro'); ?></span>
    </td>
    <td>
        <input type="radio" name="wdr_hide_other_shipping" class=""
               id="wdr_hide_other_shipping_yes"
               value="1" <?php echo($configuration->getConfig('wdr_hide_other_shipping', 0) ? 'checked' : '') ?>><label
                for="wdr_hide_other_shipping_yes"><?php esc_html_e('Yes', 'woo-discount-rules-pro'); ?></label>

        <input type="radio" name="wdr_hide_other_shipping" class=""
               id="wdr_hide_other_shipping_no"
               value="0" <?php echo(!$configuration->getConfig('wdr_hide_other_shipping', 0) ? 'checked' : '') ?>><label
                for="wdr_hide_other_shipping_no"><?php esc_html_e('No', 'woo-discount-rules-pro'); ?></label>
    </td>
</tr>