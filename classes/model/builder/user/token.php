<?php defined('SYSPATH') or die ('No direct script access.');
/**
 * Jelly Model Builder Token
 * @package Jelly Auth
 * @author	Israel Canasa
 */
class Model_Builder_User_Token extends Jelly_Builder
{

	/**
	 * Returns the current query limited to 1 and 
	 * executed, if it is a Database::SELECT.
	 *
	 * @param  mixed $key 
	 * @return Jelly_Model
	 */
	public function load($key = NULL)
	{
		$object = parent::load($key);

		if ($object->loaded() AND $object->expires < time())
		{
			$object->delete();
		}

		return $object;
	}
	
}