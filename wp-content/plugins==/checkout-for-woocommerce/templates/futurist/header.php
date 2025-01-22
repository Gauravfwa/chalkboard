<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<header id="cfw-header" class="container-fluid">
	<div class="row">
		<div class="col-12">
			<div id="cfw-logo-container">
				<!-- TODO: Find a way to inject certain backend settings as global params without having to put logic in the templates -->
				<div class="cfw-logo">
					<?php
					/**
					 * Filters header logo / title link URL
					 *
					 * @since 3.0.0
					 *
					 * @param string $url The link URL
					 */
					$url = apply_filters( 'cfw_header_home_url', get_home_url() );
					?>
					<a title="<?php echo get_bloginfo( 'name' ); ?>" href="<?php echo $url; ?>" class="logo"></a>
				</div>
			</div>
		</div>
	</div>
</header>
