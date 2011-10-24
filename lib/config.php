<?php

class Config
{
  protected $infos = array();
  protected $output = null;
  
  function __construct($filename, $output = null)
  {
    $this->infos = sfYaml::load(dirname(__FILE__).'/../config/config.yml');
    if(!is_null($output))
    {
      $this->output = $output;
    }
  }
  
  function get($name)
  {
    if(isset($this->infos[$name]))
    {
      return $this->infos[$name];
    }
    else if(!is_null($this->output))
    {
      $this->output->error('Fellow param [%]', $name);
    }
  }
}
