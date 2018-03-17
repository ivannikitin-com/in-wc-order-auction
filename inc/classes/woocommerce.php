<?php
/**
 * Класс интеграции с WooCommerce
 * Обеспечивает основноые функции, обработку заказов по хукам WC
 */
class INWCOA_WooCommerce extends INWCOA__Base
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
		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'addSettingsTab'), 50 );	// Добавляет новую страницу в настройки WC
		add_action( 'woocommerce_settings_tabs_'. INWCOA , array( $this, 'showSettings') );		// Показывает настройки на новой панели
		add_action( 'woocommerce_update_options_'. INWCOA , array( $this, 'updateSettings') );	// Обновляет настройки на новой панели
		add_action( 'woocommerce_order_status_on-hold', array( $this, 'processOrder') );		// Обрабатывает заказ on-hold
		add_action( 'woocommerce_order_status_processing', array( $this, 'processOrder') );		// Обрабатывает заказ on-hold
	}
	
	/**
	 * Добавляет новую панель в настройки WooCommerce
	 * @param mixed $tabs Массив панедей WC
	 */
	public function addSettingsTab( $tabs )
	{
		$tabs[INWCOA] = __( 'Аукцион заказов', INWCOA );
		return $tabs;		
	}
	
	/**
	 * Показывает настройки плагина
	 */
	public function showSettings()
	{

		woocommerce_admin_fields( $this->getSettings() );		
	}
	
	/**
	 * Обновляет настройки плагина
	 */
	public function updateSettings()
	{
		woocommerce_update_options( $this->getSettings() );		
	}
	
	/**
	 * Возвращает массив параметров для страницы настроек WooCommerce
	 * @return mixed 
	 */
	public function getSettings()
	{
	   return array(
			'section_title' => array(
				'name'     => __( 'Плагин аукциона заказов', INWCOA ),
				'type'     => 'title',
				'desc'     => '',
				'id'       => INWCOA . '_section_title'
			),
			'max_notifications' => array(
				'name' => __( 'Количество оповещений каждого из сотрудников', INWCOA ),
				'type' => 'number',
				'css'      => 'width:4em;',
				'default' => 2,
				'desc' => __( 'Укажите, сколько раз оповещать каждого из сотрудников', INWCOA ),
				'id'   => INWCOA . '_max_notifications'
			),
			'repeat_after' => array(
				'name' => __( 'Время повтора оповещения, мин', INWCOA ),
				'type' => 'number',
				'css'      => 'width:4em;',
				'default' => 5,
				'desc' => __( 'Укажите, через которое время, следует повторить оповещение, минимум 1 мин.', INWCOA ),
				'id'   => INWCOA . '_repeat_after'
			),		   
			'notification_text' => array(
				'name' => __( 'Текст оповещения', INWCOA ),
				'type' => 'textarea',
				'default' => __( 'Новый заказ %order_id%. Заказчик: %customer_name%', INWCOA ),
				'desc' => __( 'Допускается использование следующих кодов:<br>%order_id% - номер заказа<br>%customer_name% - имя заказчика', INWCOA ),
				'id'   => INWCOA . '_notification_text'
			),
			'section_end' => array(
				 'type' => 'sectionend',
				 'id' => INWCOA . '_section_end'
			)
		);
	}
	
	/**
	 * Обрабатывает заказ
	 * @param int $orderId Номер заказа 
	 */
	public function processOrder( $orderId )
	{	
		$order = new WC_Order( $orderId );
		if ( ! $order ) return;
		
		//if ( WP_DEBUG ) $order->add_order_note( __( 'Аукцион заказка #', INWCOA ) . $order->ID );
		
		// Максимальное число оповещений пользователям
		$maxNotifications = (int) get_option( INWCOA . '_max_notifications', 1 );
		
		// Число оповещенных исполнителей
		$perfomersSendCount = 0;
		
		// Получим список исполнителей
		$perfomers = $this->plugin->user->getPerformers();
		
		// Получим список оповещенных исполнителей из заказа
		$orderMetaField = 'inwcoa_perfomer_notifications';
		$performerNotifications = array();
		foreach ( $order->meta_data as $orderMeta )
		{
			if ( $orderMeta->__isset( $orderMetaField ) )
			{
				$performerNotifications = $orderMeta->__get( $orderMetaField );
				break;
			}
		}
		
		// Шаблон сообщения
		$template = get_option( INWCOA . '_notification_text', 1 );		
		
		// Пройдем по каждому исполнителю
		foreach( $perfomers as $performer )
		{
			// Посчитаем оповещение
			$performerNotifications[ $performer->ID ]++;
			if ( $performerNotifications[ $performer->ID ] <= $maxNotifications )
			{
				// Оповещаем его!
				foreach( $this->plugin->senders as $sender )
				{
					$sender->send( $performer, $order, $template );
					$perfomersSendCount++;
				}
			}
		}

		// Если никого оповестить не удалось, отправляем оповещение всем управляющим
		if ( $perfomersSendCount == 0 )
		{
			$supervisors = $this->plugin->user->getSupervisors();
			// Пройдем по каждому исполнителю
			foreach( $perfomers as $supervisor )
			{
				// Посчитаем оповещение
				$performerNotifications[ $supervisor->ID ]++;
				if ( $performerNotifications[ $supervisor->ID ] <= $maxNotifications )
				{
					// Оповещаем его!
					foreach( $this->plugin->senders as $sender )
					{
						$sender->send( $supervisor, $order, $template );
					}
				}
			}
		}
		
		// Сохраним счетчик оповещений
		$order->update_meta_data( $orderMetaField, $performerNotifications );
		$order->save();			
	}
	
}