<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\Base;
use Objectiv\Plugins\Checkout\Core\Admin;
use Objectiv\Plugins\Checkout\Main;
use ElementorPro\Modules\ThemeBuilder\Module as Theme_Builder_Module;

class ElementorPro extends Base {
	function is_available() {
		return defined( 'ELEMENTOR_PRO_VERSION' );
	}

	public function pre_init() {
		add_action( 'cfw_admin_integrations_settings', array( $this, 'admin_integration_setting' ) );
	}

	function run() {
		if ( Main::instance()->get_settings_manager()->get_setting( 'enable_elementor_pro_support' ) === 'yes' ) {

			/** @var Theme_Builder_Module $theme_builder_module */
			$theme_builder_module = Theme_Builder_Module::instance();

			$header_documents_by_conditions = $theme_builder_module->get_conditions_manager()->get_documents_for_location( 'header' );
			$footer_documents_by_conditions = $theme_builder_module->get_conditions_manager()->get_documents_for_location( 'footer' );

			if ( ! empty( $header_documents_by_conditions ) ) {
				add_action(
					'cfw_custom_header',
					function() {
						elementor_theme_do_location( 'header' );
					}
				);
			}

			if ( ! empty( $footer_documents_by_conditions ) ) {
				add_action(
					'cfw_custom_footer',
					function() {
						elementor_theme_do_location( 'footer' );
					}
				);
			}
		}
	}

	/**
	 * @param Admin $admin
	 */
	function admin_integration_setting( $admin ) {
		?>
		<tr>
			<th scope="row" valign="top">
				<label for="<?php echo $admin->plugin_instance->get_settings_manager()->get_field_name( 'enable_elementor_pro_support' ); ?>"><?php cfw_e( 'Elementor Pro', 'checkout-wc' ); ?></label>
			</th>
			<td>
				<input type="hidden" name="<?php echo $admin->plugin_instance->get_settings_manager()->get_field_name( 'enable_elementor_pro_support' ); ?>" value="no" />
				<label><input type="checkbox" name="<?php echo $admin->plugin_instance->get_settings_manager()->get_field_name( 'enable_elementor_pro_support' ); ?>" id="<?php echo $admin->plugin_instance->get_settings_manager()->get_field_name( 'enable_elementor_pro_support' ); ?>" value="yes" <?php echo $admin->plugin_instance->get_settings_manager()->get_setting( 'enable_elementor_pro_support' ) === 'yes' ? 'checked' : ''; ?> /> <?php cfw_e( 'Enable Elementor Pro support.', 'checkout-wc' ); ?></label>
				<p><span class="description"><?php cfw_e( 'Allow Elementor Pro to replace header and footer.', 'checkout-wc' ); ?></span></p>
			</td>
		</tr>
		<?php
	}
}
