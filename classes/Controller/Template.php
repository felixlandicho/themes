<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * Abstract controller class for automatic templating.
 *
 * @package    Themes
 * @category   Controller
 * @author     Felix Landicho
 * @copyright  (c) 2013 Felix Landicho
 */
class Controller_Template extends Controller {

	/**
	 * @var  View  page template
	 */
	public $template = 'template';

	/**
	 * @var  boolean  auto render template
	 **/
	public $auto_render = TRUE;

	/**
	 * @var string the page title
	 */
	public $title = NULL;

	/**
	 * @var array stylesheets
	 */
	public $stylesheets = array();

	/**
	 * @var array javascripts
	 */
	public $javascripts = array();

	/**
	 * Hold the response format for this request
	 * @var string
	 */
	protected $_response_format;

	/**
	 * List all supported formats for this controller
	 * (accept-type => path to format template)
	 * @var array
	 *
	 * Taken from GleezCMS
	 */
	protected $_accept_formats = array(
		'text/html'             => 'html',
		'application/xhtml+xml' => 'xhtml',
		'application/xml'       => 'xml',
		'application/json'      => 'json',
		'application/csv'       => 'csv',
		'text/plain'            => 'php',
		'text/javascript'       => 'jsonp',
		'*/*'                   => 'xhtml' //ie7 ie8
	);

	/**
	 * Loads the template [View] object.
	 */
	public function before()
	{
		parent::before();

		if ($this->request->action() === 'media')
		{
			// Do not template media files
			$this->auto_render = FALSE;
		}

		/**
		 * Get desired response formats
		 *
		 * Idea taken from GleezCMS
		 */
		$accept_types = Request::accept_type();
		$extract_accept_types = Arr::extract($accept_types, array_keys($this->_accept_formats));
		$filter_accept_types = array_filter($extract_accept_types);
		$this->_response_format = key(array_filter($filter_accept_types));

		if ($this->auto_render === TRUE)
		{
			// Load the template
			$this->template = View::factory($this->template);

			$this->template
				->set('sitename',    Themes::$_config->sitename)
				->set('description', Themes::$_config->description)
				->set('title',       $this->title);

			// Set default headers
			$this->set_headers();

			/**
			 * Make your view template available to all your other views
			 * so easily you could access template variables
			 */
			View::bind_global('template', $this->template);
			View::bind_global('sitename', $this->template->sitename);
		}
	}

	/**
	 * Assigns the template [View] as the request response.
	 */
	public function after()
	{
		if ($this->auto_render === TRUE)
		{
			$this->set_title();
			$this->set_assets(Route::get('media'));

			$this->template->set('content', $this->response->body());

			// Check if config html compress is TRUE

			if (Themes::$_config->templates['html_compress'])
			{
				// Assign the template as the request response and render it
				$this->template = preg_replace(array('/\s\s+/', '/[\t\n]/'), '', $this->template->render());
			}

			Kohana::$log->add(LOG::INFO, 'Executing Controller :template',
				array(
					':template' => $this->response->body($this->template),
				)
			);

			$this->response->body($this->template);
		}

		parent::after();
	}

