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

    // Path to the default config
    $defaultConfigPath = __DIR__ . '/config/config.php';

    // Load the default config
    $config = $this->app['files']->getRequire($defaultConfigPath);


    // Set each of the items like ->package() previously did
    $this->app['config']->set('cviebrock::eloquent-taggable', $config);
    $this->app['view']->addNamespace('eloquent-taggable', __DIR__ . '/views');
    $this->app['translator']->addNamespace('eloquent-taggable', __DIR__ . '/lang');
  }
}
