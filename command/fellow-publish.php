<?php
require_once dirname(__FILE__).'/../lib/includes.php';

$cli = new CLI();
$config = new Config(dirname(__FILE__).'/../config/config.yml', $cli);
$git = new Git($cli);
$fellow = new Fellow($git, $cli);

$projectId = $fellow->getCurrentProjectId();
$featureBranch = $fellow->initCommandOnFeatureBranch();
$lastLocalHash = $git->getLastCommitHash($featureBranch);

$git->cmd("git push origin %s", $featureBranch);

$fellow->send($config->get('Crew-server-url'), 'reviewRequest', array('project' => $projectId,'branch' => $featureBranch, 'commit' => $lastLocalHash));
