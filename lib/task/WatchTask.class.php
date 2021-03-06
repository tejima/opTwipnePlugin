<?php
class WatchTask extends sfBaseTask
{
  protected function configure()
  {
    mb_language("Japanese");
    mb_internal_encoding("utf-8");

    $this->namespace        = 'pne';
    $this->name             = '20090808watch';
    $this->aliases          = array('pne-watch');
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
    $databaseManager = new sfDatabaseManager($this->configuration);
    $app = sfYaml::load(sfConfig::get('sf_root_dir').'/plugins/twipnePlugin/config/app.yml');
    Zend_Service_Amazon_Sqs::setKeys($app['all']['twipne_config']['accesskey'],$app['all']['twipne_config']['secretaccesskey']);
    $sqs = new Zend_Service_Amazon_Sqs();
 
    
    echo "-----------------------------------------\n";

    $queue_url = $sqs->create('twitter_queue');
    echo $queue_url ."\n";
    echo "twitter_queue count " . $sqs->count($queue_url) . "\n";  
    //$sqs->delete($queue_url);
    foreach ($sqs->receive($queue_url,6,0) as $receive_message) {
      echo $receive_message['body']."\n";
    }

    $queue_url = $sqs->create('openpne_queue');
    echo $queue_url ."\n";
    echo "openpne_queue count " . $sqs->count($queue_url) . "\n";
    //$sqs->delete($queue_url);
    foreach ($sqs->receive($queue_url,5,0) as $receive_message) {
      echo $receive_message['body']."\n";
    }
    $queue_url = $sqs->create('openpne_commu_queue');
    echo $queue_url ."\n";
    echo "openpne_commu_queue count " . $sqs->count($queue_url) . "\n";
    //$sqs->delete($queue_url);
    foreach ($sqs->receive($queue_url,5,0) as $receive_message) {
      echo $receive_message['body']."\n";
    }


    $queue_url = $sqs->create('wordpress_queue');
    echo $queue_url ."\n";
    echo "wordpress_queue count " . $sqs->count($queue_url) . "\n";
    //$sqs->delete($queue_url);
    foreach ($sqs->receive($queue_url,5,0) as $receive_message) {
      echo $receive_message['body']."\n";
    }

    $queue_url = $sqs->create('mixi_queue');
    echo $queue_url ."\n";
    echo "mixi_queue count " . $sqs->count($queue_url) . "\n";
    //$sqs->delete($queue_url);
    foreach ($sqs->receive($queue_url,5,0) as $receive_message) {
      echo $receive_message['body']."\n";
    }

    $queue_url = $sqs->create('mixivoice_queue');
    echo $queue_url ."\n";
    echo "mixivoice_queue count " . $sqs->count($queue_url) . "\n";
    //$sqs->delete($queue_url);
    foreach ($sqs->receive($queue_url,5,0) as $receive_message) {
      echo $receive_message['body']."\n";
    }
  }
}
