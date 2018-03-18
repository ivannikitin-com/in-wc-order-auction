<?php
/*
Plugin Name: Аукцион заказов WooCommerce среди исполнителей
Plugin URI: https://github.com/ivannikitin-com/in-wc-order-auctionй
Description: Данный плагин реализует отправку заказа нескольким исполнителям по принципу "кто первый взял" и контроль за его исполнением
Version: 1.0
Author: Иван Никитин
Author URI: https://ivannikitin.com
Textdomain: in-wc-order-auction

Copyright 2018  Ivan Nikitin  (email: ivan.g.nikitin@gmail.com)
This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

define( 'INWCOA', 'in_wc_order_auction' );	// Константа текстового домена и других параметов плагина 

// Основной класс плагина
class INWCOA_Plugin
{
	/**
	 * Версия
	 */
	public $version;
	
	/**
	 * Путь к папке плагина
	 */
	public $path;
	
	/**
	 * URL к папке плагина
	 */
	public $url;
	
	/**
	 * Конструктор плагина
	 */
	public function __construct()
	{
		// Инициализация свойств
		$this->version = '1.0';
		$this->path = plugin_dir_path( __FILE__ );
		$this->url = plugin_dir_url( __FILE__ );
		
		// Автозагрузка классов
		spl_autoload_register( array( $this, 'autoload' ) );

		// Активация плагина
		register_activation_hook( __FILE__, 'INWCOA_User::registerRoles' );		
		
		// Хуки
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
		add_action( 'init', array( $this, 'init' ) );
	}
	
    /**
     * Автозагрузка лассов по требованию
     *
     * @param string $class Требуемый класс
     */
    function autoload( $class ) 
	{
        $classPrefix = 'INWCOA_';
	
		// Если это не наш класс, ничего не делаем...
		if ( strpos( $class, $classPrefix ) === false ) 
			return;
		
		$fileName   = $this->path . 'inc/classes/' . strtolower( str_replace( $classPrefix, '', $class ) ) . '.php';
		if ( file_exists( $fileName ) ) 
		{
			require_once $fileName;
		}
    }
	
	/**
	 * Плагины загружены
	 */
	public function plugins_loaded()
	{
		// Локализация
		load_plugin_textdomain( INWCOA, false, basename( dirname( __FILE__ ) ) . '/lang' );
	}

	/**
	 * Объект интеграции с WooOcommerce
	 * @var INWCOA_WooCommerce
	 */
	public $wc;
	
	/**
	 * Объект работы с пользователями
	 * @var INWCOA_User
	 */
	public $user;
	
	/**
	 * Объект работы с заказами
	 * @var INWCOA_User
	 */
	public $order;	
	
	/**
	 * Массив модулей отправки
	 * @var mixed INWCOA__Sender
	 */
	public $senders;
	
	/**
	 * Инициализация компонентов плагина
	 */
	public function init()
	{
		// Проверка наличия WC		
		if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) 
		{
			add_action( 'admin_notices', array( $this, 'showNoticeNoWC' ) );
			return;
		}		
		
		// Инициализация объекта интеграции с WooCommerce
		$this->wc = new INWCOA_WooCommerce( $this );
		
		// Инициализация объекта работы с пользователями
		$this->user = new INWCOA_User( $this );
		
		// Инициализация объекта работы с заказами
		$this->order = new INWCOA_Order( $this );		
		
		// Загрузка модулей отправки
		foreach ( glob( $this->path . 'inc/senders/*.php' ) as $fileName ) 
		{
			$className = 'INWCOA_' . ucfirst( basename( $fileName, '.php' ) );
			require_once( $fileName );
			$this->senders[] = new $className( $this );
		}
		
	}
	
	/**
	 * Предупреждение об отсутствии WooCommerce
	 */
	public function showNoticeNoWC()
	{ ?>
    <div class="notice notice-warning no-woocommerce">
        <p><?php _e( 'Для работы плагина "Аукцион заказов WooCommerce" требуется установить и активировать плагин WooCommerce.', INWCOA ); ?></p>
        <p><?php _e( 'В настоящий момент все функции плагина деактивированы.', INWCOA ); ?></p>
    </div>		
<?php }
	
}

// Запуск плагина
new INWCOA_Plugin();