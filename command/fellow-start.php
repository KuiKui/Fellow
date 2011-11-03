<?php
require_once dirname(__FILE__).'/../lib/includes.php';

$cli = new CLI();
$config = new Config(dirname(__FILE__).'/../config/config.yml', $cli);
$git = new Git($cli);
$fellow = new Fellow($git, $cli);

$projectId = $fellow->getCurrentProjectId();

if(count($argv) < 2)
{
  $cli->error("new feature name is missing");
}
$featureBranch = $argv[1];
$baseBranch = (count($argv) >= 3) ? $argv[2] : 'master';

$git->cmd('git fetch origin');

$baseBranchExistsOnRemote = $git->branchExists($baseBranch, true);
if(!$baseBranchExistsOnRemote)
{
  $cli->error("Base branch %s doesn't exists on remote");
}
$git->setConfig('fellow.base-branch-of-'.$featureBranch, $baseBranch);

$baseBranchExistsOnLocal = $git->branchExists($baseBranch);
if($baseBranchExistsOnLocal)
{
  $git->cmd("git checkout %s", $baseBranch);
}
else
{
  $git->cmd("git checkout -b %s", $baseBranch);
}
$git->cmd("git branch --set-upstream %s origin/%s", $baseBranch, $baseBranch);
$git->cmd("git merge origin/%s", $baseBranch);

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
