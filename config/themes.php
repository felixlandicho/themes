<?php defined('SYSPATH') OR die('No direct script access.');

return array(
	'directory' => DOCROOT.'templates',
	'templates' => array(
		'front'         => 'default',
		'headers'       => array(
			'X-Content-Type-Options' => 'nosniff',
			'X-Frame-Options'        => 'DENY',
			'X-XSS-Protection'       => '1; mode=block',
		),
		'xmlrpc'        => '',
		'html_compress' => FALSE,
	),

	'sitename'  => 'Kohana Themes',
	'description' => 'Simple Kohana templating module',
	'frontpage' => 'welcome',
	'separator' => ' - ',

	'media'       => array(
		'folder'   => DOCROOT.'media',
		'cache'    => Kohana::$environment === Kohana::PRODUCTION,
		'compress' => Kohana::$environment === Kohana::PRODUCTION,
	),
);