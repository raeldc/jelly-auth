<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Jelly Auth driver.
 *
 * @package    Jelly Auth
 * @author     Israel Canasa
 */
class Auth_Jelly extends Auth {

	/**
	 * Checks if a session is active.
	 *
	 * @param   string   role name
	 * @param   array    collection of role names
	 * @return  boolean
	 */
	public function logged_in($role = NULL)
	{
		$status = FALSE;

		// Get the user from the session
		$user = $this->session->get($this->config['session_key']);
				
		if ( ! is_object($user))
		{
			// Attempt auto login
			if ($this->auto_login())
			{
				// Success, get the user back out of the session
				$user = $this->session->get($this->config['session_key']);
			}
		}
		
		//check if the user still exists
		$exists = FALSE;
		
		if(is_object($user))
		{
			$exists = Jelly::factory('user', $user->id)->count();
			
			if ( ! $exists) 
			{
				//destroy all tokens of this user
				$this->logout(TRUE, TRUE);
				return $status;
			}
		}
				
		if (is_object($user) AND $user instanceof Model_User AND $user->loaded() AND $exists)
		{
			// Everything is okay so far
			$status = TRUE;

			if ( ! empty($role))
			{

				// If role is an array
				if (is_array($role))
				{
					// Check each role
					foreach ($role as $role_iteration)
					{
						// If the user doesn't have the role
						if( ! $user->has_role($role_iteration))
						{
							// Set the status false and get outta here
							$status = FALSE;
							break;
						}
					}
				}
				else
				{
					// Check that the user has the given role
					$status = $user->has_role($role);
				}
			}
		}

		return $status;
	}

	/**
	 * Logs a user in.
	 *
	 * @param   string   username
	 * @param   string   password
	 * @param   boolean  enable auto-login
	 * @return  boolean
	 */
	public function _login($user, $password, $remember)
	{
		// Make sure we have a user object
		$user = $this->_get_object($user);

		// If the passwords match, perform a login
		if ($user->has_role('login') AND $user->password === $password)
		{
			if ($remember === TRUE)
			{
				// Create a new autologin token
				$token = Jelly::factory('user_token');

				// Set token data
				$token->user = $user->id;
				$token->expires = time() + $this->config['lifetime'];

				$token->create();

				// Set the autologin cookie
				cookie::set('authautologin', $token->token, $this->config['lifetime']);
			}

			// Finish the login
			$this->complete_login($user);

			return TRUE;
		}

		// Login failed
		return FALSE;
	}

	/**
	 * Forces a user to be logged in, without specifying a password.
	 *
	 * @param   mixed    username
	 * @return  boolean
	 */
	public function force_login($user)
	{
		// Make sure we have a user object
		$user = $this->_get_object($user);

		// Mark the session as forced, to prevent users from changing account information
		$_SESSION['auth_forced'] = TRUE;

		// Run the standard completion
		$this->complete_login($user);
	}

	/**
	 * Logs a user in, based on the authautologin cookie.
	 *
	 * @return  boolean
	 */
	public function auto_login()
	{
		if ($token = cookie::get('authautologin'))
		{
			// Load the token and user
			$token = Jelly::factory('user_token', array('token' => $token))->load();			
			
			if ($token->loaded() AND $token->user->load() AND $token->user->loaded())
			{
				if ($token->user_agent === sha1(Request::$user_agent))
				{
					// Save the token to create a new unique token
					$token->update();

					// Set the new token
					cookie::set('authautologin', $token->token, $token->expires - time());

					// Complete the login with the found data
					$this->complete_login($token->user);

					// Automatic login was successful
					return TRUE;
				}

				// Token is invalid
				$token->delete();
			}
		}

		return FALSE;
	}

	/**
	 * Log a user out and remove any auto-login cookies.
	 *
	 * @param   boolean  completely destroy the session
	 * @param	boolean  remove all tokens for user
	 * @return  boolean
	 */
	public function logout($destroy = FALSE, $logout_all = FALSE)
	{
		if ($token = cookie::get('authautologin'))
		{
			// Delete the autologin cookie to prevent re-login
			cookie::delete('authautologin');
			
			// Clear the autologin token from the database
			$token = Jelly::factory('user_token', array('token' => $token));

			if ($token->loaded() AND $logout_all)
			{
				Jelly::factory('user_token', array('user_id' => $token->user->id))->delete();
			}
			elseif ($token->loaded())
			{
				$token->delete();
			}
		}

		return parent::logout($destroy);
	}

	/**
	 * Get the stored password for a username.
	 *
	 * @param   mixed   username
	 * @return  string
	 */
	public function password($user)
	{
		// Make sure we have a user object
		$user = $this->_get_object($user);
		
		return $user->password;
	}

	/**
	 * Complete the login for a user by incrementing the logins and setting
	 * session data: user_id, username, roles
	 *
	 * @param   object   user model object
	 * @return  void
	 */
	protected function complete_login($user)
	{
		// Update the number of logins
		$user->logins += 1;

		// Set the last login date
		$user->last_login = time();

		// Save the user
		$user->save();

		return parent::complete_login($user);
	}
	
	/**
	 * Convert a unique identifier string to a user object
	 * 
	 * @param mixed $user
	 * @return Model_User
	 */
	protected function _get_object($user)
	{
		static $current;
		
		//make sure the user is loaded only once.
		if ( ! is_object($current) AND is_string($user))
		{
			// Load the user
			$current = Jelly::factory('user', array('username' => $user));
		}
		
		if ( $user instanceof Jelly AND $user->loaded()) 
		{
			$current = $user;
		}

		return $current;
	}

} // End Auth_Sprig_Driver