<tr class="rule-row" data-id="{{data.ruleId}}">
	<td class="param">
		<select name="<?php echo esc_attr( $option_key ); ?>[{{data.groupId}}][{{data.ruleId}}][param]" class="rule-select param-select">
			<?php foreach ( $use_entries as $entry_id => $entry_title ) : ?>
				<option value="<?php echo esc_attr( $entry_id ); ?>"><?php echo esc_html( $entry_title ); ?></option>
			<?php endforeach; ?>
		</select>
	</td>
	<td class="operator">
		<select name="<?php echo esc_attr( $option_key ); ?>[{{data.groupId}}][{{data.ruleId}}][operator]" class="rule-select operator-select">
			<option value="=="><?php echo esc_html__( 'is equal to', 'wcpf' ); ?></option>
			<option value="!="><?php echo esc_html__( 'is not equal to', 'wcpf' ); ?></option>
		</select>
	</td>
	<td class="value">
		<select name="<?php echo esc_attr( $option_key ); ?>[{{data.groupId}}][{{data.ruleId}}][value]" class="rule-select value-select"></select>
	</td>
	<td class="add">
		<button class="button add-rule"><?php echo esc_html__( 'and', 'wcpf' ); ?></button>
	</td>
	<td class="remove">
		<button class="button remove-rule"></button>
	</td>
</tr>
