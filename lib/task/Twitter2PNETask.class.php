<?php
class Twitter2PNETask extends sfBaseTask
{
  protected function configure()
  {
    set_time_limit(120);
    mb_language("Japanese");
    mb_internal_encoding("utf-8");
    $this->namespace        = 'tjm';
    $this->name             = 'Twitter2PNE';
    $this->aliases          = array('tjm-twitter2pne');
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
    $databaseManager = new sfDatabaseManager($this->configuration);
    try{
      $this->processTwitterSelf();
    }catch(Exception $e){
      echo "!!!!!!!!!!Exception caught. pass this user.". $e->getMessage() ."\n";
        echo $e->getMessage();
        echo $e->getFile();
        echo $e->getLine();
    }

    sleep(30);
    try{
      $this->processTwitterSelf();
    }catch(Exception $e){
      echo "!!!!!!!!!!Exception caught. pass this user.". $e->getMessage() ."\n";
        echo $e->getMessage();
        echo $e->getFile();
        echo $e->getLine();
    }
  }
  private function processAutoPost($timing = '24'){

    $q = Doctrine_Query::create()
    ->select('mc.member_id,mc.value')->from('MemberConfig mc')->where('mc.name like ?','AUTOPOSTBODY'.$timing.'%');
    $mclist = $q->fetchArray();
    foreach($mclist as $mcline){
      if(!is_null($mcline['member_id']) && !is_null($mcline['value'])){
        $member = Doctrine::getTable('Member')->find($mcline['member_id']);
        TwoPNE::processQueing($member->getId(),$mcline['value']);
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
                TwoPNE::processQueing($m->getId(),$m->getConfig('TWITTERDIRECT_KEYWORD')."\n".$tlline->text);
              }
            }else{
              TwoPNE::processQueing($m->getId(),$tlline->text);
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
            TwoPNE::processQueing($m->getId(),$tlline->text);
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
}
