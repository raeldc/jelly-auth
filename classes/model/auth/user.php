<?php defined('SYSPATH') or die ('No direct script access.');
/**
 * Jelly Auth User Model
 * @package Jelly Auth
 * @author	Israel Canasa
 */
class Model_Auth_User extends Jelly
{
	public static function initialize(Jelly_Meta $meta)
    {
		$meta->sorting = array('username' => 'ASC');
		$meta->name_key = 'username';

		$meta->fields += array(
			'id' => new Field_Primary,
			'username' => new Field_String(array(
				'unique' => TRUE,
				'rules' => array(
						'not_empty' => TRUE,
						'max_length' => 32,
						'min_length' => 3,
						'regex' => '/^[\pL_.-]+$/ui'
					)
				)),
			'password' => new Field_Password(array(
				'hash_with' => array(Auth::instance(), 'hash_password'),
				'rules' => array(
					'not_empty' => TRUE,
					'max_length' => 50,
					'min_length' => 6
				)
			)),
			'password_confirm' => new Field_Password(array(
				'in_db' => FALSE,
				'callbacks' => array(
					//'matches' => array($this, '_check_password_matches')
				),
				'rules' => array(
					'not_empty' => TRUE,
					'max_length' => 50,
					'min_length' => 6
				)
			)),
			'email' => new Field_Email(array(
				'unique' => TRUE
			)),
			'logins' => new Field_Integer,
			'last_login' => new Field_Timestamp,
			'tokens' => new Field_HasMany(array(
				'foreign' => array(
					'model' => 'User_Token'
				)
			)),
			'roles' => new Field_ManyToMany
		);
    }

	/**
	 * Validate callback wrapper for checking password match
	 * @param Validate $array
	 * @param string   $field
	 * @return void
	 */
	public function _check_password_matches(Validate $array, $field)
	{
		$auth = Auth::instance();
		
		$salt = $auth->find_salt($array['password']);		
		echo Kohana::debug($array['password']);
		die();
		if ($array['password'] !== $auth->hash_password($array[$field], $salt))
		{
			// Re-use the error messge from the 'matches' rule in Validate
			$array->error($field, 'matches', array('param1' => 'password'));
		}
	}
	
	/**
	 * Check if user has a particular role
	 * @param mixed $role 	Role to test for, can be Model_Role object, string role name of integer role id
	 * @return bool			Whether or not the user has the requested role
	 */
	public function has_role($role)
	{
		// Check what sort of argument we have been passed
		if ($role instanceof Model_Role)
		{
			$key = 'id';
			$val = $role->id;
		}
		elseif (is_string($role))
		{
			$key = 'name';
			$val = $role;
		}
		else
		{
			$key = 'id';
			$val = (int) $role;
		}
		
		foreach ($this->roles as $user_role)
		{	
			if ($user_role->{$key} === $val)
			{
				return TRUE;
			}
		}
		
		return FALSE;
	}
	
} // End Model_Auth_User