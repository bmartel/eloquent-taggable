<?php namespace Cviebrock\EloquentTaggable;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Tag extends Eloquent {

	protected $table = 'taggable_tags';

	protected $fillable = [
		'name',
		'normalized'
	];

	/**
	 * Taggable Relationship.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\MorphTo
	 */
	public function taggable()
	{
		return $this->morphTo();
	}

	/**
	 * Mutator to normalize name attributes stored.
	 *
	 * @param $value
	 */
	public function setNameAttribute($value)
	{
		$value = trim($value);
		$this->attributes['name'] = $value;
		$this->attributes['normalized'] = static::normalizeName($value);
	}

	/**
	 * Normalize name attributes.
	 *
	 * @param $value
	 * @return mixed
	 */
	public static function normalizeName($value)
	{
		$normalizer = \Config::get('eloquent-taggable.normalizer');
		return call_user_func($normalizer, $value);
	}

	/**
	 * Find a tag by a normalized name attribute.
	 *
	 * @param $name
	 * @return mixed
	 */
	public static function findByName($name)
	{
		$normalized = static::normalizeName($name);
		return static::where('normalized',$normalized)->first();
	}

	/**
	 * @return mixed
	 */
	public function __toString()
	{
		return $this->name;
	}


}