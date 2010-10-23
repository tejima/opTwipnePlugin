<?php
class PNE2
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
}

