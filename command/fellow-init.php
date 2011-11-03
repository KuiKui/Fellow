<?php

require_once dirname(__FILE__).'/../lib/includes.php';

$cli = new CLI();
$config = new Config(dirname(__FILE__).'/../config/config.yml', $cli);
$git = new Git($cli);
$fellow = new Fellow($git, $cli);

switch(count($argv))
{
  case 1:
    $cli->error("params missing\n1 - project name\n2 - remote");
    
  case 2:
    $cli->error('remote missing');
  
  default:
    $projectName = $argv[1];
    $remote = $argv[2];
    break;
}

$status = $fellow->send($config->get('Crew-server-url'), 'addProject', array('name' => $projectName, 'remote' => $remote));

if(!$status['result'])
{
  $cli->error('wrong project id [%s]', $status['result']);
}
$git->setConfig('fellow.projectid', $status['result']);
