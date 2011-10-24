<?php

class Git
{
  protected $output = null;

  function __construct($output)
  {
    $this->output = $output;
  }
  
  public function cmd()
  {
    $cmd = call_user_func_array('sprintf', func_get_args());
    echo("\033[0;35m");
    system($cmd, $state);
    echo("\033[0m");
    return $state;
  }

  public function cmdWithResults()
  {
    $cmd = call_user_func_array('sprintf', func_get_args());
    exec($cmd, $results, $state);
    return array($state, $results);
  }
  
  public function getCurrentBranch($expected = null, $notExpected = null)
  {
    $results = $this->cmdWithResults("git symbolic-ref HEAD | cut -d'/' -f3");
    
    if(!isset($results[1][0]))
    {
      if(!is_null($this->output))
      {
        $this->output->error("no current branch");
      }
    }
    
    $currentBranch = $results[1][0];
    
    if(!is_null($expected) && $currentBranch != $expected)
    {
      if(!is_null($this->output))
      {
        $this->output->error("current branch [%s] don't match expected branch [%s]", $currentBranch, $expected);
      }
    }
    
    if(!is_null($notExpected) && $currentBranch == $notExpected)
    {
      if(!is_null($this->output))
      {
        $this->output->error("current branch [%s] match not expected branch [%s]", $currentBranch, $notExpected);
      }
    }
    
    return $currentBranch;
  }
  
  public function branchExists($branch, $remote = false)
  {
    if($remote)
    {
      $results = $this->cmdWithResults("git branch -r | grep '/%s$'", $branch);
    }
    else
    {
      $results = $this->cmdWithResults("git branch | grep ' %s$'", $branch);
    }
    
    return (isset($results[1][0]) && count($results[1][0]) > 0);
  }
  
  public function getLastCommitHash($branch, $remote = false)
  {
    if($remote)
    {
      $results = $this->cmdWithResults("git log --oneline --format='%%H' -n1 origin/%s", $branch);
    }
    else
    {
      $results = $this->cmdWithResults("git log --oneline --format='%%H' -n1 %s --", $branch);
    }
    
    if(isset($results[1][0]) && count($results[1][0]) > 0)
    {
      return $results[1][0];
    }
    
    $this->output->error("no last commit on the branch %s [%s]", $branch, ($remote) ? 'remote' : 'local');
  }
  
  public function setConfig($name, $value)
  {
    $status = $this->cmd('git config %s %s', escapeshellarg($name), escapeshellarg($value));
    if($status == 0)
    {
      $this->output->success('value [%s] set to [%s]', $name, $value);
    }
    else
    {
      $this->output->error('could not set value [%s] to [%s]', $name, $value);
    }
    
    return ($status == 0);
  }

  public function getConfig($name)
  {
    $results = $this->cmdWithResults('git config --get %s', escapeshellarg($name));

    if(isset($results[1][0]) && count($results[1][0]) > 0)
    {
      return $results[1][0];
    }
    
    $this->output->error('could not find git configuration key [%s]', $name);
  }

  public function removeConfigSection($name)
  {
    return ($this->cmd('git config --remove-section %s', escapeshellarg($name)) == 0);
  }

  public function removeConfig($name)
  {
    return ($this->cmd('git config --unset %s', escapeshellarg($name)) == 0);
  }
  
  public function getCurrentFellowProjectId()
  {
    $projectId = $this->getConfig('fellow.projectid');
    if(!is_numeric($projectId) || $projectId == 0)
    {
      $this->output->error('wrong project id [%s]', $projectId);
    }
    return $projectId;
  }
}