	/**
	 * Media action for serving static files.
	 */
	public function action_media()
	{
		// Get the folder path from the request
		$folder = $this->request->param('folder');

		// Get the file path from the request
		$file = $this->request->param('file');

		// Find the file extension
		$ext = pathinfo($file, PATHINFO_EXTENSION);

		// Remove the extension from the filename
		$file = substr($file, 0, -(strlen($ext) + 1));

		if ($file_name = Kohana::find_file($folder, $file, $ext))
		{
			// Check if the browser sent an "if-none-match: <etag>" header, and tell if the file hasn't changed
			$this->check_cache(sha1($this->request->uri()).filemtime($file_name), $this->request);

			// Send the file content as the response
			$this->response->body(file_get_contents($file_name));

			// Set the proper headers to allow caching
			$this->response->headers('content-type', File::mime_by_ext($ext));
			$this->response->headers('last-modified', date('r', filemtime($file_name)));
			$this->response->headers('cache-control', 'public, max-age=2592000'); //this is ignored by check_cache

			if (Themes::$_config->media['cache'])
			{
				// Save the contents to the public directory for future requests
				$public_path = Themes::$_config->media['public_dir'].'/'.$file.'.'.$ext;

				$directory = dirname($public_path);

				if ( ! is_dir($directory))
				{
					// Recursively create the directories needed for the file
					mkdir($directory.DIRECTORY_SEPARATOR, 0777, TRUE);

					// Set permissions (must be manually set to fix umask issues)
					chmod($directory.DIRECTORY_SEPARATOR, 0777);
				}

				file_put_contents($public_path, $this->response->body());
			}
		}
		else
		{
			Kohana::$log->add(LOG::ERROR, 'Media action error while loading file: `:file`', array(
				':file' => $file
			));

			// Return a 404 status
			$this->response->status(404);
		}
	}

	/**
	 * Is frontpage?
	 *
	 * @return boolean
	 *
	 * @uses  Request::uri
	 */
	public function is_frontpage()
	{
		$uri = preg_replace("#(/p\d+)+$#uD", '', rtrim($this->request->uri(), '/'));
		return (empty($uri) OR ($uri === Themes::$_config->frontpage));
	}

	/**
	 * Set the page title.
	 *
	 * @access	protected
	 * @return	void
	 */
	protected function set_title()
	{
		if ($this->title)
		{
			$title = array(strip_tags($this->title), $this->template->sitename);
		}
		else
		{
			if ($this->is_frontpage())
			{
				$title = array($this->template->sitename);
				if ($this->template->description)
				{
					$title[] = $this->template->description;
				}
			}
			else
			{
				$title = array(ucwords($this->request->controller()));
			}
		}

		$this->template->title = implode(Themes::$_config->separator, $title);
	}

	/**
	 * Set all assets
	 */
	protected function set_assets($media)
	{
		$themes_name = Themes::$template;
		$assets_type = array('stylesheets', 'javascripts');

		$stylesheets = array(
			$media->uri(array('file' => 'css/bootstrap.min.css'))  => 'screen',
			$media->uri(array('file' => 'css/application.css')) => 'screen',
			$media->uri(array('file' => "css/{$themes_name}.css"))  => 'screen',
		);

		$javascripts = array(
			$media->uri(array('file' => 'js/jquery.min.js')),
			$media->uri(array('file' => 'js/bootstrap.min.js')),
			$media->uri(array('file' => 'js/application.js')),
		);

		foreach ($assets_type as $type)
		{
			if (count($this->{$type}))
			{
				foreach ($this->{$type} as $key => $value)
				{
					${$type} = array_slice(${$type}, 0, 1, TRUE) + array($key => $value) + array_slice(${$type}, 1, count(${$type}) - 1, TRUE);
				}
			}

			// Add assets
			$this->template->{$type} = ${$type};
		}
	}

	/**
	 * Set the server headers.
	 *
	 * @access	protected
	 * @param	array	an associative array of server headers
	 * @return	void
	 */
	protected function set_headers()
	{
		$header = Themes::$_config->templates['headers'];
		$xmlrpc = Themes::$_config->templates['xmlrpc'];

		// Set header content-type to response format with utf-8
		$header['Content-Type'] = $this->_response_format . '; charset=' . Kohana::$charset;

		if ( ! empty($xmlrpc))
		{
			$header['X-Pingback'] = URL::site($xmlrpc, TRUE);
		}

		if (is_array($header) AND ! empty($header))
		{
			if ( ! Kohana::$expose)
			{
				if (isset($header['X-Powered-By']))
					unset($header['X-Powered-By']);

				header_remove('X-Powered-By');
			}

			$this->response->headers($header);
		}
	}

} // End Controller_Template