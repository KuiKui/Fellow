<?php
require_once dirname(__FILE__).'/../lib/includes.php';

$cli = new CLI();
$config = new Config(dirname(__FILE__).'/../config/config.yml', $cli);
$git = new Git($cli);
$fellow = new Fellow($git, $cli);

$projectId = $fellow->getCurrentProjectId();
$featureBranch = $fellow->initCommandOnFeatureBranch();
$lastLocalHash = $git->getLastCommitHash($featureBranch); 

$status = $fellow->send($config->get('Crew-server-url'), 'reviewStatus', array('project' => $projectId,'branch' => $featureBranch, 'commit' => $lastLocalHash), false);

switch($status)
{
  case 0:
    $cli->error("La revue de code n'est pas encore effectuée");

  case 2:
    $cli->error("Le code a été refusé !");

  case 1:
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
    $git->cmd('git merge origin/master');
    $git->cmd('git merge %s', $featureBranch);
    $git->cmd('git push');

    $git->cmd("git branch -d %s", $featureBranch);
    $git->cmd('git push origin :%s', $featureBranch);
    break;

  default:
    $cli->error("Status de code review inconnu : %s", $status);
}
