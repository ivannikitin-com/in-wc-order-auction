<?php
/**
 * Базовый класс модуля отправки
 * Непосредственно не создается, от него наследуются все остальные модули отправки 
 */
class INWCOA__Sender extends INWCOA__Base
{	
	
	/**
	 * Название модуля 
	 * @var string
	 */	
	public $title;
	
	/**
	 * Описание модуля 
	 * @var string
	 */	
	public $description;
	
	/**
	 * Массив с параметрами модуля 
	 * @var string
	 */	
	protected $settings;
	
	/**
	 * Параметр ID для установок WC 
	 * @var string
	 */	
	protected $settingsId;	
	
	/**
	 * Конструктор 
	 * @param INWCOA_Plugin $plugin Экземпляр основного класса плагина
	 */
	public function __construct( $plugin )
	{
		// Вызов родительского конструктора
		parent::__construct( $plugin );
		
		// Инициализация свойств
		$this->title = '';
		$this->description = '';
		$this->settings = array();
		$this->settingsId = strtolower( get_class( $this ) );
		
		// Хуки на вызов настроек
		add_action( 'woocommerce_settings_tabs_'. INWCOA, array( $this, 'showSettings') );
		add_action( 'woocommerce_update_options_'. INWCOA, array( $this, 'updateSettings') );
	}
	
	/**
	 * Показывает настройки модуля
	 */
	public function showSettings()
	{

		woocommerce_admin_fields( $this->getSettings() );		
	}	
	
	/**
	 * Обновляет настройки модуля
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
		return array_merge(
			// Заголовок модуля
			array( 
				'section_title' => array(
					'name'     => $this->title,
					'type'     => 'title',
					'desc'     => $this->description,
					'id'       => $this->settingsId . '_title'
				),
				'section_enabled' => array(
					'name'     => __( 'Модуль включен', INWCOA ),
					'type'     => 'checkbox',
					'desc'     => __( 'Отметьте или снимите эту отметку для включение или выключения оповещений через этот модуль', INWCOA ),
					'id'       => $this->settingsId . '_enabled'
				),				
			),
			
			// Параметры модуля
			$this->settings,
			
			// Завершение параметров модуля
			array( 	'section_end' => array(
					 'type' => 'sectionend',
					 'id' => $this->settingsId . '_section_end'
				)
			)
		);		
	}
	
}