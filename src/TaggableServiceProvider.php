<?php namespace Cviebrock\EloquentTaggable;


use Illuminate\Support\ServiceProvider;

class TaggableServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->registerConfig();
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

	  /**
	 * Register the config files
	 *
	 * @return void
	 */
  protected function registerConfig()
  {
  	$userConfigFile    = app()->configPath().'/cviebrock/eloquent-taggable.php';

    // Path to the default config
    $packageConfigFile = __DIR__ . '/config/config.php';

    // Load the default config
   	$config = $this->app['files']->getRequire($packageConfigFile);

    if (file_exists($userConfigFile)) {
	    $userConfig = $this->app['files']->getRequire($userConfigFile);
	    $config     = array_replace_recursive($config, $userConfig);
    }

    // Set each of the items like ->package() previously did
    $this->app['config']->set('cviebrock::eloquent-taggable', $config);
  }
}
