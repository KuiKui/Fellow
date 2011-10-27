<?php

require_once dirname(__FILE__).'/../lib/includes.php';

$cli = new CLI();
$config = new Config(dirname(__FILE__).'/../config/config.yml', $cli);
$git = new Git($cli);

$projectId = $git->getCurrentFellowProjectId();
$featureBranch = $git->getCurrentBranch(null, 'master');
$git->cmd('git fetch');
$existsOnRemote = $git->branchExists($featureBranch, true);

if(!$existsOnRemote)
{
  $cli->error("La branche %s n'existe pas sur le remote", $featureBranch);
}

$cli->info("Push de la branch %s local vers le remote\n", $featureBranch);
$git->cmd('git merge master');
$lastLocalHash = $git->getLastCommitHash('master');

$api = new curlConnexion($config->get('Crew-server-url'));
$api->setOutput($cli);
$json = $api->send('synchronise', array('project' => $projectId, 'branch' => $featureBranch, 'commit' => $lastLocalHash), true);
$status = json_decode($json, true);
$cli->custom("<<< API : %s",$status['message']);
