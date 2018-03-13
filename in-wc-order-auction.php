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

define( 'INWCOA', 'in-wc-order-auction' );	// Константа текстового домена плагина

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
	 * Массив модулей отправки
	 */
	public $senders;
	
	/**
	 * Инициализация компонентов плагина
	 */
	public function init()
	{
		// Загрузка модулей отправки
		foreach ( glob( $this->path . 'inc/senders/*.php' ) as $fileName ) 
		{
			$className = 'INWCOA_' . ucfirst( basename( $fileName, '.php' ) );
			require_once( $fileName );
			$this->senders[] = new $className( $this );
		}
		
	}	
	
}

// Запуск плагина
new INWCOA_Plugin();