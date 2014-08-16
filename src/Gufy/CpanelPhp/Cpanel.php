<?php
/**
 * Cpanel/WHM API
 *
*/

namespace Gufy\CpanelPhp;

/**
 * Provides easy to use class for calling some CPanel/WHM API functions
 *
 * @author Mochamad Gufron <mgufronefendi@gmail.com>
 * @version v1.0.0
 * @package Gufy\CpanelPhp
 * @link https://github.com/mgufrone/cpanel-php
 * @since v1.0.0
*/
class Cpanel
{
  /**
  * @var string Username of your whm server. Must be string
  */
  private $username;

  /**
  * @var string Password or long hash of your whm server.
  */
  private $password;

  /**
  * @var string Authentication type you want to use. You can set as 'hash' or 'password'.
  */
  private $auth_type;

  /**
  * @var string Host of your whm server. You must set it with full host with its port and protocol.
  */
  private $host;

  /**
  * @var string Sets of headers that will be sent at request.
  */
  protected $headers=array();

  /**
  * Class constructor. The options must be contain username, host, and password
  *
  * @param string $options options that will be passed and processed
  * @return object return as self-object
  */
  public function __construct($options=array())
  {
    if(!empty($options))
    {
      if(!empty($options['auth_type']))
        $this->setAuthType($options['auth_type']);
      return $this->checkOptions($options)
      ->setHost($options['host'])
      ->setAuthorization($options['username'], $options['password']);
    }
  }

  /**
  * Magic method who will call the CPanel/WHM Api
  *
  * @param string $function function name that will be called
  * @param array $arguments parameter that should be passed when calling API function
  * @return array result of called functions
  */
  public function __call($function, $arguments=[])
  {
    return $this->runQuery($function, $arguments);
  }

  /**
  * checking options for 'username', 'password', and 'host'. If they are not set, some exception will be thrown
  *
  * @param array $options list of options that will be checked
  * @return object return as self-object
  */
  private function checkOptions($options)
  {
    if(empty($options['username']))
      throw new \Exception('Username is not set', 2301);
    if(empty($options['password']))
      throw new \Exception('Password or hash is not set', 2302);
    if(empty($options['host']))
      throw new \Exception('CPanel Host is not set', 2303);
    return $this;
  }

  /**
  * set authorization for access.
  * It only set 'username' and 'password'
  *
  * @param string $username Username of your whm server.
  * @param string $password Password or long hash of your whm server.
  * @access public
  * @return object return as self-object
  */
  public function setAuthorization($username, $password)
  {
    $this->username = $username;
    $this->password = $password;
    return $this;
  }

  /**
  * set API Host.
  *
  * @param string $host Host of your whm server.
  * @access public
  * @return object return as self-object
  */
  public function setHost($host)
  {
    $this->host = $host;
    return $this;
  }

  /**
  * set Authentication Type.
  *
  * @param string $auth_type Authentication type for calling API.
  * @access public
  * @return object return as self-object
  */
  public function setAuthType($auth_type)
  {
    $this->auth_type = $auth_type;
    return $this;
  }

  /**
  * set some header.
  *
  * @param string $name key of header you want to add
  * @param string $value value of header you want to add
  * @return object return as self-object
  */
  public function setHeader($name, $value='')
  {
    $this->headers[$name] = $value;
    return $this;
  }

  /**
  * get username.
  *
  * @return string return username
  */
  public function getUsername()
  {
    return $this->username;
  }

  /**
  * get authentication type.
  *
  * @return string get authentication type
  */
  public function getAuthType()
  {
    return $this->auth_type;
  }

  /**
  * get password or long hash.
  *
  * @return string get password or long hash
  */
  public function getPassword()
  {
    return $this->password;
  }

  /**
  * get host of your whm server.
  *
  * @return string host of your whm server
  */
  public function getHost()
  {
    return $this->host;
  }

  /**
  * extend http headers that will be sent.
  *
  * @return array list of headers that will be sent
  */
  private function createHeader()
  {
    $headers = $this->headers;

    $username = $this->getUsername();
    $auth_type = $this->getAuthType();

    if('hash' == $auth_type)
      $headers['Authorization'] = 'WHM '.$username.':'. preg_replace("'(\r|\n)'","",$this->getPassword());
    elseif('password' == $auth_type)
      $headers['Authorization'] = 'Basic '.$username.':'. preg_replace("'(\r|\n)'","",$this->getPassword());

    return $headers;
  }

  /**
  * The executor. It will run API function and get the data
  *
  * @param string $action function name that will be called.
  * @param string $arguments list of parameters that will be attached.
  * @return array results of API call
  */
	protected function runQuery($action, $arguments)
	{
    $host = $this->getHost();
		$response = \GuzzleHttp\post($host.'/json-api/'.$action, [
		    'headers' => $this->createHeader(),
		    // 'body'    => $arguments[0],
		    'verify'  => false,
		    'query'	  => $arguments

		]);
		return $response->json();
	}
}
