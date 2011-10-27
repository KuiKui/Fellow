<?php

class CLI
{
  public function error()
  {
    $msg = call_user_func_array('sprintf', func_get_args());
    printf("\033[1;37;41mError\033[0;31;21m : %s\033[0m\n", $msg);
    exit(0);
  }

  public function success()
  {
    $msg = call_user_func_array('sprintf', func_get_args());
    printf("\033[1;37;44mSuccess\033[0;34;21m : %s\033[0m\n", $msg);
  }
  
  public function info()
  {
    $msg = call_user_func_array('sprintf', func_get_args());
    printf("* Infos : %s\n", $msg);
  }
  
  public function custom()
  {
    $msg = call_user_func_array('sprintf', func_get_args());
    printf("\033[0;0;33m%s\033[0m\n", $msg);
  }
}
