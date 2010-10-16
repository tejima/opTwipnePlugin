<?php

include(dirname(__FILE__).'/../bootstrap/Doctrine.php');
require_once dirname(__FILE__).'/../bootstrap/unit.php';
$t = new lime_test();
$t->comment("lime drive");

$test_member_id = 2;
$m =  Doctrine::getTable('Member')->find($test_member_id);

$t->ok(test_google_auth($m),"test_google_auth");
$t->ok(processCalendar($m),"processCalendar");

function test_google_auth($member = null)
{
  $user = $member->getConfig('GOOGLEID');
  $pass = $member->getConfig('GOOGLEPASS');
  try{
    $service = Zend_Gdata_Calendar::AUTH_SERVICE_NAME;
    $client = Zend_Gdata_ClientLogin::getHttpClient($user,$pass,$service);
    $service = new Zend_Gdata_Calendar($client);
  }catch(Exception $e){
    echo $e->getMessage();
    return false;
  }
  return true;
}

function processCalendar($member = null){
  $user = $member->getConfig('GOOGLEID');
  $pass = $member->getConfig('GOOGLEPASS');

  $service = Zend_Gdata_Calendar::AUTH_SERVICE_NAME;
  $client = Zend_Gdata_ClientLogin::getHttpClient($user,$pass,$service);
  $service = new Zend_Gdata_Calendar($client);
  try{
    $query = $service->newEventQuery();
    $query->setUser('default');
    $query->setVisibility('private');
    $query->setProjection('full');
    $query->setOrderby('starttime');
    //$query->setFutureevents('true');
    $q_starttime = strtotime('now');
    $q_endtime = strtotime('now + 5 days');
    $query->setStartMin(date('c',$q_starttime));
    $query->setStartMax(date('c',$q_endtime));
    $eventFeed = $service->getCalendarEventFeed($query);
    foreach($eventFeed as $event){
      //echo "start time:"  ;
      echo "event";
      echo $event->title;
      //print_r($event);
      $t_starttime = strtotime($event->when[0]->startTime);
      if($t_starttime >= $q_starttime && $t_starttime < $q_endtime){
        if(FALSE === strpos($event->title,'【PNE済み】')){
          echo $event->title . "\n";
          echo "    ". $event->id . "\n"; 


          $old_event = $event;
          //print_r($event->content->text);
//          $event->title = $service->newTitle("【PNE済み】".$event->title);
//          $event->save();

          $event->title = $service->newTitle("【PNE済み】".$event->title);
          $event->recurrence = new Zend_Gdata_Extension_RecurrenceException(true,null,$old_event);
          $newEvent = $service->insertEvent($event);



        }else{
          echo "【PNE済み】なのでパス\n";
        }
      }else{
        echo "レンジ内のイベントではないのでパス\n";
      }
    }
  }catch(Zend_Gdata_App_Exception $e){
    echo $e->getMessage();
  }
}




//$this->teststrcut();
//$this->test_member();
//$this->test_insert_entry();
//$this->test_list_entry();
//$this->test_insert_config();
//$this->test_reverse_search();
//$this->test_get_twitter();
//$this->test_access_sns_config();
//$this->test_mixi_api();
//$this->test_get_twitter_self();
//$this->test_get_member4config();
//$this->test_get_twitterfoxtext();
//$this->test_islog();
//$this->test_auto();
//print_r($options);
//$this->test_option($options);
//$this->test_timeline();
//$this->test_rpc_failure();
//echo get_include_path();
//$this->test_array_intersect();
//$this->test_browser();
//$this->test_exec();
//$this->test_parse_str($options);
//date_default_timezone_set('Asia/Tokyo');
//$this->test_calendar($options);
//$this->test_recurrence();
//$this->test_readonly_calendar();
//$this->test_member_config();
//$this->test_member_config_checkbox();
//$this->test_sepalator();
//$this->test_lf();
//$this->test_pne_community();
//$this->test_pne_exclude();
//$this->test_twitter();
//$this->test_sepalator2();
//$this->test_TwipneQueue();
//$this->test_reg();
//$this->test_postqueue();

