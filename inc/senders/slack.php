<?php
/**
 * Модуль отправки черех Slack
 * Реализует отправку оповещений через Slack 
 */
class INWCOA_Slack extends INWCOA__Sender
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
		$this->title = __( 'Оповещение через Slack', INWCOA );
		$this->settings = array(
			'slack_service_url' => array(
				'name' => __( 'Service URL', INWCOA ),
				'type' => 'text',
				'desc' => __( 'Укажите URL веб-хука Slack. Подробности см. <a href="https://my.slack.com/services/new/incoming-webhook/" target="_blank">здесь</a>', INWCOA ),
				'id'   => $this->settingsId . '_service_url'
			),
			'slack_channel' => array(
				'name' => __( 'Канал Slack', INWCOA ),
				'type' => 'text',
				'desc' => __( 'Укажите канал Slack, в который необходимо выполнять передачу', INWCOA ),
				'id'   => $this->settingsId . '_channel'
			),
		);
	}
}