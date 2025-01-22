<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>
<tr>
    <td scope="row">
        <label for="" class="awdr-left-align"><?php esc_html_e('Show cross sell block on cart?', 'woo-discount-rules-pro') ?></label>
        <span class="wdr_settings_desc_text awdr-clear-both"><?php esc_html_e('This setting applies only for Buy X Get Y - Product rule  type only.', 'woo-discount-rules-pro'); ?></span>
    </td>
    <td>
        <?php $show_cross_sell_on_cart = $configuration->getConfig('show_cross_sell_on_cart', 0); ?>
        <input type="radio" name="show_cross_sell_on_cart" class="settings_option_show_hide"
               id="show_cross_sell_on_cart_1" data-name="hide_show_cross_sell_blocks"
               value="1" <?php echo($show_cross_sell_on_cart == 1 ? 'checked' : '') ?>><label
                for="show_cross_sell_on_cart_1"><?php esc_html_e('Yes', 'woo-discount-rules-pro'); ?></label>

        <input type="radio" name="show_cross_sell_on_cart" class="settings_option_show_hide"
               id="show_cross_sell_on_cart_2" data-name="hide_show_cross_sell_blocks"
               value="0" <?php echo($show_cross_sell_on_cart == 0 ? 'checked' : '') ?>><label
                for="show_cross_sell_on_cart_2"><?php esc_html_e('No', 'woo-discount-rules-pro'); ?></label>
    </td>
</tr>
<tr class="hide_show_cross_sell_blocks" style="<?php echo (!$show_cross_sell_on_cart) ? 'display:none' : ''; ?>">
    <td scope="row">
        <label for="cross_sell_on_cart_limit" class="awdr-left-align"><?php esc_html_e('Limit', 'woo-discount-rules-pro') ?></label>
        <span class="wdr_settings_desc_text awdr-clear-both"><?php esc_html_e('Cross sell products limit to display', 'woo-discount-rules-pro'); ?></span>
    </td>
    <td>
        <input name="cross_sell_on_cart_limit" type="number" id="cross_sell_on_cart_limit" value="<?php echo esc_attr($configuration->getConfig('cross_sell_on_cart_limit', 2)); ?>" placeholder="2"/>
    </td>
</tr>
<tr class="hide_show_cross_sell_blocks" style="<?php echo (!$show_cross_sell_on_cart) ? 'display:none' : ''; ?>">
    <td scope="row">
        <label for="cross_sell_on_cart_column" class="awdr-left-align"><?php esc_html_e('Column', 'woo-discount-rules-pro') ?></label>
        <span class="wdr_settings_desc_text awdr-clear-both"><?php esc_html_e('Cross sell Column', 'woo-discount-rules-pro'); ?></span>
    </td>
    <td>
        <input name="cross_sell_on_cart_column" id="cross_sell_on_cart_column" type="number" value="<?php echo esc_attr($configuration->getConfig('cross_sell_on_cart_column', 2)); ?>" placeholder="2"/>
    </td>
</tr>
<tr class="hide_show_cross_sell_blocks" style="<?php echo (!$show_cross_sell_on_cart) ? 'display:none' : ''; ?>">
    <td scope="row">
        <label for="cross_sell_on_cart_order_by" class="awdr-left-align"><?php esc_html_e('Order by', 'woo-discount-rules-pro') ?></label>
        <span class="wdr_settings_desc_text awdr-clear-both"><?php esc_html_e('Cross sell Order by', 'woo-discount-rules-pro'); ?></span>
    </td>
    <td>
        <?php $cross_sell_on_cart_order_by = $configuration->getConfig('cross_sell_on_cart_order_by', 'rand'); ?>
        <select name="cross_sell_on_cart_order_by" id="cross_sell_on_cart_order_by">
            <option value="rand"<?php echo ($cross_sell_on_cart_order_by == 'rand')? " selected": '' ?>><?php esc_html_e('Rand', 'woo-discount-rules-pro'); ?></option>
            <option value="menu_order"<?php echo ($cross_sell_on_cart_order_by == 'menu_order')? " selected": '' ?>><?php esc_html_e('Menu order', 'woo-discount-rules-pro'); ?></option>
            <option value="price"<?php echo ($cross_sell_on_cart_order_by == 'price')? " selected": '' ?>><?php esc_html_e('Price', 'woo-discount-rules-pro'); ?></option>
        </select>
    </td>
</tr>
<tr class="hide_show_cross_sell_blocks" style="<?php echo (!$show_cross_sell_on_cart) ? 'display:none' : ''; ?>">
    <td scope="row">
        <label for="cross_sell_on_cart_order" class="awdr-left-align"><?php esc_html_e('Ordering', 'woo-discount-rules-pro') ?></label>
        <span class="wdr_settings_desc_text awdr-clear-both"><?php esc_html_e('Cross sell Ordering', 'woo-discount-rules-pro'); ?></span>
    </td>
    <td>
        <?php $cross_sell_on_cart_order = $configuration->getConfig('cross_sell_on_cart_order', 'desc'); ?>
        <select name="cross_sell_on_cart_order" id="cross_sell_on_cart_order">
            <option value="desc"<?php echo ($cross_sell_on_cart_order == 'desc')? " selected": '' ?>><?php esc_html_e('Desc', 'woo-discount-rules-pro'); ?></option>
            <option value="asc"<?php echo ($cross_sell_on_cart_order == 'asc')? " selected": '' ?>><?php esc_html_e('Asc', 'woo-discount-rules-pro'); ?></option>
        </select>
    </td>
</tr>
