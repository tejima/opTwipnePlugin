<?php
class PNE2Task extends sfBaseTask
{
  protected function configure()
  {
    set_time_limit(120);
    mb_language("Japanese");
    mb_internal_encoding("utf-8");

    $this->namespace        = 'tjm';
    $this->name             = 'PNE2';
    $this->aliases          = array('tjm-pne2');
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [feed-reader|INFO] task does things.
Call it with:

  [php symfony socialagent:feed-reader [--env="..."] application|INFO]
EOF;
    //$this->addArgument('application', sfCommandArgument::REQUIRED, 'The application name');
    //$this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod');
  }
  protected function execute($arguments = array(), $options = array())
  {
    sfOpenPNEApplicationConfiguration::registerZend();
    Zend_Service_Amazon_Sqs::setKeys($app['all']['twipne_config']['accesskey'],$app['all']['twipne_config']['secretaccesskey']);
    $databaseManager = new sfDatabaseManager($this->configuration);

    $this->sqs = new Zend_Service_Amazon_Sqs();

    try{
      $this->pne2();
    }catch(Exception $e){
      echo "!!!!!!!!!!Exception caught. pass this user.\n";
      echo $e->getMessage();
    }
    echo "sleep\n";
    sleep(18);
    try{
      $this->pne2();
    }catch(Exception $e){
      echo "!!!!!!!!!!Exception caught. pass this user.\n";
      echo $e->getMessage();
    }
    echo "sleep\n";
    sleep(18);
    try{
      $this->pne2();
    }catch(Exception $e){
      echo "!!!!!!!!!!Exception caught. pass this user.\n";
      echo $e->getMessage();
    }
  }
}
