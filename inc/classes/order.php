<?php
/**
 * Класс реализует показ и назначение заказов на исполнителей
 */
class INWCOA_Order extends INWCOA__Base
{
	/**
	 * Конструктор 
	 * @param INWCOA_Plugin $plugin Экземпляр основного класса плагина
	 */
	public function __construct( $plugin )
	{
		// Вызов родительского конструктора
		parent::__construct( $plugin );
		
		// Хуки
		add_action( 'admin_menu', array( $this, 'addAdminMenu') );									// Страница Нераспределенные заказы
		add_action( 'add_meta_boxes_shop_order', array( $this, 'addMetabox') );						// Мета-бокс в заказе WooCOmmerce
		add_action( 'save_post', array( $this, 'saveMetabox') );										// Сохранение метабокса
		add_filter( 'manage_shop_order_posts_columns', array( $this, 'addOrderColumns'), 50, 1 );	// Добавляет колонки в таблицу заказов
		add_filter( 'manage_shop_order_posts_custom_column', array( $this, 'showOrderColumns') );	// Добавляет колонки в таблицу заказов
	}
	
	/**
	 * Добавляет страницу распределения заказов в WooCommerce
	 */
	public function addAdminMenu()
	{
		add_submenu_page( 'woocommerce',
			__( 'Нераспределенные заказы', INWCOA ), 	// Текст, который будет использован в теге title на странице, настроек
			__( 'Нераспределенные заказы', INWCOA ),	// Текст, который будет использован в качестве называния для пункта меню
			'manage_woocommerce', 						// Права доступа для пользователя, чтобы ему был показан этот пункт меню
			INWCOA, 									// Идентификатор меню
			array( $this, 'showAdminPage' ) 			// Вывод страницы распределения заказов в WooCommerce
		);
	}
	
	/**
	 * Показывает страницу распределения заказов в WooCommerce
	 */
	public function showAdminPage()
	{
		echo $this->getHTML();
	}
	
	/**
	 * GET параметр, в котором передается номер заказа
	 * @static
	 */
	const ORDER_ID = 'order_id';
	
	/**
	 * Метод возвращает HTML списка заказов или заказа
	 * @return string
	 */
	public function getHTML()
	{
		// Номер заказа для отображения
		$orderId = ( isset( $_GET[ self::ORDER_ID ] ) ) ? absint( $_GET[ self::ORDER_ID ] ) : 0;
		
		// Обработка POST
		if ( $_SERVER['REQUEST_METHOD'] == 'POST' )
		{
			// Проверка nonce
			if ( wp_verify_nonce( $_REQUEST['nonce'], INWCOA ) )
			{
				// Обработка формы order-single, Кнопка назначения исполнителя
				if ( isset( $_POST['inwcoa-order-set-performer'] ) )
				{
					$this->assignOrder( $orderId, get_current_user_id() );
				}
			}
		}
		
		$nonce = wp_create_nonce( INWCOA );
		
		// Формирование HTML 
		ob_start();

		// Подключение шаблонов
		if ( empty( $orderId ) )
		{
			// Код заказа не указан, покажем все нераспределенные заказы
			include( $this->plugin->path . 'inc/views/order-list.php' );
		}
		elseif ( $this->isAssigned( $orderId ) )
		{
			// Этот заказ уже назначен, переадресания на агдминистрирование в WooCommerce
			wp_redirect( home_url() . '/wp-admin/post.php?action=edit&post=' . $orderId );
			exit;
		}
		else
		{
			// Этот заказ не назначен, Покажем форму назначения заказа
			include( $this->plugin->path . 'inc/views/order-single.php' );
		}

		// Результат
		$html = ob_get_contents();
		ob_end_clean();
	
		return $html;
	}
	
	/**
	 * Мета-поле заказа "Исполнитель"
	 * @static
	 */
	const META_PERFORMER = 'inwcoa_performer';
	
