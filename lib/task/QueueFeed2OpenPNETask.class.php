<?php
class QueueFeed2OpenPNETask extends sfBaseTask
{
  protected function configure()
  {
    mb_language("Japanese");
    mb_internal_encoding("utf-8");

    $this->namespace        = 'pne';
    $this->name             = 'queuefromfeed';
    $this->aliases          = array('pne-queue-feed2openpne');
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
    $app = sfYaml::load(sfConfig::get('sf_root_dir').'/plugins/twipnePlugin/config/app.yml');
    Zend_Service_Amazon_Sqs::setKeys($app['all']['twipne_config']['accesskey'],$app['all']['twipne_config']['secretaccesskey']);
    $databaseManager = new sfDatabaseManager($this->configuration);
    
    $_feed_url = 'http://twitter.com/statuses/user_timeline/59987801.rss';
    $_user_id = 2;

    $q = Doctrine_Query::create()->from('FeedStatus f')->where('f.user_id = ?',$_user_id)->addWhere('f.feed_url = ?',$_feed_url);
    $list = $q->fetchOne();
    
    
    if(!$list){
      echo "empty line \n";
      $_fs = new FeedStatus();
      $_fs->user_id = $_user_id;
      $_fs->feed_url = $_feed_url;
      $_fs->date = '2000-01-01 00:00:00';
      $_fs->save();
      $list = $_fs;
    }

    $feed = Zend_Feed_Reader::import($_feed_url);
    
    $_latestentry = strtotime($list->date);
    foreach($feed as $entry){
      if(strtotime($entry->getDateCreated()) <= strtotime($list->date)){
        echo "old entry skip \n";
        continue;
      }else{
        echo $entry->getTitle() , "\n";
        //queue
        // set basic parameters
        $this->params = array();
        $this->params['MEMBER_ID'] = $member->getId();
        $this->params['TMP_NAME'] = $_imglist[0]['tmp_name'];
        $this->params['TYPE'] = $_imglist[0]['type'];
        $this->params['STATUS'] = $body;
        $queue_url = $sqs->create('openpne_queue');
        echo "SNSNAME1----------------->";
        if($member->getProfile('SNSNAME1')->getValue() == 7){
          $this->params['SNSURL'] = 'http://sns.openpne.jp/';
          $this->params['USERNAME'] = $member->getProfile('SNSID1')->value;
          $this->params['PASSWORD'] = $member->getProfile('SNSPASS1')->value;
          $sqs->send($queue_url, serialize($this->params));
        }else if($member->getProfile('SNSNAME2')->getValue() == 9){
          $this->params['SNSURL'] = 'http://sns.openpne.jp/';
          $this->params['USERNAME'] = $member->getProfile('SNSID2')->value;
          $this->params['PASSWORD'] = $member->getProfile('SNSPASS2')->value;
          $sqs->send($queue_url, serialize($this->params));
        }
        if($_latestentry < strtotime($entry->getDateCreated())){
          $_latestentry = strtotime($entry->getDateCreated());
        }
      }
    }
    $list->date = date('Y-m-d H:i:s',$_latestentry);
    $list->save();
    /*
    $feed = new FeedStatus();
    $feed->user_id = 2;
    $feed->feed_url = 'http://www.openpne.jp';
    $feed->date = '2009-08-15 00:00:00';
    $feed->save(); 
    */
  } 
}
