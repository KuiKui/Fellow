<?php
require_once dirname(__FILE__).'/../lib/includes.php';

$cli = new CLI();
$config = new Config(dirname(__FILE__).'/../config/config.yml', $cli);
$git = new Git($cli);
$fellow = new Fellow($git, $cli);

$projectId = $fellow->getCurrentProjectId();
$featureBranch = $fellow->initCommandOnFeatureBranch();

if(count($argv) >= 2)
{
  if($argv[1] == 'master')
  {
    $baseBranch = $argv[1];
  }
  else
  {
    $cli->error("'master' expected as first parameter");
  }
}
else
{
  $baseBranch = $fellow->getBaseBranch($featureBranch);
}

$git->cmd('git checkout %s', $baseBranch);
$git->cmd('git merge origin/%s', $baseBranch);
$git->cmd('git checkout %s', $featureBranch);
$git->cmd('git merge %s', $baseBranch);

$lastMasterHash = $git->getLastCommitHash($baseBranch);

$fellow->send($config->get('Crew-server-url'), 'synchronise', array('project' => $projectId,'branch' => $featureBranch, 'commit' => $lastMasterHash));
