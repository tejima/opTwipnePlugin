<?php
class CalendarQueueTask extends sfBaseTask
{
  protected function configure()
  {
    set_time_limit(120);
    mb_language("Japanese");
    mb_internal_encoding("utf-8");
    $this->namespace        = 'tjm';
    $this->name             = 'calendarqueue';
    $this->aliases          = array('tjm-calendarqueue');
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
    foreach($result_arr as $result){
      TwipneQueue::processQueing($result['MEMBER_ID'],$result['BODY']);
    }
  }
}
