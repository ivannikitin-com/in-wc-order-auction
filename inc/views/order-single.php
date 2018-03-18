<?php
/**
 * Вывод формы для распеделения заказа
 * В текущем контексте 
 * 	$this 		-- экземпляр класса INWCOA_Order
 *	$nonce 		-- поле nonce для форм 
 *	$orderId	-- ID заказа
 */
$order = new WC_Order( $orderId );
$orderData = $order->get_data();

$address = ( empty( $orderData['shipping']['address_1'] ) ) ?
	implode(', ', array( $orderData['billing']['city'], $orderData['billing']['address_1'], $orderData['billing']['address_2']  ) ) :
	implode(', ', array( $orderData['shipping']['city'], $orderData['shipping']['address_1'], $orderData['shipping']['address_2']  ) );

$customer = ( empty( $orderData['shipping']['last_name'] ) ) ?
	implode(', ', array( $orderData['billing']['last_name'], $orderData['billing']['first_name']  ) ) :
	implode(' ', array( $orderData['shipping']['last_name'], $orderData['shipping']['first_name'] ) );

$tel = $orderData['billing']['phone'];
$telNum = preg_replace('/[^0-9+]/', '', $tel);

$customerNote = ( isset( $orderData['customer_note'] ) ) ? $orderData['customer_note'] : '';

?>
<section id="inwcoa-order-details">
<h1><?php esc_html_e( 'Заказ №', INWCOA ) ?><?php echo $orderId?></h1>
<form action="<?php echo $_SERVER['REQUEST_URI'] ?>" method="post">
	<input type="hidden" name="nonce" value="<?php echo $nonce ?>" />
	<div>
		<span class="label"><?php esc_html_e( 'Заказчик', INWCOA ) ?></span>:
		<span class="detail"><?php echo $customer ?></span>
	</div>
	<div>
		<span class="label"><?php esc_html_e( 'Адрес', INWCOA ) ?></span>:
		<span class="detail"><?php echo $address ?></span>
	</div>
<?php if ( ! empty( $telNum ) ): ?>
	<div class="action">
		<a href="tel:<?php echo $telNum ?>" class="button tel"><?php echo $telNum ?></a>
	</div>
<?php endif ?>
<?php if ( ! empty( $customerNote ) ): ?>
	<div>
		<p><?php esc_html_e( 'Комментарий пользователя', INWCOA ) ?>:</p>
		<?php echo $customerNote ?>
	</div>
<?php endif ?>
<div class="action">
	<button type="submit" name="inwcoa-order-set-performer" value="1"><?php esc_html_e( 'Взять заказ', INWCOA ) ?></button>
</div>
</form>
</section>