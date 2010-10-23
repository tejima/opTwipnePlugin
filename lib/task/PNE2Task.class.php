<?php
class PNE2Task extends sfBaseTask
{
  protected function configure()
  {
    set_time_limit(120);
    mb_language("Japanese");
    mb_internal_encoding("utf-8");

    $this->namespace        = 'tjm';
    $this->name             = 'PNE2';
    $this->aliases          = array('tjm-pne2');
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
    Zend_Service_Amazon_Sqs::setKeys($app['all']['twipne_config']['accesskey'],$app['all']['twipne_config']['secretaccesskey']);
    $databaseManager = new sfDatabaseManager($this->configuration);

    $this->sqs = new Zend_Service_Amazon_Sqs();

    try{
      $this->pne2();
    }catch(Exception $e){
      echo "!!!!!!!!!!Exception caught. pass this user.\n";
      echo $e->getMessage();
    }
    echo "sleep\n";
    sleep(18);
    try{
      $this->pne2();
    }catch(Exception $e){
      echo "!!!!!!!!!!Exception caught. pass this user.\n";
      echo $e->getMessage();
    }
    echo "sleep\n";
    sleep(18);
    try{
      $this->pne2();
    }catch(Exception $e){
      echo "!!!!!!!!!!Exception caught. pass this user.\n";
      echo $e->getMessage();
    }
  }

