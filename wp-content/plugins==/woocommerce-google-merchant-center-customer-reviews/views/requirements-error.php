<div class="error">
	<p>
		<?php
		echo GMC_NAMES;
		_e( ' error: Your environment doesn\'t meet all of the system requirements listed below.', 'wc-google-merchant-center-customer-reviews' );
		?>
	</p>
	<ul class="ul-disc">
		<li>
			<strong>
				<?php
				_e( 'Plugin ', 'wc-google-merchant-center-customer-reviews' );
				echo GMC_PLUGIN_DEPENDENCIES;
				?>
			</strong>
			<?php _e( ' Not activated', 'wc-google-merchant-center-customer-reviews' ); ?></em>
		</li>
	</ul>
	<p>
		<?php _e( 'If you need to upgrade your version of PHP you can ask your hosting company for assistance, and if you need help upgrading WordPress you can refer to ', 'wc-google-merchant-center-customer-reviews' );
		?>
		<a href="http://codex.wordpress.org/Upgrading_WordPress">
			<?php
			_e( 'the Codex.', 'wc-google-merchant-center-customer-reviews' );
			?>
		</a>
	</p>
</div>
