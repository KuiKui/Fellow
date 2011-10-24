<?php

class curlConnexion
{
  protected $serviceUrl;
  protected $serviceContentType;
  protected $serviceUser;
  protected $servicePassword;
  protected $output;
  
  public function __construct($serviceUrl, $serviceContentType = 'json', $serviceUser = null, $servicePassword = null)
  {
    $this->serviceUrl         = $serviceUrl;
    $this->serviceContentType = $serviceContentType;
    $this->serviceUser        = $serviceUser;
    $this->servicePassword    = $servicePassword;
  }
  
  public function setOutput($output)
  {
    $this->output = $output;
  }

  public function send($ressourceUrl, $params = array(), $postMethod = false)
  {
    $encoded = '';
    foreach($params as $name => $value) {
      $encoded .= urlencode($name).'='.urlencode($value).'&';
    }
    $encoded = substr($encoded, 0, strlen($encoded)-1);

    if(!in_array($this->serviceContentType, array('json', 'xml')))
    {
      throw new RuntimeException(sprintf("Content-Type inconnu : %s (%s%s)", $this->serviceContentType, $this->serviceUrl, $ressourceUrl));
    }

    if($postMethod)
    {
      $url = $this->serviceUrl.$ressourceUrl;
    }
    else
    {
      $url = $this->serviceUrl.$ressourceUrl.$encoded;
    }
    
    if(!is_null($this->output))
    {
      $this->output->custom(">>> API : %s", $url);
    }

    try
    {
      $session = curl_init();

      curl_setopt($session, CURLOPT_URL, $url);
      if($postMethod)
      {
        curl_setopt($session, CURLOPT_POSTFIELDS,  $encoded);
        curl_setopt($session, CURLOPT_POST, 1);
      }
      curl_setopt($session, CURLOPT_TIMEOUT, 5);
      curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($session, CURLOPT_HEADER, 'Accept: application/'.$this->serviceContentType);
      curl_setopt($session, CURLOPT_HEADER, 'Content-Type: application/'.$this->serviceContentType);


      if(!is_null($this->serviceUser) && !is_null($this->servicePassword))
      {
        curl_setopt($session, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($session, CURLOPT_USERPWD, $this->serviceUser.':'.$this->servicePassword);
      }
      $response = curl_exec($session);
      $status   = curl_getinfo($session, CURLINFO_HTTP_CODE);
      if (!$response)
      {
        throw new RuntimeException(sprintf("%s (%s)", curl_error($session), $url));
      }
      curl_close($session);
      if($status != 200)
      {
        throw new RuntimeException(sprintf("Erreur %d (%s)", $status, $url));
      }
    }
    catch(Exception $e)
    {
      throw $e;
    }

    return $response;
  }
}
