<?php
class MemberConfigOpenPNESites5Form extends MemberConfigForm
{
  protected $category = 'OpenPNESites5';

  public function setMemberConfigWidget($name)
  {
    $result = parent::setMemberConfigWidget($name);

    if($name === 'SNSKEYWORD5'){$this->widgetSchema['SNSKEYWORD5']->setAttributes(array('size' => 4));}
    if($name === 'SNSURL5'){$this->widgetSchema['SNSURL5']->setAttributes(array('size' => 45));}
    if($name === 'SNSID5'){$this->widgetSchema['SNSID5']->setAttributes(array('size' => 45));}
    return $result;
  }

  public function validate($validator,$value)
  {
    if($value['blog_url'] !== "")
    {
/*
      $root = opBlogPlugin::getFeedByUrl($value['blog_url']);
      if(!$root)
      {
        $error = new sfValidatorError($validator, 'URL is invalid.');
        throw new sfValidatorErrorSchema($validator,array('blog_url' => $error));
      }
*/
    }
    return $value;
  }
}
