<?php

require_once dirname(__FILE__).'/../lib/includes.php';

$cli = new CLI();
$config = new Config(dirname(__FILE__).'/../config/config.yml', $cli);
$git = new Git($cli);

$projectId = $git->getCurrentFellowProjectId();
$featureBranch = $git->getCurrentBranch(null, 'master');
$git->cmd('git fetch');
$lastLocalHash = $git->getLastCommitHash($featureBranch); 

$api = new curlConnexion($config->get('Crew-server-url'));
$api->setOutput($cli);
$json = $api->get('reviewStatus/', array('project' => $projectId,'branch' => $featureBranch, 'commit' => $lastLocalHash));
$status = json_decode($json, true);
$cli->custom("<<< API : %s",$status['message']);

switch($status['result'])
{
  case 1:
    $cli->error("La revue de code n'est pas encore effectuée");
  case 3:
    $cli->error("Le code a été refusé !");
  case 2:
    $lastLocalHash = $git->getLastCommitHash($featureBranch); 
    $lastRemoteHash = $git->getLastCommitHash($featureBranch, true);
    if($lastLocalHash != $lastRemoteHash)
    {
      $cli->error("Les branches %s ne sont pas synchronisées :\n* local : %s\n* remote : %s", $featureBranch, $lastLocalHash, $lastRemoteHash);
    }
    else
    {
      $cli->info("Les branches %s sont synchronisées : %s", $featureBranch, $lastLocalHash);
    }
    $git->cmd('git checkout master');
    $git->cmd("git merge %s", $featureBranch);
    $git->cmd("git branch -d %s", $featureBranch);
    $git->cmd('git push');
    $git->cmd('git push origin :%s', $featureBranch);
    $cli->success("Feature %s correctement terminée", $featureBranch);
    break;
  default:
    $cli->error("Status de code review inconnu : %s", $status['result']);
}
