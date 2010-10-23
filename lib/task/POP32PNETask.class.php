<?php
class POP32PNETask extends sfBaseTask
{
  protected function configure()
  {
    set_time_limit(120);
    mb_language("Japanese");
    mb_internal_encoding("utf-8");
    $this->namespace        = 'tjm';
    $this->name             = 'POP32PNE';
    $this->aliases          = array('tjm-pop32pne');
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

    if($options['mode'] == 'auto'){
      echo "auto mode";
      $this->processAutoPost($options['timing']);
      exit;
    }
    try{
      $this->processPOP3();
    }catch(Exception $e){
      echo "!!!!!!!!!!Exception caught. pass this user.". $e->getMessage() ."\n";
        echo $e->getMessage();
        echo $e->getFile();
        echo $e->getLine();
    }

    sleep(30);
    try{
      $this->processPOP3();
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
        TwipneQueue::processQueing($member->getId(),$mcline['value']);
      }
    }
  } 
  private function processPOP3(){
    echo "---------------------------->processPOP3() @pne.jp \n";
    try{
      $mail = new Zend_Mail_Storage_Pop3(array('host' => Doctrine::getTable('SnsConfig')->get('optwipneplugin_pop3_host','pop.gmail.com') ,
                                              'user' => Doctrine::getTable('SnsConfig')->get('optwipneplugin_pop3_user',null),
                                              'password' => Doctrine::getTable('SnsConfig')->get('optwipneplugin_pop3_pass',null),
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
        $re = '/' . Doctrine::getTable('SnsConfig')->get('optwipneplugin_mailprefix','noneprefix') . '\+(.*?)\+([0-9]+)@pne\.jp/';
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
        

        TwipneQueue::processQueing($member->getId(),$opMessage->getContent());
        $mail->removeMessage(1);
    }catch(Exception $e){
       echo $e->getMessage();
    }
  }
}