	/**
	 * Метод проверяет назначение заказа
	 * @param WC_Order|int	$order		Заказ или ID заказа	 
	 * @return bool
	 */
	public function isAssigned( $order )
	{
		return (bool) $this->getPerformer( $order );
	}
	
	/**
	 * Метод вовзращает испольнителя заказа
	 * @param WC_Order|int	$order		Заказ или ID заказа	 
	 * @return WP_User
	 */
	public function getPerformer( $order )
	{
		// Текущий заказ
		if ( ! ( $order instanceof WC_Order ) )
			$order = new WC_Order( $order );
		
		$performerId = $order->get_meta( self::META_PERFORMER );
		if ( empty( $performerId ) )
			return false;
		
		return new WP_User( $performerId );
	}	
	
	/**
	 * Метод возвращает нераспределенные заказы
	 * @return mixed WC_Order
	 */
	public function getUnassignedOrders()
	{
		// Запрос
		$query = new WP_Query( array(
			'post_type'		=> array( 'shop_order' ),				// Тип заказы WC
			'fields'		=> 'ids',								// Запрашиваем только ID заказов
			'numberposts'	=> -1,									// Запрашиваем все заказы
			'post_status'	=> array( 'on-hold', 'processing' ),	// Требуемые статусы
			'meta_query' => array(									// Мета-запрос
				'relation' => 'OR',
				array(
					'key' => self::META_PERFORMER,					// Поле META_PERFORMER пусто
					'value' => false,
					'type' => 'BOOLEAN'
				),
				array(
					'key' => self::META_PERFORMER,					// Поле META_PERFORMER отсуствует
					'compare' => 'NOT EXISTS'
				)
			)			
		));
		
		// Формируем массив заказов
		$orders = array();
		foreach ( $query->posts as $id )
			$orders[] = new WC_Order( $id );
		
		return $orders;
	}
	
	/**
	 * Метод назначает исполнителя на заказ
	 * @param WC_Order|int	$order		Заказ или ID заказа
	 * @param WP_User|int	$performer	Исполнитель или ID исполнителя
	 * @return bool 
	 */
	public function assignOrder( $order, $performer )
	{
		// Текущий заказ
		if ( ! ( $order instanceof WC_Order ) )
			$order = new WC_Order( $order );
		
		// Проеерка корректности
		if ( ! $order )
			return false;		
		
		// Текущий исполнитель
		$currentPerformer = $this->getPerformer( $order );
		$currentPerformerId = ( $currentPerformer ) ? $currentPerformer->ID : 0;
		
		// Новый исполнитель
		if ( ! empty( $performer ) && ! ( $performer instanceof WP_User ) )
			$performer = new WP_User( $performer );
		else
			$performer = null;
		
		$performerId = ( $performer ) ? $performer->ID : 0;
		
		// Изменений не было
		if ( $currentPerformerId == $performerId )
			return false;
		
		// Сохранение мета-поля
		$order->update_meta_data( self::META_PERFORMER, $performerId );
		
		if ( $performerId != 0 ) 
			$order->add_order_note( __( 'Исполнитель', INWCOA ) . ': ' . $performer->display_name, false, true );
		else
			$order->add_order_note( __( 'Исполнитель не назначен', INWCOA ), false, true );
		$order->save();
		
		return true;
	}
	
	/**
	 * Добавляет мета-бокс в CRM
	 */
	public function addMetabox()
	{
		add_meta_box( INWCOA . '_performer', 		// id атрибут HTML тега, контейнера блока.
					 __( 'Исполнитель', INWCOA ),	// Заголовок/название блока. 
					 array( $this, 'showMetabox' ),	// Функция, которая выводит на экран HTML
					 'shop_order',					// Тип записи, на экране которой будет отображаться метабокс
					 'side',						// Место где должен показываться блок
					 'high');						// Приоритет блока для показа выше или ниже остальных блоков
	}
	
