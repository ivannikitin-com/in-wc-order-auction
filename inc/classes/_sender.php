<?php
/**
 * Базовый класс модуля отправки
 * Непосредственно не создается, от него наследуются все остальные модули отправки 
 */
class INWCOA__Sender extends INWCOA__Base
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