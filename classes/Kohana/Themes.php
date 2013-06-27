<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * Themes helper for adding content to views.
 * This is the API for handling Themes.
 *
 * Note: by design, this class does not do any permission checking.
 *
 * @package    Themes
 * @category   Helpers
 * @author     Felix Landicho
 * @copyright  (c) 2013 Felix Landicho
 */
class Kohana_Themes {
	/**
	 * @var  string  site theme name
	 */
	public static $template = 'default';

	/**
	 * @var  string  theme config
	 */
	public static $_config;

	/**
	 * Load the active theme. This is called at bootstrap time.
	 * We will only ever have one theme active for any given request.
	 */
	public static function load()
	{
		// Get the module lists
		$modules = Kohana::modules();

		// Set the dir and name
		Themes::$template = Themes::$_config->templates['front'];

		// Create a array to add on path
		$path = array(
			Themes::$template => TPLPATH.Themes::$template
		);

		// Append theme path to the module lists
		Kohana::modules($path + $modules);

		// Clean up the vars
		unset($modules, $array);
	}

}