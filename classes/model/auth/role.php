<?php defined('SYSPATH') or die ('No direct script access.');
/**
 * Jelly Auth Role Model
 * @package Jelly Auth
 * @author 	Israel Canasa
 */
class Model_Auth_Role extends Jelly_Model
{
	public static function initialize(Jelly_Meta $meta)
	{
		$meta->name_key('name')
			->fields(array(
			'id' => new Field_Primary,
			'name' => new Field_String(array(
				'unique' => TRUE,
				'rules' => array(
					'max_length' => array(32),
					'not_empty' => NULL
				)
			)),
			'description' => new Field_Text,
			'users' => new Field_ManyToMany
		));
	}
} // End Model_Auth_Role