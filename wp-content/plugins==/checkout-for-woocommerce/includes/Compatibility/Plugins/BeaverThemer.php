<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\Base;
use Objectiv\Plugins\Checkout\Core\Admin;

class BeaverThemer extends Base {
	function is_available() {
		return class_exists( '\\FLThemeBuilderLayoutRenderer' );
	}

	function pre_init() {
		add_action( 'cfw_admin_integrations_settings', array( $this, 'admin_integration_setting' ) );
	}

	function run() {
		if ( cfw_get_main()->get_settings_manager()->get_setting( 'enable_beaver_themer_support' ) === 'yes' ) {
			add_action( 'cfw_custom_header', 'FLThemeBuilderLayoutRenderer::render_header' );
			add_action( 'cfw_custom_footer', 'FLThemeBuilderLayoutRenderer::render_footer' );
		}
	}

	/**
	 * @param Admin $admin
	 */
	function admin_integration_setting( $admin ) {
		?>
		<tr>
			<th scope="row" valign="top">
				<label for="<?php echo $admin->plugin_instance->get_settings_manager()->get_field_name( 'enable_beaver_themer_support' ); ?>"><?php cfw_e( 'Beaver Themer', 'checkout-wc' ); ?></label>
			</th>
			<td>
				<input type="hidden" name="<?php echo $admin->plugin_instance->get_settings_manager()->get_field_name( 'enable_beaver_themer_support' ); ?>" value="no" />
				<label><input type="checkbox" name="<?php echo $admin->plugin_instance->get_settings_manager()->get_field_name( 'enable_beaver_themer_support' ); ?>" id="<?php echo $admin->plugin_instance->get_settings_manager()->get_field_name( 'enable_beaver_themer_support' ); ?>" value="yes" <?php echo $admin->plugin_instance->get_settings_manager()->get_setting( 'enable_beaver_themer_support' ) === 'yes' ? 'checked' : ''; ?> /> <?php cfw_e( 'Enable Beaver Themer support.', 'checkout-wc' ); ?></label>
				<p><span class="description"><?php cfw_e( 'Allow Beaver Themer to replace header and footer.', 'checkout-wc' ); ?></span></p>
			</td>
		</tr>
		<?php
	}
}