  private function pne2(){
    $queue = $this->sqs->create('twipne_queue');
    foreach ($this->sqs->receive($queue,5,3) as $message){
      $_params = unserialize($message['body']);
      $_keyword = $_params['KEYWORD'];

      switch($_keyword){
        case("ふ"):
          $result = $this->processFacebookQueue($_params);
          break;
        case("あ"):
        case("く"):
        case("て"):
          $result = $this->processOpenPNEACTQueue($_params);
          break;
        case("な"):
          $result = $this->processAmebaNowQueue($_params);
          break;
        case("ぼ"):
          $result = $this->processMixiVoiceQueue($_params);
          break;
        case("ふ"):
        case("ふ"):
        case("ふ"):
        case("ふ"):
        case("ふ"):
      }
      if($result){
        $this->sqs->deleteMessage($queue,$message['handle']);
        $this->addEntry($_params['MEMBER_ID'],$_keyword,'【成功】'.$_params['STATUS']);
      }else{
        $this->addEntry($_params['MEMBER_ID'],$_keyword,'【失敗】'.$_params['STATUS']);
      }
    }
  }
  private function processFacebookQueue($_params){
    $_member =  Doctrine::getTable('Member')->find($_params['MEMBER_ID']);
    $_status = mb_substr($_params['STATUS'],0,140,'UTF-8');

    $appapikey = 'API';
    $appsecret = 'APISECRET';
    $facebook = new Facebook($appapikey, $appsecret);
    try{
      $facebook->api_client->session_key = $_member->getConfig('FB_SESSION');
      $facebook->api_client->stream_publish($_status);
      $result = true;
    }catch (Exception $e) {
      print_r($e);
      return false;
    }
    return true;
  }
  private function processOpenPNEACTQueue($_params){
    echo "------- openpne act queue\n";
    $_member =  Doctrine::getTable('Member')->find($_params['MEMBER_ID']);
    $_status = mb_substr($_params['STATUS'],0,140,'UTF-8');
    $result = false;
        echo realpath('./') . "\n";
        $command = 'ruby '. realpath('./') . "/plugins/twipnePlugin/pne_activity.rb";
        $command = $command . " "
      . escapeshellarg($_member->getConfig('SNSURL_ACT'.$index)) . " "
      . escapeshellarg($_member->getConfig('SNSID_ACT'.$index)) . " "
      . escapeshellarg($_member->getConfig('SNSPASS_ACT'.$index)) . " "
      . escapeshellarg(str_replace("\n","\n",$_params['STATUS']));

    //echo $command . "\n";
    $line =  exec($command);
    //echo $line . "\n";
    if($line == 'OK'){
      $result = true;
    }
    return $result;
  }
  private function processAmebaNowQueue($_params){
    $_member =  Doctrine::getTable('Member')->find($_params['MEMBER_ID']);
    $_mediaurl = $this->post2twitpic($_params['TMP_NAME'],$_member->getConfig('TWITTERNAME'),$_member->getConfig('TWITTERPASS'));
    $result = false;
      echo realpath('./') . "\n";
      $command = 'ruby '. realpath('./') . "/plugins/twipnePlugin/now_ameba_jp.rb";
      $command = $command . " "
    . escapeshellarg($_member->getConfig('AMEBAID')) . " "
    . escapeshellarg($_member->getConfig('AMEBAPASS')) . " "
    . escapeshellarg(str_replace("\n","\n",$_params['STATUS']));

      //echo $command . "\n";
      $line =  exec($command);
      //echo $line . "\n";
      if($line == 'OK'){
        $result = true;
      }
    return $result;
  }
  private function getTitleAndBody($text,$member){
 /*
    $member = Doctrine::getTable('Member')->find(2);
    $separator_keywords = $member->getConfig('SEPALATOR_KEYWORDS');
    print_r(explode(" ",$separator_keywords));
    print_r($separator_keywords);
*/
      error_log($text . "::::::\n", 3, "/tmp/aaaaaaa");
      if($member->getConfig('SEPARATOR_LF') == "1"){
        $titleandbody = explode("\n",$text,2);
        if(count($titleandbody)==2){
          return $titleandbody;
        }
      }
      $separator = explode("",$member->getConfig('SEPARATOR_KEYWORDS'));
      $pattern = '[' . preg_quote($separator) . ']';
      mb_regex_encoding('UTF-8');
      $result = mb_split($pattern,$text,1);
      if(count($result) == 2){
        return $result;
      }else{
        return array($text,'');
      }
  }
  private function post2twitpic($filepath = null,$twitterid = null,$twitterpass = null){
    if(is_null($filepath)){
      return null;
    }
    $_cmd = 'curl -F media=@'.$filepath.' -F username='.$twitterid.' -F password='.$twitterpass.' http://twitpic.com/api/upload | grep mediaurl';
    $_result = exec($_cmd);
    //echo $_result . "\n";
    if(preg_match('/<mediaurl>(.*?)<\/mediaurl>/',$_result,$_match)){
      $_mediaurl = $_match[1];
    }else{
      $_mediaurl = null;
    } 
    return $_mediaurl;
  }
  private function processMixiVoiceQueue($_params){
    $_params = unserialize($message['body']);
    $_member =  Doctrine::getTable('Member')->find($_params['MEMBER_ID']);

    $_mediaurl = $this->post2twitpic($_params['TMP_NAME'],$_member->getConfig('TWITTERNAME'),$_member->getConfig('TWITTERPASS'));
    if($_mediaurl){
      $_status = mb_substr($_params['STATUS'],0,113,'UTF-8') . '  ' . $_mediaurl;
    }else{
      $_status = mb_substr($_params['STATUS'],0,140,'UTF-8');
    }

    $_member->getConfig('MIXIUSER');
    $_member->getConfig('MIXIPASS');
    $_status = $_status;

    $result = false;

    echo realpath('./') . "\n";
    $command = 'ruby '. realpath('./') . "/plugins/twipnePlugin/voice.rb";
    $command = $command . " "
      . escapeshellarg($_member->getConfig('MIXIUSER')) . " "
      . escapeshellarg($_member->getConfig('MIXIPASS')) . " " 
      . escapeshellarg($_status);

    echo "LANG=" ;
    echo  $_SERVER['LANG'] . "\n";
    system('locale');

    //echo $command . "\n";
    $line =  exec($command);
    //echo $line . "\n";
    if($line == 'OK'){
      $result = true;
    }else{
      $result = false;
    }
    if($result){
      $this->addEntry($_params['MEMBER_ID'],'pne2mixivoice','【成功】'.$_status);
    }else{
      $this->addEntry($_params['MEMBER_ID'],'pne2mixivoice','【失敗】'.$_status);
    }
    $this->sqs->deleteMessage($queue,$message['handle']);
  }
  private function processMixiQueue($_params){
    $_member =  Doctrine::getTable('Member')->find($_params['MEMBER_ID']);
    $_status = mb_substr($_params['STATUS'],0,140,'UTF-8');
    list($_title,$_body) = $this->getTitleAndBody($_status,$_member);
    $_plist = array(
     'subject'  => $_title,
     'body'     => $_body,
    );

    $_plist['user'] = $_member->getConfig('MIXIUSER');
    $_plist['pass'] = $_member->getConfig('MIXIPASS');
    $_plist['id'] = $_member->getConfig('MIXIID');
    $result = $this->addDiary_mixi($_plist);
    return $result;
  }
  private function addDiary_mixi($params = null){
    require_once 'HTTP/Request.php';

       // mixi USER infomation
    $user        = $params['user'];
    $pass        = $params['pass'];
    $id          = $params['id'];
    $subject     = $params['subject'];
    $body        = $params['body'];

     // WSSE Authentication
      $nonce       = pack('H*', sha1(md5(time().rand().posix_getpid())));
      $created     = date('Y-m-d\TH:i:s\Z');
      $digest      = base64_encode(pack('H*', sha1($nonce . $created . $pass)));
      $wsse_text   = 'UsernameToken Username="%s", PasswordDigest="%s", Nonce="%s", Created="%s"';
      $wsse_header = sprintf($wsse_text, $user, $digest, base64_encode($nonce), $created);

    // mixi POST URL
      $url         = 'http://mixi.jp/atom/diary/member_id=' . $id;
     // Post Text
       $post_data  = '<?xml version="1.0" encoding="utf-8"?>' 
       . '<entry xmlns="http://www.w3.org/2007/app">'
       . '<title>'.$subject.'</title>'
       . '<summary>'.$body.'</summary>'
       . '</entry>';


      echo $wsse_header . "\n";
      echo $post_data . "\n";
      $request = new HTTP_Request($url);
      $request->setMethod(HTTP_REQUEST_METHOD_POST);
      $request->addHeader('X-WSSE', $wsse_header);
      $request->setBody($post_data);
      if (PEAR::isError($request->sendRequest())) {
        die('request failed');
       }
      $res_code = $request->getResponseCode();
     if($res_code = '200'){
        return true;
     }else{
        return false;
     }
  }
  private function processTwitterQueue($_params){
    $_member =  Doctrine::getTable('Member')->find($_params['MEMBER_ID']);
    $_status = mb_substr($_params['STATUS'],0,140,'UTF-8');
    try{
      $nameindex = '';
      $passindex = '';
      switch($_params['INDEX']){
        case '2':
          $nameindex = 'TWITTERNAME2';
          $passindex = 'TWITTERPASS2';
          break;
        case '3':
          $nameindex = 'TWITTERNAME3';
          $passindex = 'TWITTERPASS3';
          break;
        case '1':
        default:
          $nameindex = 'TWITTERNAME';
          $passindex = 'TWITTERPASS';
          break;
      }
      if($_member->getConfig('oauth_token')){
        $consumer_key = $app['all']['twipne_config']['consumer_key'];
        $consumer_secret = $app['all']['twipne_config']['consumer_secret'];

        $to = new TwitterOAuth($consumer_key,$consumer_secret,$_member->getConfig('oauth_token'),$_member->getConfig('oauth_token_secret'));
        $req = $to->OAuthRequest('https://twitter.com/statuses/update.xml',array('status'=>$_status),'POST');
        echo $to->lastStatusCode(); 
        if($to->lastStatusCode()=='200'){
          $result = true;
        }else{
          $result = false;
        }
      }else{
        $twitter = new Zend_Service_Twitter($_member->getConfig($nameindex),$_member->getConfig($passindex));
        $response = $twitter->status->update($_status);
        $result = $response->isSuccess();
      }
    }catch(Exception $e){
      echo "twitter 失敗". $e->getMessage() ."\n";
      $result = false;
    }
    return $result;
  }
  private function processOpenPNECOMMUQueue($_params){
    $_member =  Doctrine::getTable('Member')->find($_params['MEMBER_ID']);
    echo "target sns " . $_params['URL'] ."\n" ;
    //print_r($_member);
    if(!$_member){
      print_r($_params);
      $this->sqs->deleteMessage($queue,$message['handle']);
     // exit;
      continue;
    }
    try{
      if($_params['URL'] == $_member->getConfig('SNSURL_COMMU1')){ $_id = $_member->getConfig('SNSID_COMMU1'); $_pass = $_member->getConfig('SNSPASS_COMMU1'); $_targetid = $_member->getConfig('SNSTARGETID_COMMU1');}
      $openpne = new Services_OpenPNE($_params['URL']);
      $_status = mb_substr($_params['STATUS'],0,140,'UTF-8');
      $result = false;
      echo realpath('./') . "\n";
      $command = 'ruby '. realpath('./') . "/plugins/twipnePlugin/pne_community.rb";
      $command = $command . " "
      . escapeshellarg($_params['URL']) . " "
      . escapeshellarg($_id) . " "
      . escapeshellarg($_pass) . " "
      . escapeshellarg($_targetid) . " "
      . escapeshellarg(str_replace("\n","\n",$_status));

        //echo $command . "\n";
        $line =  exec($command);
        //echo $line . "\n";
        if($line == 'OK'){
          $result = true;
        }
    }catch(Exception $e){
      $result = false;
    }
    return $result;
  }

