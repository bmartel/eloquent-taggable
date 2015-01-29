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
	public function boot() {

		$this->handleConfigs();
		$this->handleMigrations();
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register() {
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides() {

		return [];
	}

	private function handleConfigs() {

		$configPath = __DIR__ . '/config/taggable.php';

		// Allow this package config to be published to the application config directory.
		$this->publishes([$configPath => config_path('taggable.php')]);

		// Only require minimum configs to be set in application. Missing values will fall back to
		// those set in the package config.
		$this->mergeConfigFrom($configPath, 'taggable');
	}

	private function handleMigrations() {

		$this->publishes([__DIR__ . '/migrations' => base_path('database/migrations')]);
	}

}
