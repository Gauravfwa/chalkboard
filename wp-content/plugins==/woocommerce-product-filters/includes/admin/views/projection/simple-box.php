<div class="sort-item projection simple-box-projection" data-entity-id="{{data.entity.entityId}}">
	<div class="header-container">
		<div class="left-position">
			<div class="title-container entity-id-{{data.entity.entityId}}">
				<span class="entity-title">{{data.entity.title}}</span>
				<span class="type-title"><?php echo esc_html( $title ); ?></span>
			</div>
		</div>
		<div class="right-position">
			<div class="actions-wrapper entity-id-{{data.entity.entityId}}">
				<div class="button-link edit-action">
					<span class="text"><?php echo esc_html__( 'Edit', 'wcpf' ); ?></span>
				</div>
				<div class="button-link remove-action">
					<span class="text"><?php echo esc_html__( 'Remove', 'wcpf' ); ?></span>
				</div>
			</div>
		</div>
	</div>
	<div class="body-container">
		<div class="sort-list entity-id-{{data.entity.entityId}}"></div>
	</div>
</div>
