<?php
require_once dirname(__FILE__).'/../lib/includes.php';

$cli = new CLI();
$git = new Git($cli);

$projectId = $git->getCurrentFellowProjectId();
$featureBranch = $git->getCurrentBranch(null, 'master');
$git->cmd('git fetch');
$existsOnRemote = $git->branchExists($featureBranch, true);

if(!$existsOnRemote)
{
  $cli->error("La branche %s n'existe pas sur le remote\n", $featureBranch);
}

printf("Push de la branch %s local vers le remote\n", $featureBranch);
$git->cmd("git push origin %s", $featureBranch);

$lastLocalHash = $git->getLastCommitHash($featureBranch);

$api = new curlConnexion('http://droussel-desktop/crew/');
$api->setOutput($cli);
$json = $api->post('reviewRequest/', array('project' => $projectId,'branch' => $featureBranch, 'commit' => $lastLocalHash));
$status = json_decode($json, true);
$cli->custom("<<< API : %s",$status['message']);
