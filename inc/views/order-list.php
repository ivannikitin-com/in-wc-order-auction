<?php
/**
 * Вывод списка нераспределенных заказов
 * В текущем контексте 
 * 	$this -- экземпляр класса INWCOA_Order
 *	$nonce -- поле nonce для форм
 */
?>
<section id="inwcoa-order-list">
<h1><?php esc_html_e( 'Нераспределенные заказы', INWCOA ) ?></h1>
<table>
	<thead>
		<tr>
			<th><?php esc_html_e( 'Заказ №', INWCOA ) ?></th>
			<th><?php esc_html_e( 'Дата', INWCOA ) ?></th>
			<th><?php esc_html_e( 'Адрес', INWCOA ) ?></th>
			<th><?php esc_html_e( 'Заказчик', INWCOA ) ?></th>
		</tr>
	</thead>
	<tbody>
<?php
	$orders = $this->getUnassignedOrders();
	foreach ( $orders as $order ):	
		$orderData = $order->get_data();
	
		$link = ( strpos( $_SERVER['REQUEST_URI'], '?') !== false) ? 
			$_SERVER['REQUEST_URI'] . '&' . self::ORDER_ID . '=' . $orderData['id'] :
			$_SERVER['REQUEST_URI'] . '?' . self::ORDER_ID . '=' . $orderData['id'];

		$address = ( empty( $orderData['shipping']['address_1'] ) ) ?
			implode(', ', array( $orderData['billing']['city'], $orderData['billing']['address_1'], $orderData['billing']['address_2']  ) ) :
			implode(', ', array( $orderData['shipping']['city'], $orderData['shipping']['address_1'], $orderData['shipping']['address_2']  ) );

		$customer = ( empty( $orderData['shipping']['last_name'] ) ) ?
			implode(', ', array( $orderData['billing']['last_name'], $orderData['billing']['first_name']  ) ) :
			implode(' ', array( $orderData['shipping']['last_name'], $orderData['shipping']['first_name'] ) );		
		?>
		<tr>
			<td><a href="<?php echo $link ?>"><?php echo $orderData['id'] ?></a></td>
			<td><a href="<?php echo $link ?>"><?php echo $orderData['date_created']->date('Y-m-d H:i') ?></a></td>
			<td><a href="<?php echo $link ?>"><?php echo $address ?></a></td>
			<td><a href="<?php echo $link ?>"><?php echo $customer ?></a></td>
		</tr>
<?php endforeach; ?>
	</tbody>
</table>
</section>