<div class="sort-item projection columns-projection" data-entity-id="{{data.entity.entityId}}">
	<div class="header-container">
		<div class="left-position">
			<div class="title-container">
				<span class="columns-title"><?php echo esc_html( $title ); ?></span>
			</div>
		</div>
		<div class="right-position">
			<div class="actions-wrapper entity-id-{{data.entity.entityId}}">
				<div class="button-link add-column-action">
					<span class="text"><?php echo esc_html__( 'Add column', 'wcpf' ); ?></span>
				</div>
				<div class="button-link remove-action">
					<span class="text"><?php echo esc_html__( 'Remove', 'wcpf' ); ?></span>
				</div>
			</div>
		</div>
	</div>
	<div class="body-container"></div>
</div>
