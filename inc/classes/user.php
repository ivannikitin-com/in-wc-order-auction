<?php
/**
 * Класс обеспечивает работу с пользователями
 */
class INWCOA_User extends INWCOA__Base
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
	
	/**
	 * Метод регистрирует роли и разрешения, необходимые плагину
	 * метод статичный, потому что вызывается по хуку активации плагина
	 */
	static function registerRoles()
	{
		
	}
	
}