function test_postqueue(){
  $queue_url = $this->sqs->create("test_queue");
  $this->sqs->send($queue_url, "TESTQUEUE");
  $queue = $this->sqs->create('test_queue');
  foreach($this->sqs->receive($queue,5,3) as $message){
    print_r($message);
  }

}
function test_reg(){
  $target = "あ\nぼおおおおおおおおおおお";
  //$target = "あ い う え お あああああああああああ";
  //$target = "ああ い う え お あああああああああああ";
  $re = '/^(([あ-ん][\s\n])+)(.*)$/us';
  $result = preg_match($re,$target,$match);
  print_r($match);
  $command = ($result) ? $match[0] : null ;
  
  $command = trim($command);
  echo $command;
  echo ($command == "あ い う え お") ? "OK" : "NG" ;
}
function test_TwipneQueue(){
  $obj = TwipneQueue::getInstance();
  $m =  Doctrine::getTable('Member')->find(1);
  $result = $obj->processQueing($m,"つ\n\nあああああああああ");
}
function test_sepalator2(){
  $subject = 'あしたのあさも！きのうのよるも。きみの.となりにいるよ。だよ';
  $pattern = '[！.。]';
  mb_regex_encoding('UTF-8');
  $result = mb_split($pattern,$subject,10);
  print_r($result);
}
function test_twitter(){
$m =  Doctrine::getTable('Member')->find(1);
      $twitter = new Zend_Service_Twitter($m->getConfig('PRIV8TWITTERNAME'),$m->getConfig('PRIV8TWITTERPASS'));
  $tl = $twitter->status->userTimeline();

print_r($tl);

}
function test_pne_exclude(){
  $keywords = array('RT','@','||');
  foreach($keywords as $keyword){
    $keyword = preg_quote($keyword);
    $re = '/^' . $keyword . '/';
    $target = ';;RT: いやーーーーーーーーー';

    $result = preg_match($re,$target);
    echo "match:";
    print_r($result);
  
  }
}

function test_pne_community(){
  echo realpath('./') . "\n";
  $command = 'ruby '. realpath('./') . "/plugins/twipnePlugin/pne_community.rb http://ppne.jp/ tejima@tejimaya.com gatagata 197 本文";
  echo $command . "\n";
  echo exec($command);
}


