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
		add_action( 'admin_menu', array( $this, 'addAdminMenu') );								// Страница Нераспределенные заказы
		add_action( 'add_meta_boxes_shop_order', array( $this, 'addMetabox') );					// Мета-бокс в заказе WooCOmmerce
		add_filter( 'manage_shop_order_posts_columns', array( $this, 'addOrderColumns') );		// Добавляет колонки в таблицу заказов
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
		
		// ID исполнителя
		$performerId = ( $performer instanceof WP_User ) ? $performerId->ID : (int) $performer;
		
		// Проеерка корректности
		if ( ! $order || empty( $performerId ) )
			return false;
		
		// Сохранение мета-поля
		$order->update_meta_data( self::META_PERFORMER, $performerId );
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
	 * Добавляет мета-бокс в CRM
	 * @param WP_Post $post		Запись (заказ), который сейчас показывается
	 */
	public function showMetabox( $post )
	{
		$performer = $this->getPerformer( $post->ID );
		if ( $performer )
			include( $this->plugin->path . 'inc/views/metabox-performer.php' );
		else
			esc_html_e( 'Исполнитель не назначен', INWCOA );
	}	
	
	/**
	 * Добавляет колонки в таблицу заказов
	 * @param mixed $columns	Массив колонок в таблице
	 */
	public function addOrderColumns( $columns )
	{
		$columns['inwcoa_performer'] = __( 'Исполнитель', INWCOA );
		return $columns;
	}
}