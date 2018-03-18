<?php
/**
 * Вывод метабокса исполнителя
 * В текущем контексте 
 * 	$this 		-- экземпляр класса INWCOA_Order
 *	$performer 	-- объект WP_User с данными исполнителя
 */
?>
<div class="performer-photo">
	<?php echo get_avatar( $performer->ID, 120 ); ?>
</div>
<div class="performer-name">
	<?php echo $performer->display_name ?>
</div>