function test_lf(){
  $str = <<< EOF
件名
本文
本文二行目
EOF;
  print_r(explode("\n",$str,2));

}
function test_sepalator(){
  $member = Doctrine::getTable('Member')->find(2);
  $separator_keywords = $member->getConfig('SEPALATOR_KEYWORDS');
  print_r(explode(" ",$separator_keywords));
  print_r($separator_keywords);
}
function test_member_config_checkbox(){
  $member = Doctrine::getTable('Member')->find(2);
  print_r($member->getConfig('SEPALATOR'));
}
function test_member_config(){

  $member = Doctrine::getTable('Member')->find(2);
  print_r($member->getConfig('TWITTERDIRECT'));

}
function test_readonly_calendar(){
  $user = 'USER';
  $pass = 'PASSWORD';
  $service = Zend_Gdata_Calendar::AUTH_SERVICE_NAME;
  $client = Zend_Gdata_ClientLogin::getHttpClient($user,$pass,$service);
  $service = new Zend_Gdata_Calendar($client);
/*
try {
  $listFeed= $service->getCalendarListFeed();
echo "<h1>カレンダーリストのフィード</h1>";
echo "<ul>\n";
foreach ($listFeed as $calendar) {
  echo "<li>" . $calendar->title .
       " (Event Feed: " . $calendar->id . ")</li>\n";
  $author = $calendar->getAuthor();
  echo $author[0]->getEmail() . "\n";
  //echo $calendar->getAuthor()->getEmail() . "\n";
  

  echo "\n\n\n\n\n\n\n\n\n";
  print_r($calendar);
}
echo "</ul>";

} catch (Zend_Gdata_App_Exception $e) {
  echo "エラー: " . $e->getMessage();
}
*/
  $query = $service->newEventQuery();
  $query->setUser('default');
  //$query->setUser('tejima@gmail.com');
  $query->setVisibility('private');
  $query->setProjection('full');
  $query->setOrderby('starttime');
  //$query->setFutureevents('true');
  $q_starttime = strtotime('now');
  $q_endtime = strtotime('now + 1 days');
  //$query->setStartMin(date('c',$q_starttime));
  //$query->setStartMax(date('c',$q_endtime));
  $eventFeed = $service->getCalendarEventFeed($query);
  foreach($eventFeed as $event){
    //echo "start time:"  ;
    echo "event";
    echo $event->title;
    print_r($event);
  }
}
function test_recurrence($member = null){
  $user = $member->getConfig('GOOGLEID');
  $pass = $member->getConfig('GOOGLEPASS');

  $service = Zend_Gdata_Calendar::AUTH_SERVICE_NAME;
  $client = Zend_Gdata_ClientLogin::getHttpClient($user,$pass,$service);
  $service = new Zend_Gdata_Calendar($client);
  $query = $service->newEventQuery();
  $query->setUser('default');
  $query->setVisibility('private');
  $query->setProjection('full');
  $query->setOrderby('starttime');

  return false;
  //$query->setFutureevents('true');
  $q_starttime = strtotime('now');
  $q_endtime = strtotime('now + 1 days');
  $query->setStartMin(date('c',$q_starttime));
  $query->setStartMax(date('c',$q_endtime));
  $eventFeed = $service->getCalendarEventFeed($query);
  foreach($eventFeed as $event){
    //echo "start time:"  ;
    echo "event";
    echo $event->title;
    //print_r($event);
    $t_starttime = strtotime($event->when[0]->startTime);
    if(FALSE === strpos($event->title,'【完了】')){
      echo $event->title . "\n";
      echo "    ". $event->id . "\n"; 
      print_r($event->content->text);
      $old_event = $event;
      $event= $service->newEventEntry();
      $event->title = $service->newTitle("aaaaaaaaaaaaaa");
      $event->where = array($service->newWhere("Mountain View, California"));
      $event->content =
      $service->newContent(" This is my awesome event. RSVP required.");
      $startDate = "2009-11-19";
      $startTime = "03:50";
      $endDate = "2009-11-19";
      $endTime = "04:50";
      $tzOffset = "+09";

      $when = $service->newWhen();
      $when->startTime = "{$startDate}T{$startTime}:00.000{$tzOffset}:00";
      $when->endTime = "{$endDate}T{$endTime}:00.000{$tzOffset}:00";
      $event->when = array($when);
      $event->recurrence = new Zend_Gdata_Extension_RecurrenceException(true,null,$old_event);
      //$event->save();
      $newEvent = $service->insertEvent($event);
    }else{
      echo "【完了】なのでパス\n";
    }
  }    
}
function test_parse_str($options){
  $command = $options['command'];
  $qname = $options['qname'];
  $arr = array();
  parse_str($command,$arr);

  print_r($arr);
  $queue_url = $this->sqs->create($qname);
  $this->sqs->send($queue_url, serialize($arr));
}
function test_exec(){
  echo realpath('./') . "\n";
  $command = 'ruby '. realpath('./') . "/plugins/twipnePlugin/voice.rb";
  echo $command . "\n";
  echo exec($command);
}
function test_browser(){
  // $context = sfContext::createInstance($this->configuration->getApplicationConfiguration());
  //$config =  ProjectConfiguration::getApplicationConfiguration();

  sfContext::createInstance($this->configuration);
  $browser = new sfTestFunctional(new sfBrowser());
  $browser->get('http://sns.openpne.jp/')->
  with('request')->begin()->
    isParameter('m','pc')->
    isParameter('a','page_o_help_login_error')->
  end()->

  with('response')->begin()->
    isStatusCode(200)->
    checkElement('body','aaaaaaaaa')->
  end();
//http://sns.openpne.jp/?m=pc&a=page_o_help_login_error
  //$b->get('http://www.google.co.jp/')->click('Gmail');
  
}
function test_array_intersect(){
  $array1 = array('つ','い','た');
  $array2 = array('い','た','つ');
  $result = array_intersect($array1,$array2);
  print_r($result);
  
  print_r($array1); 
}
function test_timeline(){
  //date_default_timezone_set('Asia/Tokyo');
  $time = 'Sat Sep 26 08:00:40 +0000 2009';
  echo strtotime($time) . "\n";
  echo time() . "\n";
  echo date('l jS \of F Y H:i:s ',strtotime($time)) . "\n";
  echo date('l jS \of F Y H:i:s ',time()) . "\n";
}
function test_option($options){
   $result = $options['mode'];
   print_r($result);
}
function test_auto(){
  $task = new QueueTask(new sfEventDispatcher(),new sfFormatter());
  $task->processQueing();
  $q = Doctrine_Query::create()
  ->select('mc.member_id,mc.value')->from('MemberConfig mc')->where('mc.name = ?','AUTOPOSTBODY');
  $obj = $q->fetchOne();
  echo $obj->member_id;
  echo $obj->value;
}

