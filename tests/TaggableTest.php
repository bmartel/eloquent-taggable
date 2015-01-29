<?php

use Orchestra\Testbench\TestCase;


class TaggableTest extends TestCase {

	/**
	* Setup the test environment.
	*/
	public function setUp()
	{
		parent::setUp();

		// Create an artisan object for calling migrations
		$artisan = $this->app->make('Illuminate\Contracts\Console\Kernel');

		$this->app['path.database'] = __DIR__;

		// Call migrations specific to our tests, e.g. to seed the db
		$artisan->call('migrate', [
			'--database' => 'testbench',
			'--path'     => './database/migrations',
		]);

		// Call migrations for the package
		$artisan->call('migrate', [
			'--database' => 'testbench',
			'--path'     => '../src/migrations',
		]);

	}

	/**
	* Get base path.
	*
	* @return string
	*/
	protected function getBasePath()
	{
			// reset base path to point to our package's src directory
			return __DIR__.'/../src';
	}

	/**
	* Define environment setup.
	*
	* @param  Illuminate\Foundation\Application    $app
	* @return void
	*/
	protected function getEnvironmentSetUp($app)
	{
		// set up database configuration
		$app['config']->set('database.default', 'testbench');
		$app['config']->set('database.connections.testbench', [
				'driver'   => 'sqlite',
				'database' => ':memory:',
				'prefix'   => '',
		]);

	}

	/**
	* Get Sluggable package providers.
	*
	* @return array
	*/
	protected function getPackageProviders()
	{
		return ['Cviebrock\EloquentTaggable\TaggableServiceProvider'];
	}


	protected function makePost()
	{
		return Post::create([
			'title' => \Str::random(10)
		]);
	}

	public function testCanTagModelWithTokenDelimitedItemList()
	{
		$post = $this->makePost();

		$post->tag('Apple,Banana,Cherry');

		$this->assertEquals(['Apple', 'Banana', 'Cherry'], $post->tagArray);
	}

	public function testCanTagModelWithArrayOfItems()
	{
		$post = $this->makePost();

		$post->tag(['Apple','Banana','Cherry']);

		$this->assertEquals(['Apple', 'Banana', 'Cherry'], $post->tagArray);
	}

	public function testTaggingModelIsAdditive()
	{
		$post = $this->makePost();

		$post->tag('Apple,Banana,Cherry');
		$post->tag('Durian');

		$this->assertCount(['Apple', 'Banana', 'Cherry', 'Durian'], $post->tagArray);
	}

	public function testCanUntagValueFromModel()
	{
		$post = $this->makePost();

		$post->tag('Apple,Banana,Cherry');
		$post->untag('Banana');

		$this->assertEquals(['Apple', 'Cherry'], $post->tagArray);
	}

	public function testCanRemoveAllModelsTags()
	{
		$post = $this->makePost();

		$post->tag('Apple,Banana,Cherry');
		$post->detag();

		$this->assertCount(0, $post->tags);
	}

	public function testCanRetagModelWithDifferentTags()
	{
		$post = $this->makePost();

		$post->tag('Apple,Banana,Cherry');
		$post->retag('Etrog,Fig,Grape');

		$this->assertEquals(['Etrog', 'Fig', 'Grape'], $post->tagArray);
	}

	public function testCanRetrieveTagsAsTokenDelimitedString()
	{
		$post = $this->makePost();

		$post->tag(['Apple','Banana','Cherry']);

		$this->assertEquals('Apple,Banana,Cherry', $post->tagList);
	}

	public function testTagNamesAreNormalized()
	{
		$post = $this->makePost();

		$post->tag('Apple');
		$post->tag('apple');
		$post->tag('APPLE');

		$this->assertCount(1, $post->tags);
		$this->assertEquals(['Apple'], $post->tagArray);
	}
}
