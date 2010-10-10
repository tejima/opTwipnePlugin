<?php
class QueueTask extends sfBaseTask
{
  protected function configure()
  {
    set_time_limit(120);
    mb_language("Japanese");
    mb_internal_encoding("utf-8");
    $this->namespace        = 'pne';
    $this->name             = 'queue';
    $this->aliases          = array('pne-queue');
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
    $app = sfYaml::load(sfConfig::get('sf_root_dir').'/plugins/twipnePlugin/config/app.yml');

    $this->queue = TwipneQueue::getInstance();
    $databaseManager = new sfDatabaseManager($this->configuration);

    if($options['mode'] == 'auto'){
      echo "auto mode";
      $this->processAutoPost($options['timing']);
      exit;
    }else if($options['mode'] == 'calendar'){
      echo "google calendar mode";
      $this->processGoogleCalendar();
      exit;
    }
    try{
      $this->processPOP3();
      $this->processTwitterSelf();
    }catch(Exception $e){
      echo "!!!!!!!!!!Exception caught. pass this user.". $e->getMessage() ."\n";
        echo $e->getMessage();
        echo $e->getFile();
        echo $e->getLine();
    }

    sleep(30);
    try{
      $this->processPOP3();
      $this->processTwitterSelf();
    }catch(Exception $e){
      echo "!!!!!!!!!!Exception caught. pass this user.". $e->getMessage() ."\n";
        echo $e->getMessage();
        echo $e->getFile();
        echo $e->getLine();
    }
  }
  private function processGoogleCalendar(){
    $q = Doctrine_Query::create()
    ->select('mc.member_id,mc.value')->from('MemberConfig mc')->where('mc.name = ?','GOOGLEID');
    $mclist = $q->fetchArray();
    foreach($mclist as $mcline){
      if(!is_null($mcline['member_id']) && !is_null($mcline['value'])){
        $member = Doctrine::getTable('Member')->find($mcline['member_id']);
        $this->processCalendar($member);
      }
    }
  }
  private function processCalendar($member){
    $service = Zend_Gdata_Calendar::AUTH_SERVICE_NAME;
    $client = Zend_Gdata_ClientLogin::getHttpClient($member->getConfig('GOOGLEID'),$member->getConfig('GOOGLEPASS'),$service);
    $service = new Zend_Gdata_Calendar($client);
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
      foreach($eventFeed as $event){
        //echo "start time:"  ;
        $t_starttime = strtotime($event->when[0]->startTime);
        if($t_starttime >= $q_starttime && $t_starttime < $q_endtime){
          if(FALSE === strpos($event->title,'【完了】')){
            echo $event->title . "\n";
            echo "    ". $event->id . "\n";
            print_r($event->content->text);

            $result = $this->queue->processQueing($member,$event->content->text);
            if($result){
              $event->title = $service->newTitle("【完了】".$event->title);
              $event->save();
            }else{
              echo "プロセス時にエラー発生";
            }
          }else{
            echo "【完了】なのでパス\n";
          }
        }else{
          echo "レンジ内のイベントではないのでパス\n";
        }
      }
    }catch(Zend_Gdata_App_Exception $e){
      echo $e->getMessage();
    }
  }
  private function processAutoPost($timing = '24'){

    $q = Doctrine_Query::create()
    ->select('mc.member_id,mc.value')->from('MemberConfig mc')->where('mc.name like ?','AUTOPOSTBODY'.$timing.'%');
    $mclist = $q->fetchArray();
    foreach($mclist as $mcline){
      if(!is_null($mcline['member_id']) && !is_null($mcline['value'])){
        $member = Doctrine::getTable('Member')->find($mcline['member_id']);
        $this->queue->processQueing($member,$mcline['value']);
      }
    }
  } 
  private function processTwitterSelf(){
    echo "processTwitterSelf()\n";
    $twitter = new Zend_Service_Twitter('ID','PASS');
    $res = $twitter->status->update('PROCESS:' . date('H:i:s') );
    $borderline = $res->id - 5000000 * 21;
    print $borderline;
    $idlist = Doctrine_Query::create()
    ->select('mc.member_id')->from('MemberConfig mc')->where('mc.name = ?','PRIV8TWITTERNAME')->execute();
    foreach( $idlist as $idline){
      $m =  Doctrine::getTable('Member')->find($idline->member_id);
      if($m->getConfig('TWITTERLASTID') < $borderline){
	if(rand(0,10) < 9.9){
	  echo "behind the borderline\n";
          continue;
	}else{
	  echo "refresh the borderline\n";
        }
      }
      if(substr_count($m->getConfig('PRIV8TWITTERNAME'),':') == 0){
        $twitter = new Zend_Service_Twitter($m->getConfig('PRIV8TWITTERNAME'),$m->getConfig('PRIV8TWITTERPASS'));
      }else{
        continue;
      }

      $tl = $twitter->status->userTimeline();
      $db_latest = $m->getConfig('TWITTERLASTID');
      $latest = $db_latest;
      foreach($tl as $tlline){
        echo $m->getId()."<-ID:--------------->" .  $tlline->id . "<-Twitterの値:DBの値->" . $db_latest . "\n";
        if((float)$tlline->id <= (float)$db_latest){
          echo "パス\n";
          break;
        }else{
          if((float)$tlline->id > (float)$latest){
            $latest = $tlline->id;
          }
          $timestamp = strtotime($tlline->created_at);
          if(time() - 300 < $timestamp){
            echo "\n--------\nscreen_name:" . $tlline->user->screen_name  , "\n" . $tlline->text . "\n" . $tlline->id;
            if($m->getConfig('TWITTERDIRECT') == 1){
              $keywords = explode(" ",$m->getConfig('TWITTER_EXCLUDE'));
              foreach($keywords as $keyword){
                $num = preg_match('/^' . preg_quote($keyword) . '/',$tlline->text);
                if($num == 1){
                  $result = false;
                  break;
                }else{
                  $result = true;
                }
              }
              if($result){
                $this->queue->processQueing($m,$m->getConfig('TWITTERDIRECT_KEYWORD')."\n".$tlline->text);
              }
            }else{
              $this->queue->processQueing($m,$tlline->text);
            }
          }
        }
      }
      $m->setConfig('TWITTERLASTID',$latest);
    }
    echo "\n---------------PART2-------------------------\n";
    $idlist2 = Doctrine_Query::create()
    ->select('mc.member_id')->from('MemberConfig mc')->where('mc.name = ?','PRIV8TWITTERNAME2')->execute();
    foreach( $idlist2 as $idline){
      $m =  Doctrine::getTable('Member')->find($idline->member_id);
      if(substr_count($m->getConfig('PRIV8TWITTERNAME2'),':') == 0){
        $twitter = new Zend_Service_Twitter($m->getConfig('PRIV8TWITTERNAME2'),$m->getConfig('PRIV8TWITTERPASS2'));
      }else{
        continue;
      }
      $tl = $twitter->status->userTimeline();
      $db_latest = $m->getConfig('TWITTERLASTID2');
      $latest = $db_latest;
      foreach($tl as $tlline){
        echo $m->getId()."<--ID:" .  $tlline->id . "<--Twitterの値:DBの値-->" . $db_latest . "\n";
        if((float)$tlline->id <= (float)$db_latest){
          echo "パス\n";
          break;
        }else{
          if((float)$tlline->id > (float)$latest){
            $latest = $tlline->id;
          }
          $timestamp = strtotime($tlline->created_at);
          if(time() - 300 < $timestamp){
            echo "\n--------\nscreen_name:" . $tlline->user->screen_name  , "\n" . $tlline->text . "\n" . $tlline->id;
            $this->queue->processQueing($m,$tlline->text);
          }
        }
      }
      $m->setConfig('TWITTERLASTID2',$latest);
    }
  }
  private function member_id4twittername($name = 'tejicube'){
   $q = Doctrine_Query::create()
    ->select('mc.member_id')
    ->from('MemberConfig mc')
    ->where('mc.name = ?','PRIV8TWITTERNAME')
    ->andWhere('mc.value = ?',$name);
   $obj = $q->fetchOne();
    $member_id = is_object($obj) ? $obj->member_id : null;
    return $member_id;
  }

  private function processPOP3(){
    $app = sfYaml::load(sfConfig::get('sf_root_dir').'/plugins/twipnePlugin/config/app.yml');
    echo "---------------------------->processPOP3() @pne.jp \n";
    try{
      $mail = new Zend_Mail_Storage_Pop3(array('host' => 'pop.gmail.com' ,
                                              'user' => $app['all']['twipne_config']['pop3user'],
                                              'password' => $app['all']['twipne_config']['pop3pass'],
                                              'ssl' => 'SSL',
                                              'port' => 995)
                                      );
        echo $mail->countMessages() . " messages found(from POP3 Server)\n";
        $count = $mail->countMessages();
        if($count == 0){
          return;
        }
        mb_internal_encoding('UTF-8');
        $raw_data = $mail->getRawHeader(1) . "\r\n\r\n" .  $mail->getRawContent(1);
        $opMessage = new opMailMessage(array('raw' =>$raw_data));
        $re = '/' . $app['all']['twipne_config']['mailprefix'] . '\+(.*?)\+([0-9]+)@pne\.jp/';
        preg_match($re,$opMessage->getHeader('To'),$matches);
	if(!$matches){
		return;
	}
        echo "------------------>". $matches[1] ."<-keyword|id->". $matches[2] ."\n";
        $_keyword = $matches[1];
        $_id = $matches[2];
        $_imglist = $opMessage->getImages();

        echo "--------------------------opMessage.content\n";
        print_r(bin2hex($opMessage->getContent()));

        $member = Doctrine::getTable('Member')->find($_id);
        if(!$member){
	  return;
	}
          print ":::::::::::::::::::::::::::::::obj::::::::::::::::::::::";
	  print_r($member);  
	  if($member->getConfig('MAILKEYWORD') != $_keyword){
            echo "keyword missmatch\n";
           return;
          }
        

        $this->queue->processQueing($member,$opMessage->getContent());
        $mail->removeMessage(1);
    }catch(Exception $e){
       echo $e->getMessage();
    }
  }
}
