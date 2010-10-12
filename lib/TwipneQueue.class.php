<?php
class TwipneQueue
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
  public static function isAWSKeysCorrect(){
    $aws_accesskey = Doctrine::getTable('SnsConfig')->get('optwipneplugin_aws_accesskey',null);
    $aws_secret = Doctrine::getTable('SnsConfig')->get('optwipneplugin_aws_secret',null);
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
    $aws_accesskey = Doctrine::getTable('SnsConfig')->get('optwipneplugin_aws_accesskey',null);
    $aws_secret = Doctrine::getTable('SnsConfig')->get('optwipneplugin_aws_secret',null);
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
      $act->setPublicFlag(PUBLIC_FLAG_PRIVATE);
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
    $aws_accesskey = Doctrine::getTable('SnsConfig')->get('optwipneplugin_aws_accesskey',null);
    $aws_secret = Doctrine::getTable('SnsConfig')->get('optwipneplugin_aws_secret',null);
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
    $aws_accesskey = Doctrine::getTable('SnsConfig')->get('optwipneplugin_aws_accesskey',null);
    $aws_secret = Doctrine::getTable('SnsConfig')->get('optwipneplugin_aws_secret',null);
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

