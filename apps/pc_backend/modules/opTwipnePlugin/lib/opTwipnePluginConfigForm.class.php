<?php
class opTwipnePluginConfigForm extends sfForm
{
  protected $configs = array(

//s($app['all']['twipne_config']['accesskey'],$app['all']['twipne_config']['secretaccesskey']);
    'aws_accesskey' => 'optwipneplugin_aws_accesskey',
    'aws_secret' => 'optwipneplugin_aws_secret',
    'mailprefix' => 'optwipneplugin_mailprefix',
    'pop3_host' => 'optwipneplugin_pop3_host',
    'pop3_user' => 'optwipneplugin_pop3_user',
    'pop3_pass' => 'optwipneplugin_pop3_pass',
  );
  public function configure()
  {
    $this->setWidgets(array(
      'aws_accesskey' => new sfWidgetFormInput(),
      'aws_secret' => new sfWidgetFormInput(),
      'mailprefix' => new sfWidgetFormInput(),
      'pop3_host' => new sfWidgetFormInput(),
      'pop3_user' => new sfWidgetFormInput(),
      'pop3_pass' => new sfWidgetFormInput(),
    ));
    $this->setValidators(array(
      'aws_accesskey' => new sfValidatorString(array(),array()),
      'aws_secret' => new sfValidatorString(array(),array()),
      'mailprefix' => new sfValidatorString(array(),array()),
      'pop3_host' => new sfValidatorString(array(),array()),
      'pop3_user' => new sfValidatorString(array(),array()),
      'pop3_pass' => new sfValidatorString(array(),array()),
    ));

    $this->widgetSchema->setHelp('aws_accesskey', 'AWS ACCESSKEY');
    $this->widgetSchema->setHelp('aws_secret', 'AWS SECRET');
    $this->widgetSchema->setHelp('mailprefix', 'mail address prefix');
    $this->widgetSchema->setHelp('pop3_host', 'POP3 HOST');
    $this->widgetSchema->setHelp('pop3_user', 'POP3 USER ID');
    $this->widgetSchema->setHelp('pop3_pass', 'POP3 PASSWORD');

    foreach($this->configs as $k => $v)
    {
      $config = Doctrine::getTable('SnsConfig')->retrieveByName($v);
      if($config)
      {
        $this->getWidgetSchema()->setDefault($k,$config->getValue());
      }
    }
    $this->getWidgetSchema()->setNameFormat('twipne[%s]');
  }
  public function save()
  {
    foreach($this->getValues() as $k => $v)
    {
      if(!isset($this->configs[$k]))
      {
        continue;
      }
      $config = Doctrine::getTable('SnsConfig')->retrieveByName($this->configs[$k]);
      if(!$config)
      {
        $config = new SnsConfig();
        $config->setName($this->configs[$k]);
      }
      $config->setValue($v);
      $config->save();
    }
  }
  public function validate($validator,$value,$arguments = array())
  {
    return $value;
  }
}
