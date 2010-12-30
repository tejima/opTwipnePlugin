<?php
class PNE2
{
  public static function getQueueFromSQS()
  {
    $aws_accesskey = opConfig::get('optwipneplugin_aws_accesskey',null);
    $aws_secret = opConfig::get('optwipneplugin_aws_secret',null);
    $sqs = new Zend_Service_Amazon_Sqs($aws_accesskey, $aws_secret);
    $queue_url = $sqs->create('twipne_queue');
    return $sqs->receive($queue_url,5,3);
  }
  public static function processKeyword($message_list)
  {
    foreach ($message_list as $message){
      $_params = unserialize($message['body']);
      $_keyword = $_params['KEYWORD'];

      switch($_keyword){
        case("ふ"):
          $result = self::pne2facebook($_params);
          break;
        case("あ"):
        case("く"):
        case("て"):
          $result = self::pne2openpneAct($_params);
          break;
        case("な"):
          $result = self::pne2amebaNow($_params);
          break;
        case("ぼ"):
          $result = self::pne2mixiVoice($_params);
          break;
        case("つ"):
          $result = self::pne2twitter($_params);
          break;
        case("ふ"):
        case("ふ"):
        case("ふ"):
        case("ふ"):
      }
      if($result){
        $sqs->deleteMessage($queue,$message['handle']);
        self::addEntry($_params['MEMBER_ID'],$_keyword,'【成功】'.$_params['STATUS']);
      }else{
        self::addEntry($_params['MEMBER_ID'],$_keyword,'【失敗】'.$_params['STATUS']);
      }
    }
  }
  public static function pne2facebook($_params){
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
  public static function pne2openpneAct($_params){
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
  public static function pne2amebaNow($_params){


    $_member =  Doctrine::getTable('Member')->find($_params['MEMBER_ID']);
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


  public static function getTitleAndBody($text,$member){
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
  public static function pne2mixiVoice($_params){


    $_params = unserialize($message['body']);
    $_member =  Doctrine::getTable('Member')->find($_params['MEMBER_ID']);

    $_mediaurl = self::post2twitpic($_params['TMP_NAME'],$_member->getConfig('TWITTERNAME'),$_member->getConfig('TWITTERPASS'));
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
    return $result;
  }
  public static function processMixiQueue($_params){
    $_member =  Doctrine::getTable('Member')->find($_params['MEMBER_ID']);
    $_status = mb_substr($_params['STATUS'],0,140,'UTF-8');
    list($_title,$_body) = self::getTitleAndBody($_status,$_member);
    $_plist = array(
     'subject'  => $_title,
     'body'     => $_body,
    );

    $_plist['user'] = $_member->getConfig('MIXIUSER');
    $_plist['pass'] = $_member->getConfig('MIXIPASS');
    $_plist['id'] = $_member->getConfig('MIXIID');
    $result = self::addDiary_mixi($_plist);
    return $result;
  }
  public static function addDiary_mixi($params = null){
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
       . '</entry>'  ;  //

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
  public static function pne2twitter($_params){
    $_member =  Doctrine::getTable('Member')->find($_params['MEMBER_ID']);
    $_status = mb_substr($_params['STATUS'],0,140,'UTF-8');
    try{
      if($_member->getConfig('twitter_oauth_token') && $_member->getConfig('twitter_oauth_token_secret')){
        $consumer_key = opConfig::get('op_auth_WithTwitter_plugin_awt_consumer',null);
        $consumer_secret = opConfig::get('op_auth_WithTwitter_plugin_awt_secret',null);

        $to = new TwitterOAuth($consumer_key,$consumer_secret,$_member->getConfig('twitter_oauth_token'),$_member->getConfig('twitter_oauth_token_secret'));
        $req = $to->OAuthRequest('https://twitter.com/statuses/update.xml',array('status'=>$_status),'POST');
        if($to->lastStatusCode()=='200'){
          $result = true;
        }else{
          $result = false;
        }
      }else{
        throw new Exception("EMPTY_MEMBER_SETTING");
      }
    }catch(Exception $e){
      $result = false;
    }
    return $result;
  }
  public static function processOpenPNECOMMUQueue($_params){
    $_member =  Doctrine::getTable('Member')->find($_params['MEMBER_ID']);
    echo "target sns " . $_params['URL'] ."\n" ;
    //print_r($_member);
    if(!$_member){
      print_r($_params);
      //self::sqs->deleteMessage($queue,$message['handle']);
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

  public static function processOpenPNEQueue($_params){
    $_member =  Doctrine::getTable('Member')->find($_params['MEMBER_ID']);
    echo "target sns " . $_params['URL'] ."\n" ;
    //print_r($_member);
    if(!$_member){
      return false;
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
      list($_title,$_body) = self::getTitleAndBody($_status,$_member);
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
        }
      }else{
        if (!$openpne->auth($_id,$_pass))
        {
          $result = false;
        }
        $openpne->addDiary($_plist);
        $result = true;
      }
    }catch(Exception $e){
      $result = false;
    }
    return $result;
  }
  public static function processWordPressQueue($_params){
    $_member =  Doctrine::getTable('Member')->find($_params['MEMBER_ID']);
    try{
      $_status = mb_substr($_params['STATUS'],0,140,'UTF-8');
      list($_title,$_body) = self::getTitleAndBody($_status,$_member);
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
  public static function addEntry($member_id,$meta,$body){

    $act = new ActivityData();
    $act->setMemberId($member_id);
    $act->setBody("【成功】" . $meta . $body);
    $act->setPublicFlag(ActivtyDataTable::PUBLIC_FLAG_PRIVATE);
    $act->setIsPc(true);
    $act->save();

  }
}
