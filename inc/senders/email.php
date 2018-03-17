<?php
/**
 * Модуль отправки по E-mail
 * Реализует отправку оповещений по E-mail. Используется больше в отладочных целях 
 */
class INWCOA_Email extends INWCOA__Sender
{	
	/**
	 * Конструктор 
	 * @param INWCOA_Plugin $plugin Экземпляр основного класса плагина
	 */
	public function __construct( $plugin )
	{
		// Вызов родительского конструктора
		parent::__construct( $plugin );
		
		// Параметры модуля
		$this->title = __( 'Отправка на Email', INWCOA );
		$this->settings = array(
			'email_from_name' => array(
				'name' => __( 'Имя в поле "From"', INWCOA ),
				'type' => 'text',
				'desc' => __( 'От какого имени отправляется почта. Оставьте пустым для отправки по умолчанию', INWCOA ),
				'id'   => $this->settingsId . '_from_name'
			),
			'email_from_email' => array(
				'name' => __( 'E-mail в поле "From"', INWCOA ),
				'type' => 'text',
				'desc' => __( 'От какого имени отправляется почта. Оставьте пустым для отправки по умолчанию', INWCOA ),
				'id'   => $this->settingsId . '_from_email'
			),			
			'email_subject' => array(
				'name' => __( 'Тема сообщения', INWCOA ),
				'type' => 'text',
				'desc' => __( 'Допускается использование тех же кодов что и в шаблоне сообщения', INWCOA ),
				'id'   => $this->settingsId . '_subject'
			),
		);
		
		// Хук ошибки мейлера
		if ( WP_DEBUG ) add_action( 'wp_mail_failed', array( $this, 'errorLog' ), 10, 1 );
	}
	
	/**
	 * Отправляет сообщение указанному пользователю 
	 * @param WP_User	$user		Получаль сообщения
	 * @param WC_Order	$order		Заказ
	 * @param string	$template	Шаблон сообщения
	 * @return bool 
	 */
	public function send( $user, $order, $template )
	{
		// Подготовка
		$to = $user->user_email;
		$subject = $this->prepare( $user, $order, get_option( $this->settingsId . '_subject' ) );
		$message = $this->prepare( $user, $order, $template );
		$from_name = get_option( $this->settingsId . '_from_name', '' );
		$from_email = get_option( $this->settingsId . '_from_email', '' );
		$headers = '';
		
		if ( ! empty( $from_name ) && ! empty( $from_email ) )
		{
			$headers = "From: $from_name <$from_email>\r\n";
		}
		
		// Отправка почты
		return 
			wp_mail( $to, $subject, $message, $headers ) && 	// Отправляем почту 
			$this->orderLog( $order, $to . ': ' . $message );	// и пишем лог в случае успеха
	}
	
	
	/**
	 * Журналирование ошибок почты 
	 * @param WP_Error $error		Ошибка отправки сообщения
	 */
	public function errorLog( $error )
	{
		file_put_contents( $this->plugin->path . 'email.log', date( 'd.m.Y H:i:s' ) . ': ' . $error->get_error_message() . PHP_EOL, FILE_APPEND );
	}
	
	
	
}