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
		$roles = array(
				'performer'	=> array(								// Роль Исполнитель
					'title' 	=>  __( 'Исполнитель', INWCOA ),	// Название роли
					'baseRole'	=> 'shop_manager',					// Базовая роль
					'caps'		=> array(),							// Дополнительные разрешения (на будущее)
				),
				'supervisor' => array(								// Роль Распорядитель (оператор)
					'title' 	=>  __( 'Управляющий', INWCOA ),	// Название роли
					'baseRole'	=> 'shop_manager',					// Базовая роль
					'caps'		=> array(),							// Дополнительные разрешения (на будущее)
				),	
			);		
		
		foreach ( $roles as $role => $props )
		{
			// Читаем базовую роль
			$baseRole = get_role( $props['baseRole'] );
			
			// Дополняем новыми разрешениями
			$caps = array_merge( $baseRole->capabilities, $props['caps'] );
			
			// Регистрация новой роли пользователя с разрешениями
			add_role( $role, $props['title'] );
			
			// Установка дополнительных разрешений для роли
			$currentRole = get_role( $role );				
			foreach ( $props[ 'caps' ] as $cap => $hasCap )
			{
				if ( $hasCap )
					$currentRole->add_cap( $cap );		
				else
					$currentRole->remove_cap( $cap );
			}
		}		
	}
	
	/**
	 * Возвращает список исполнителей 
	 * @return mixed WP_User
	 */
	public function getPerformers()
	{
		return get_users( array( 'role' => 'performer' ) );
	}
	
	/**
	 * Возвращает список распорядителей 
	 * @return mixed WP_User
	 */
	public function getSupervisors()
	{
		return get_users( array( 'role' => 'supervisor' ) );
	}
	
	
}