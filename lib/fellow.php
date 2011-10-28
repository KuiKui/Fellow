<?php

class Fellow
{
  protected $git = null;
  protected $output = null;
  
  function __construct($git = null, $output = null)
  {
    $this->git = $git;
    $this->output = $output;
  }
  
  protected function getOutput()
  {
    if(is_null($this->output))
    {
      $this->output = new CLI();
    }
    
    return $this->output;
  }
  
  protected function getGit()
  {
    if(is_null($this->git))
    {
      $this->git = new Git($this->getOutput());
    }
    
    return $this->git;
  }
  
  public function getCurrentProjectId()
  {
    $projectId = $this->getGit()->getConfig('fellow.projectid');
    if(!is_numeric($projectId) || $projectId == 0)
    {
      $this->getOutput()->error('wrong project id [%s]', $projectId);
    }
    return $projectId;
  }
  
  public function initCommandOnFeatureBranch()
  {
    $featureBranch = $this->getGit()->getCurrentBranch(null, 'master');

    $this->getGit()->cmd('git fetch origin');
    
    $existsOnRemote = $this->getGit()->branchExists($featureBranch, true);
    if(!$existsOnRemote)
    {
      $this->getOutput()->error("Branch %s doesn't exists on remote", $featureBranch);
    }
    
    return $featureBranch;
  }
  
  public function getBaseBranch($featureBranch)
  {
    $baseBranch = $this->getGit()->setConfig('fellow.base-branch-of-'.$featureBranch);
    if(strlen($baseBranch) == 0)
    {
      $this->getOutput()->error("No base branch stored for feature branch %s", $featureBranch);
    }
    return $baseBranch;
  }
  public function send($serviceUrl, $resourceUrl, $params = array(), $post = true)
  {
    $api = new curlConnexion($serviceUrl);
    $api->setOutput($this->getOutput());
    $json = $api->send($resourceUrl, $params, $post);
    $status = json_decode($json, true);
    $this->getOutput()->custom("<<< API : %s", $status['message']);
    
    return $status['result'];
  }
}