function test_islog(){
  if(sfConfig::get('sf_logging_enabled')){
    echo "true";
  }else{
    echo "false";
  }
  echo sfConfig::get('sf_hoge_piyo');
}
function test_get_twitterfoxtext(){
  $twitter = new Zend_Service_Twitter('himi2_dev', 'aoisora');
  $tl = $twitter->status->userTimeline();
   foreach($tl as $line){
    if($line->id > $latest){
      $latest = $line->id;
    }
    echo "\n-------------------------\n";
    echo "screen_name:" . $line->user->screen_name  , "\n";
    echo $line->text . "\n";
    echo bin2hex($line->text)  . "\n";
    break;
  }
}
function test_get_member4config(){
  $q = Doctrine_Query::create()
  ->select('mc.member_id')->from('MemberConfig mc')->where('mc.name = ?','PRIV8TWITTERNAME');
 $list = $q->execute();
  foreach( $list as $line){
    $m =  Doctrine::getTable('Member')->find($line->member_id);
    echo $m->getConfig('PRIV8TWITTERNAME');
    echo ":";
    echo $m->getConfig('PRIV8TWITTERPASS');
    echo "\n";
  }
}
function test_reverse_search($name = 'tejicube'){
 $q = Doctrine_Query::create()
  ->select('mc.member_id')
  ->from('MemberConfig mc')
  ->where('mc.name = ?','PRIV8TWITTERNAME')
  ->andWhere('mc.value = ?',$name); 
 $obj = $q->fetchOne();
  $member_id = is_object($obj) ? $obj->member_id : null;
  echo $member_id;
}
function test_insert_config(){
  $config = new SnsConfig();
  $config->name = 'test_japanese';
  $config->value = 'ぼぢー';
  $config->save();
}
function test_insert_entry(){
  $data = new Entry();
  $data->body = '日本語ぼでい';
  $data->meta = 'にほんごめた';
  $data->member_id = 2;
  $data->save();
}
function test_list_entry(){
  $q = Doctrine_Query::create()->from('Entry e')->where('e.member_id = ?',2);
  $entries = $q->fetchArray();
  print_r($entries);
}

function test_member(){
  $_member =  Doctrine::getTable('Member')->find(2);
  $q = Doctrine_Query::create()->from('ProfileOptionTranslation p')->where('p.id = ?',7)->addWhere('p.lang = ja_JP');
  $list = $q->fetchOne();
  print_r($list);
  //print_r($_member->getProfile('nickname')->value);
  //print_r($_member->getProfile('SNSNAME1')->getValue());
  //print_r($_member->getProfile('TWITTERNAME')->value);
}
function teststrcut(){
  $str = 'ああああああああああいいいいいいいいいいううううううううううええええええええええおおおおおおおおおおああああああああああいいいいいいいいいいううううううううううええええええええええおおおおおおおおおおああああああああああいいいいいいいいいいううううううううううええええええええええおおおおおおおおおお';
  $str2 = 'aああああああああああいいいいいいいいいいううううううううううええええええええええおおおおおおおおおおああああああああああいいいいいいいいいいううううううううううええええええええええおおおおおおおおおおああああああああああいいいいいいいいいいううううううううううええええええええええおおおおおおおおおお';
$_status = mb_substr($str2,0,140,'UTF-8');
  echo $_status ;
}
