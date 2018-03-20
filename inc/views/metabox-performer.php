<?php
/**
 * Вывод метабокса исполнителя
 * В текущем контексте 
 * 	$this 		-- экземпляр класса INWCOA_Order
 *	$performer 	-- объект WP_User с данными исполнителя
 */

$currentPerformer = ( $performer ) ? $performer->ID : 0;
?>
<?php wp_nonce_field( INWCOA . '_metabox_save', 'inwcoa-nonce' ); ?>
<?php if ( $currentPerformer ): ?>
<div class="performer-photo">
	<?php echo get_avatar( $currentPerformer, 120 ); ?>
</div>
<?php endif ?>
<div class="performer-select">
	<?php wp_dropdown_users( array( 
			'name'				=> 'performer',
			'selected' 			=> $currentPerformer,
			'role__in' 			=> ( WP_DEBUG ) ? array( 'administrator', 'performer' ) : array( 'performer' ),
			'show_option_none'  => __( 'Нет исполнителя', INWCOA )
		  ) ); ?>
</div>