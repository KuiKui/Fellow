<?php
require_once dirname(__FILE__).'/../lib/includes.php';

$cli = new CLI();
$config = new Config(dirname(__FILE__).'/../config/config.yml', $cli);
$git = new Git($cli);

$projectId = $git->getCurrentFellowProjectId();
$git->getCurrentBranch('master');

if(count($argv) < 2)
{
  $cli->error("new feature name is missing");
}
$featureBranch = $argv[1];

$git->cmd('git fetch origin');
$existsOnLocal = $git->branchExists($featureBranch);
$existsOnRemote = $git->branchExists($featureBranch, true);

if($existsOnRemote)
{
  $cli->info("La branch %s est déjà présente sur le remote", $featureBranch);
  if($existsOnLocal)
  {
    $cli->info("La branch %s est déjà présente en local", $featureBranch);
    $git->cmd("git checkout %s", $featureBranch);
  }
  else
  {
    $cli->info("Création de la branch %s en local", $featureBranch);
    $git->cmd("git checkout -b %s", $featureBranch);
  }
  
  $cli->info("Retrackage de la branch %s", $featureBranch);
  $git->cmd("git branch --set-upstream %s origin/%s", $featureBranch, $featureBranch);
  
  $cli->info("Récupération des donnée de la branch %s remote vers local", $featureBranch);
  $git->cmd("git merge origin/%s", $featureBranch);
}
else
{  
  if($existsOnLocal)
  {
    $cli->info("La branch %s est déjà présente en local", $featureBranch);
    $git->cmd("git checkout %s", $featureBranch);
  }
  else
  {
    $cli->info("On crée la branch %s en local", $featureBranch);
    $git->cmd("git checkout -b %s", $featureBranch);
  }

  $cli->info("On push la branch %s sur le remote", $featureBranch);
  $git->cmd("git push origin %s", $featureBranch);
}

$api = new curlConnexion($config->get('Crew-server-url'));
$api->setOutput($cli);
$json = $api->send('startBranch', array('project' => $projectId), true);
$status = json_decode($json, true);
$cli->custom("<<< API : %s",$status['message']);