	/**
	 * Добавляет мета-бокс в заказы
	 * @param WP_Post $post		Запись (заказ), который сейчас показывается
	 */
	public function showMetabox( $post )
	{
		$performer = $this->getPerformer( $post->ID );
		include( $this->plugin->path . 'inc/views/metabox-performer.php' );
	}	
	
	/**
	 * Сохраняет данные мета-бокса 
	 * @param WP_Post $post		Запись (заказ), который сейчас показывается
	 */
	public function saveMetabox( $postId )
	{
		// Если это не наш тип записей, ничего не недаем
		if ( $_POST['post_type'] != 'shop_order' ) 
			return $postId;
		
		// Если это автосохранение, ничего не делаем
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
			return $postId;

		// Если неправильный nonce - это не наша форма, ничего не делаем
		if ( ! wp_verify_nonce( $_POST['inwcoa-nonce'], INWCOA . '_metabox_save' ) )
			return $postId;
		
		// Если текущий пользователь не имеет прав на назначение, ничего не делаем
		if ( ! current_user_can( 'supervisor' ) && ! current_user_can( 'administrator' ) )
			return $postId;
				
		$performerId = ( isset( $_POST['performer'] ) ) ? (int) $_POST['performer'] : 0;
		$this->assignOrder( $postId, $performerId );
	}	
	
	/**
	 * Добавляет колонки в таблицу заказов
	 * @param mixed $columns	Массив колонок в таблице
	 */
	public function addOrderColumns( $columns )
	{
		$show_columns                     = array();
		$show_columns['cb']               = $columns['cb'];
		$show_columns['order_number']     = __( 'Order', 'woocommerce' );
		$show_columns['order_date']       = __( 'Date', 'woocommerce' );
		$show_columns['order_status']     = __( 'Status', 'woocommerce' );
		$show_columns['billing_address']  = __( 'Billing', 'woocommerce' );
		$show_columns['shipping_address'] = __( 'Ship to', 'woocommerce' );
		$show_columns['inwcoa_address']   = __( 'Адрес', INWCOA );
		$show_columns['inwcoa_performer'] = __( 'Исполнитель', INWCOA );
		$show_columns['order_total']      = __( 'Total', 'woocommerce' );
		$show_columns['wc_actions']       = __( 'Actions', 'woocommerce' );
		$show_columns = array_merge( $show_columns, $columns );
		unset( $show_columns['billing_address'] );
		unset( $show_columns['shipping_address'] );	
		return $show_columns;
	}
	
  /** 
   * Output custom columns for coupons. 
   * @param string $column 
   */ 
  public function showOrderColumns( $column ) 
  { 
      global $post, $the_order; 
 
      if ( empty( $the_order ) || $the_order->get_id() !== $post->ID ) 
	  { 
          $the_order = wc_get_order( $post->ID ); 
      } 
	  
      switch ( $column ) 
	  { 
          case 'inwcoa_performer' :
			 $performer = $this->getPerformer( $the_order );
			 if ( $performer )
				  echo $performer->display_name;
			 else
				  echo '-';
          	 break;

		  case 'inwcoa_address' :
              // Если указан адрес доставки, используем его
			  if ( $address = $the_order->get_formatted_shipping_address() ) 
                  echo '<a target="_blank" href="' . esc_url( $the_order->get_shipping_address_map_url() ) . '">' . esc_html( preg_replace( '#<br\s*/?>#i', ', ', $address ) ) . '</a>'; 
			  else 
			  { 
				  // Если указан адрес заказчика, используем его
				  if ( $address = $the_order->get_formatted_billing_address() ) 
					  echo esc_html( preg_replace( '#<br\s*/?>#i', ', ', $address ) ); 
				  else 
					  echo '–';  
              } 
			  
			  // Если есть телефон, покажем его
              if ( $the_order->get_billing_phone() ) 
                  echo '<small class="meta">' . __( 'Phone:', 'woocommerce' ) . ' ' . esc_html( $the_order->get_billing_phone() ) . '</small>';  			  
			  
          	break;			  
	  }
  }
}