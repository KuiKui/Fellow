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
}
