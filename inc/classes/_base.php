<?php
/**
 * Базовый класс компонентов плгина
 * Непосредственно не создается, от него наследуются все остальные классы 
 */
class INWCOA__Base
{
	/**
	 * Ссылка на экземпляр основного класса плагина
	 * @var INWCOA_Plugin
	 */
	protected $plugin;
	
	/**
	 * Конструктор 
	 * @param INWCOA_Plugin $plugin Экземпляр основного класса плагина
	 */
	public function __construct( $plugin )
	{
		// Сохраним ссылку на основной объект плагина
		$this->plugin = $plugin;
	}
}