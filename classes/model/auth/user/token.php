<?php defined('SYSPATH') or die ('No direct script access.');
/**
 * Sprig Auth User Token Model
 * @package Sprig Auth
 * @author	Paul Banks
 */
class Model_Auth_User_Token extends Jelly
{
 	public static function initialize(Jelly_Meta $meta)
	{
		$meta->fields += array(
			'id' => new Field_Primary,
			'token' => new Field_String(array(
				'unique' => TRUE,
				'rules' => array(
					'max_length' => array(32)
				)
			)),
			'user' => new Field_BelongsTo,
			'user_agent' => new Field_String,
			'created' => new Field_Timestamp(array(
				'auto_now_create' => TRUE,
			)),
			'expires' => new Field_Timestamp,
		);
		
		if (mt_rand(1, 100) === 1)
		{
			// Do garbage collection
			$this->delete_expired();
		}
	}
	
	/**
	 * Handle deletion of expired token on load
	 * @param array $query [optional]
	 * @param int $limit [optional]
	 * @return Sprig
	 */
	public function load($query = NULL, $limit = 1)
	{
		parent::load($query, $limit);
		
		if ($limit === 1 AND $this->loaded() AND $this->expires < time())
		{
			$this->delete();
			$this->_loaded = FALSE;
		}
		
		return $this;
	}
	
	public function create()
	{		
		// Set hash of the user agent
		$this->user_agent = sha1(Request::$user_agent);

		// Create a new token each time the token is saved
		$this->token = $this->create_token();
		
		return parent::save();
	}
	
	public function update()
	{
		// Create a new token each time the token is saved
		$this->token = $this->create_token();
		
		return parent::save();
	}
	
	/**
	 * Deletes all expired tokens.
	 *
	 * @return  void
	 */
	public function delete_expired()
	{
		// Delete all expired tokens
		$this->where('expires', '<', time())->delete();
		return $this;
	}
	
	/**
	 * Finds a new unique token, using a loop to make sure that the token does
	 * not already exist in the database. This could potentially become an
	 * infinite loop, but the chances of that happening are very unlikely.
	 *
	 * @return  string
	 */
	public function create_token()
	{
		while (TRUE)
		{
			// Create a random token
			$token = text::random('alnum', 32);

			// Make sure the token does not already exist
			if( ! $this->count(array('token' => $token)))
			{
				// A unique token has been found
				return $token;
			}
		}
	}
} // End Model_Auth_User_Token