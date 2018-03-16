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
			'email_from' => array(
				'name' => __( 'Поле "From"', INWCOA ),
				'type' => 'text',
				'desc' => __( 'От чъего имени отправляется почта. Оставьте пустым для отправки по умолчанию', INWCOA ),
				'id'   => $this->settingsId . '_from'
			),
			'email_subject' => array(
				'name' => __( 'Тема сообщения', INWCOA ),
				'type' => 'text',
				'desc' => __( 'Допускается использование тех же кодов что и в шаблоне сообщения', INWCOA ),
				'id'   => $this->settingsId . '_subject'
			),
		);
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
		// Подготовка текста		
		$subject = $this->prepare( $user, $order, get_option( $this->settingsId . '_subject' ) );
		$message = $this->prepare( $user, $order, $template );
		
		// Отправка
		return wp_mail( $user->user_email, $subject, $message );
	}	
	
}