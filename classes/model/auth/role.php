<?php defined('SYSPATH') or die ('No direct script access.');
/**
 * Jelly Auth Role Model
 * @package Jelly Auth
 * @author 	Israel Canasa
 */
class Model_Auth_Role extends Jelly 
{
	public static function initialize(Jelly_Meta $meta)
	{
		$meta->primary_key = 'name';
		$meta->fields += array(
			'id' => new Field_Primary,
			'name' => new Field_String(array(
				'unique' => TRUE,
				'rules' => array(
					'max_length' => 32,
					'not_empty' => TRUE
				)
			)),
			'description' => new Field_Text,
			'users' => new Field_ManyToMany
		);
	}
} // End Model_Auth_Role