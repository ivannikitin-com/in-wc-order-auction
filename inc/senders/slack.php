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
	}
}