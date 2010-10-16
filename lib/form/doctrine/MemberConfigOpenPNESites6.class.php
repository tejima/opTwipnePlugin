<?php
class MemberConfigOpenPNESites6Form extends MemberConfigForm
{
  protected $category = 'OpenPNESites6';

  public function setMemberConfigWidget($name)
  {
    $result = parent::setMemberConfigWidget($name);

    if($name === 'SNSKEYWORD6'){$this->widgetSchema['SNSKEYWORD6']->setAttributes(array('size' => 4));}
    if($name === 'SNSURL6'){$this->widgetSchema['SNSURL6']->setAttributes(array('size' => 45));}
    if($name === 'SNSID6'){$this->widgetSchema['SNSID6']->setAttributes(array('size' => 45));}
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
