<?php namespace Cviebrock\EloquentTaggable;

use Config;
use Cviebrock\EloquentTaggable\Tag;
use DB;

trait TaggableImpl {

	/**
	 * Tags Relationship
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
	 */
	public function tags()
	{
		return $this->morphToMany('Cviebrock\EloquentTaggable\Tag', 'taggable', 'taggable_taggables')
			->withTimestamps();
	}

	/**
	 * Add all tags provided to this model instance.
	 *
	 * @param $tags
	 * @return $this
	 */
	public function tag($tags)
	{
		$tags = $this->buildTagArray($tags);
		foreach($tags as $tag)
		{
			$this->addOneTag($tag);
		}
		return $this;
	}

	/**
	 * Remove all the tags provided.
	 *
	 * @param $tags
	 * @return $this
	 */
	public function untag($tags)
	{
		$tags = $this->buildTagArray($tags);
		foreach($tags as $tag)
		{
			$this->removeOneTag($tag);
		}
		return $this;
	}

	/**
	 * Reset the tags for this model instance to those provided.
	 *
	 * @param $tags
	 * @return $this
	 */
	public function retag($tags)
	{
		return $this->detag()->tag($tags);
	}


	/**
	 * Remove all tags from this model instance.
	 *
	 * @return $this
	 */
	public function detag()
	{
		$this->removeAllTags();
		return $this;
	}

	/**
	 * Build an array of tags from the provided argument.
	 *
	 * @param $tags
	 * @return array
	 */
	protected function buildTagArray($tags)
	{
		if (is_array($tags)) return $tags;

		if (is_string($tags))
		{
			$delimiters = Config::get('eloquent-taggable.delimiters', ',');
			return preg_split('#['.preg_quote($delimiters,'#').']#', $tags, null, PREG_SPLIT_NO_EMPTY);
		}

		return (array) $tags;
	}

	/**
	 * Add a single tag to this model.
	 *
	 * @param $string
	 */
	protected function addOneTag($string)
	{
		$tag = Tag::findOrCreate($string);

		if (!$this->tags->contains($tag->id))
		{
			$this->tags()->attach($tag);
		}
	}

	/**
	 * Remove a single tag from this model.
	 *
	 * @param $string
	 */
	protected function removeOneTag($string)
	{
		if ($tag = Tag::findByName($string))
		{
			$this->tags()->detach($tag);
		}
	}

	/**
	 * Remove all tags from the owning model.
	 */
	protected function removeAllTags()
	{
		$this->tags()->sync([]);
	}

	/**
	 * Retrieve model tags formatted as a delimited string list.
	 *
	 * @return string
	 */
	public function getTagListAttribute()
	{
		return $this->makeTagList('name');
	}

	/**
	 * Retrieve the normalized tags' names formatted as a delimited string list.
	 *
	 * @return string
	 */
	public function getTagListNormalizedAttribute()
	{
		return $this->makeTagList('normalized');
	}

	/**
	 * Retrieve the tags' names in array format.
	 *
	 * @return mixed
	 */
	public function getTagArrayAttribute()
	{
		return $this->makeTagArray('name');
	}

	/**
	 * Retrieve the tags' normalized names in array format.
	 *
	 * @return mixed
	 */
	public function getTagArrayNormalizedAttribute()
	{
		return $this->makeTagArray('normalized');
	}

	/**
	 * Format tag attribute results as delimited string.
	 *
	 * @param $field
	 * @return string
	 */
	protected function makeTagList($field)
	{
		$glue = substr(Config::get('eloquent-taggable.delimiters', ','), 0, 1);
		$tags = $this->makeTagArray($field);
		return implode($glue, $tags);
	}

	/**
	 * Format tag attribute as an array.
	 *
	 * @param $field
	 * @return mixed
	 */
	protected function makeTagArray($field)
	{
		$return  = $this->tags->lists($field,'id');

		return $return;
	}

	/**
	 * Find all instances of this model which have all the corresponding tags.
	 *
	 * @param $query
	 * @param $tags
	 * @return mixed
	 */
	public function scopeWithAllTags($query, $tags)
	{
		$tags = $this->buildTagArray($tags);
		$normalized = array_map(['\Cviebrock\EloquentTaggable\Tag','normalizeName'], $tags);

		return $query->whereHas('tags', function($q) use ($normalized)
		{
			$q->whereIn('normalized', $normalized);
		}, '=', count($normalized));
	}

	/**
	 * Find all instances of this model which have any of the corresponding tags.
	 *
	 * @param $query
	 * @param array $tags
	 * @return mixed
	 */
	public function scopeWithAnyTags($query, $tags = [])
	{
		$tags = $this->buildTagArray($tags);

		if (empty($tags))
		{
			return $query->has('tags');
		}

		$normalized = array_map(['\Cviebrock\EloquentTaggable\Tag','normalizeName'], $tags);
		return $query->whereHas('tags', function($q) use ($normalized)
		{
			$q->whereIn('normalized', $normalized);
		});
	}

	/**
	 * Delimited string list of all tag names for this model type.
	 *
	 * @return string
	 */
	public static function tagList()
	{
		$glue = static::getListDelimiter();
		return implode($glue, static::allTags('name'));
	}

	/**
	 * All tag names for this given model type formatted as an array
	 *
	 * @return mixed
	 */
	public static function tagArray()
	{
		return static::allTags('name');
	}

	/**
	 * Delimited string list of all normalized tag names for this model type.
	 *
	 * @return string
	 */
	public static function tagListNormalized()
	{
		$glue = static::getListDelimiter();
		return implode($glue, static::allTags('normalized'));
	}

	/**
	 * All normalized tag names for this given model type formatted as an array.
	 *
	 * @return mixed
	 */
	public static function tagArrayNormalized()
	{
		return static::allTags('normalized');
	}

	/**
	 * Get the list delimiter used from string representations of tags.
	 *
	 * @return string
	 */
	protected static function getListDelimiter()
	{
		return substr(Config::get('eloquent-taggable.delimiters', ','), 0, 1);
	}

	/**
	 * Retrieve all the tags for this model type.
	 *
	 * @param $nameAttribute
	 * @return mixed
	 */
	protected static function allTags($nameAttribute)
	{
		return DB::table('taggable_tags')
			->join('taggable_taggables', 'taggable_taggables.tag_id', '=', 'taggable_tags.id')
			->where('taggable_type', '=', get_called_class())
			->distinct()
			->lists('taggable_tags.'.$nameAttribute, 'taggable_tags.id');
	}

}