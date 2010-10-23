<?php
class Calendar2PNETask extends sfBaseTask
{
  protected function configure()
  {
    set_time_limit(120);
    mb_language("Japanese");
    mb_internal_encoding("utf-8");
    $this->namespace        = 'tjm';
    $this->name             = 'Calendar2PNE';
    $this->aliases          = array('tjm-calendar2pne');
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [feed-reader|INFO] task does things.
Call it with:

  [php symfony socialagent:feed-reader [--env="..."] application|INFO]
EOF;
    $this->addOption('mode', null, sfCommandOption::PARAMETER_OPTIONAL, 'The environment', null);
    $this->addOption('timing', null, sfCommandOption::PARAMETER_OPTIONAL, 'The environment', null);
  }
  protected function execute($arguments = array(), $options = array())
  {
    //$this->logMessage('QueueTask', 'info');
    //sfOpenPNEApplicationConfiguration::registerZend();

    $databaseManager = new sfDatabaseManager($this->configuration);

    echo "google calendar mode";
    $result_arr = TwipneQueue::processGoogleCalendar();
    print sizeof($result_arr);
    foreach($result_arr as $result){
      TwipneQueue::processQueing($result['MEMBER_ID'],$result['BODY']);
    }
  }
}
