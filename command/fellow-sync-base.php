<?php
require_once dirname(__FILE__).'/../lib/includes.php';

$cli = new CLI();
$config = new Config(dirname(__FILE__).'/../config/config.yml', $cli);
$git = new Git($cli);
$fellow = new Fellow($git, $cli);

$projectId = $fellow->getCurrentProjectId();
$featureBranch = $fellow->initCommandOnFeatureBranch();

$git->cmd('git checkout master');
$git->cmd('git merge origin/master');
$git->cmd('git checkout %s', $featureBranch);
$git->cmd('git merge master');

$lastMasterHash = $git->getLastCommitHash('master');

$fellow->send($config->get('Crew-server-url'), 'synchronise', array('project' => $projectId,'branch' => $featureBranch, 'commit' => $lastMasterHash));
