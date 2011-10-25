<?php

require_once dirname(__FILE__).'/../lib/includes.php';

$cli = new CLI();
$config = new Config(dirname(__FILE__).'/../config/config.yml', $cli);
$git = new Git($cli);
$git->getCurrentBranch('master');

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

$api = new curlConnexion($config->get('Crew-server-url'));
$api->setOutput($cli);
$json = $api->send('addProject', array('name' => $projectName, 'remote' => $remote));
$status = json_decode($json, true);
$cli->custom("<<< API : %s", $status['message']);

if(!$status['result'])
{
  $cli->error('wrong project id [%s]', $status['result']);
}
$git->setConfig('fellow.projectid', $status['result']);
