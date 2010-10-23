<?php
class TwoPNE
{
  //private static $instance;

  /*
  public static function getInstance(){
    if(TwipneQueue::$instance == null){
      TwipneQueue::$instance = new TwipneQueue();
    }
    return TwipneQueue::$instance;
  }
  */
  public static function processGoogleCalendar(){
    $q = Doctrine_Query::create()
    ->select('mc.member_id,mc.value')->from('MemberConfig mc')->where('mc.name = ?','GOOGLEID');
    $mclist = $q->fetchArray();
    foreach($mclist as $mcline){
      if(!is_null($mcline['member_id']) && !is_null($mcline['value'])){
        return self::processCalendar($mcline['member_id']);
      }
    }
  }
  public static function processCalendar($member_id){

    //echo "processCalendar()";
    $member = Doctrine::getTable('Member')->find($member_id);
    $service = Zend_Gdata_Calendar::AUTH_SERVICE_NAME;
    $client = Zend_Gdata_ClientLogin::getHttpClient($member->getConfig('GOOGLEID'),$member->getConfig('GOOGLEPASS'),$service);
    $service = new Zend_Gdata_Calendar($client);

    $result_arr = array();
    try{
      $query = $service->newEventQuery();
      $query->setUser('default');
      $query->setVisibility('private');
      $query->setProjection('full');
      $query->setOrderby('starttime');
      //$query->setFutureevents('true');
      $q_starttime = strtotime('now');
      $q_endtime = strtotime('now + 5 minutes');
      $query->setStartMin(date('c',$q_starttime));
      $query->setStartMax(date('c',$q_endtime));
      $eventFeed = $service->getCalendarEventFeed($query);
      //echo "after getCalendarEventFeed()";
      //echo sizeof($eventFeed);
      foreach($eventFeed as $event){
        //echo "start time:"  ;
        echo "active events";
        $t_starttime = strtotime($event->when[0]->startTime);
        if($t_starttime >= $q_starttime && $t_starttime < $q_endtime){
          if(FALSE === strpos($event->title,'【PNE済み】')){
            echo $event->title . "\n";
            echo "    ". $event->id . "\n";
            print_r($event->content->text);

            $result_arr[] = array("MEMBER_ID" => $member->getId(),"BODY" => $event->title .  '');
            $event->title = $service->newTitle("【PNE済み】".$event->title);
            $event->save();
          }else{
            echo "【PNE済み】なのでパス\n";
          }
        }else{
          echo "レンジ内のイベントではないのでパス\n";
        }
      }
    }catch(Zend_Gdata_App_Exception $e){
      echo "Exception";
      echo $e->getMessage();
    }
    return $result_arr;
  }
  public static function isAWSKeysCorrect(){
    $aws_accesskey = opConfig::get('optwipneplugin_aws_accesskey',null);
    $aws_secret = opConfig::get('optwipneplugin_aws_secret',null);
    if(!$aws_accesskey || !$aws_secret){
      return false;
    }
    try{
      $sqs = new Zend_Service_Amazon_Sqs($aws_accesskey, $aws_secret);
      $q = $sqs->create('test_dummy_queue');
    }catch(Exception $e){
      return false;
    }
    return true;
  }
  public static function postQueue($message){
    $aws_accesskey = opConfig::get('optwipneplugin_aws_accesskey',null);
    $aws_secret = opConfig::get('optwipneplugin_aws_secret',null);
    $sqs = new Zend_Service_Amazon_Sqs($aws_accesskey, $aws_secret);
    try{
      $queue_url = $sqs->create('twipne_queue');
      $sqs->send($queue_url, $message);
    }catch(Exception $e){
      return null;
    }
    return $message;
  }
  public static function processQueing($member_id,$message,$meta="twitter2pne"){
    try{
      preg_match("/(.*?)\n(.*)$/s",trim($message),$_m);

      $result = preg_match('/^(([あ-ん][\s\n])+)(.*)$/us',trim($message),$_m);
      //print_r($match);
      $command = ($result) ? $_m[1] : null ;
      $body = ($result) ? $_m[3] : $message;

      $act = new ActivityData();
      $act->setMemberId($member_id);
      $act->setBody("【成功】".$body);
      $act->setPublicFlag(ActivityDataTable::PUBLIC_FLAG_PRIVATE);
      $act->setIsPc(true);
      $act->save();

      // set basic parameters
      $params = array();
      $params['MEMBER_ID'] = $member_id;
      $params['STATUS'] = $body;

      mb_regex_encoding('UTF-8');
      $keywords = mb_split("[\s]+", trim($command));
      
      $result = array();
      foreach($keywords as $keyword){
        $params['KEYWORD'] = $keyword;
        self::postQueue(serialize($params));
        $result[] = $params;
      }
    }catch(Exception $e){
      echo "!!!!!!!!!!Exception caught. pass this user.\n";
      echo $e->getMessage();
      echo $e->getFile();
      echo $e->getLine();
      $result = null;
    }
    return $result;
  }
  public static function countQueue(){
    $aws_accesskey = opConfig::get('optwipneplugin_aws_accesskey',null);
    $aws_secret = opConfig::get('optwipneplugin_aws_secret',null);
    $sqs = new Zend_Service_Amazon_Sqs($aws_accesskey, $aws_secret);
    try{
      $queue_url = $sqs->create('twipne_queue');
      $result = $sqs->count($queue_url);
    }catch(Exception $e){
      return null;
    }
    return $result;
  }
  public static function deleteQueue()
  {
    $aws_accesskey = opConfig::get('optwipneplugin_aws_accesskey',null);
    $aws_secret = opConfig::get('optwipneplugin_aws_secret',null);
    $sqs = new Zend_Service_Amazon_Sqs($aws_accesskey, $aws_secret);
    try{
      $queue_url = $sqs->create('twipne_queue');
      $sqs->delete($queue_url);
    }catch(Exception $e){
      return null;
    }
    return true;
  }
}