  private function processOpenPNEQueue($_params){
    $_member =  Doctrine::getTable('Member')->find($_params['MEMBER_ID']);
    echo "target sns " . $_params['URL'] ."\n" ;
    //print_r($_member);
    if(!$_member){
      print_r($_params);
      $this->sqs->deleteMessage($queue,$message['handle']);
     // exit;
      continue;
    }
    try{
      if($_params['URL'] == $_member->getConfig('SNSURL1')){ $_id = $_member->getConfig('SNSID1'); $_pass = $_member->getConfig('SNSPASS1'); }
      if($_params['URL'] == $_member->getConfig('SNSURL2')){ $_id = $_member->getConfig('SNSID2'); $_pass = $_member->getConfig('SNSPASS2'); }
      if($_params['URL'] == $_member->getConfig('SNSURL3')){ $_id = $_member->getConfig('SNSID3'); $_pass = $_member->getConfig('SNSPASS3'); }
      if($_params['URL'] == $_member->getConfig('SNSURL4')){ $_id = $_member->getConfig('SNSID4'); $_pass = $_member->getConfig('SNSPASS4'); }
      if($_params['URL'] == $_member->getConfig('SNSURL5')){ $_id = $_member->getConfig('SNSID5'); $_pass = $_member->getConfig('SNSPASS5'); }
      if($_params['URL'] == $_member->getConfig('SNSURL6')){ $_id = $_member->getConfig('SNSID6'); $_pass = $_member->getConfig('SNSPASS6'); }
      if($_params['URL'] == $_member->getConfig('SNSURL7')){ $_id = $_member->getConfig('SNSID7'); $_pass = $_member->getConfig('SNSPASS7'); }
      if($_params['URL'] == $_member->getConfig('SNSURL8')){ $_id = $_member->getConfig('SNSID8'); $_pass = $_member->getConfig('SNSPASS8'); }
      if($_params['URL'] == $_member->getConfig('SNSURL9')){ $_id = $_member->getConfig('SNSID9'); $_pass = $_member->getConfig('SNSPASS9'); }
      if($_params['URL'] == $_member->getConfig('SNSURL10')){ $_id = $_member->getConfig('SNSID10'); $_pass = $_member->getConfig('SNSPASS10'); }
      
      $openpne = new Services_OpenPNE($_params['URL']);
      $_status = mb_substr($_params['STATUS'],0,140,'UTF-8');
      list($_title,$_body) = $this->getTitleAndBody($_status,$_member);
      $_plist = array(
       'subject'  => $_title,
       'body'     => $_body . "\n" . '<op:color code="#808080">(pne.jpから)</op:color>',
       'category' => 'pne.jp',
      );
      if($app['all']['twipne_config']['use_mechanize']){
        $result = false;
        echo realpath('./') . "\n";
        $command = 'ruby '. realpath('./') . "/plugins/twipnePlugin/pne.rb";
        $command = $command . " "
      . escapeshellarg($_params['URL']) . " "
      . escapeshellarg($_id) . " "
      . escapeshellarg($_pass) . " "
      . escapeshellarg($_plist['subject']) . " "
      . escapeshellarg(str_replace("\n","\n",$_plist['body']));

        //echo $command . "\n";
        $line =  exec($command);
        //echo $line . "\n";
        if($line == 'OK'){
          $result = true;
        }else{
          $result = false;
          $this->sqs->deleteMessage($queue,$message['handle']);
          continue;
        }
      }else{
        if (!$openpne->auth($_id,$_pass))
        {
          echo "AUTH fail\n";
          $this->sqs->deleteMessage($queue,$message['handle']);
          continue;
        }
        $openpne->addDiary($_plist);
        $result = true;
      }
    }catch(Exception $e){
      $result = false;
    }
    return $result;
  }
  private function processWordPressQueue($_params){
    $_member =  Doctrine::getTable('Member')->find($_params['MEMBER_ID']);
    try{
      $_status = mb_substr($_params['STATUS'],0,140,'UTF-8');
      list($_title,$_body) = $this->getTitleAndBody($_status,$_member);
      //$_title = '【つぶやき】' . $_title;
      $_body = $_body . '<br /><small><a href="http://twi.pne.jp">ついぴーね</a>から</small>';

      if($_params['URL'] == $_member->getConfig('WPURL1')){ $_id = $_member->getConfig('WPID1'); $_pass = $_member->getConfig('WPPASS1'); $_category = $_member->getConfig('WPCATEGORY1'); $_tag = $_member->getConfig('WPTAG1'); }
      if($_params['URL'] == $_member->getConfig('WPURL2')){ $_id = $_member->getConfig('WPID2'); $_pass = $_member->getConfig('WPPASS2'); $_category = $_member->getConfig('WPCATEGORY2'); $_tag = $_member->getConfig('WPTAG2'); }
      $_blog_params = array(
                1,
                $_id,
                $_pass,
                array("title" =>$_title,
                      "description" => $_body ,
                      "categories" => array($_category),
                      "mt_keywords" => array('ついぴーね',$_tag),
                      "dateCreated" => ''),1,);

      $client = new Zend_XmlRpc_Client($_params['URL']. "xmlrpc.php");
      $result = $client->call('metaWeblog.newPost',$_blog_params);
    }catch(Exception $e){
      echo "WordPress 失敗". $e->getMessage() ."\n";
      $result = false;
    }
    return $result;
  }
  private function addEntry($member_id,$meta,$body){
    $entry = new Entry();
    $entry->member_id = $member_id;
    $entry->meta = $meta;
    $entry->body = $body;
    $entry->save();
  }
}
