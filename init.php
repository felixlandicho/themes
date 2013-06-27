<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * The directory in which the themes directory are located.
 * This directory should contain all the themes,
 * and the resources you included in your layout of the application.
 *
 * This path can be absolute or relative to this file.
 */
Themes::$_config = Kohana::$config->load('themes');

// Check if the directory exist
if ( ! is_dir(Themes::$_config->directory))
{
	throw new Kohana_Exception(':folder folder does not exist', array(
        ':folder' => Themes::$_config->directory,
    ));
}

// Define the template path for easy access
define('TPLPATH', realpath(Themes::$_config->directory).DIRECTORY_SEPARATOR);

/**
 * Load the active theme(s)
 */
Themes::load();

/**
 * Set the routes. Each route must have a minimum of a name, a URI and a set of
 * defaults for the URI.
 *
 * Static file serving (CSS, JS, IMAGES)
 */
Route::set('media', '<folder>/<file>',
	array(
		'file' => '.+',
		'folder' => 'media|static',
	))
	->defaults(array(
		'controller' => 'Template',
		'action'     => 'media',
		'file'       => NULL,
		'folder'     => 'media',
	));