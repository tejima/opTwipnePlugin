<?php
class MemberConfigOpenPNESites8Form extends MemberConfigForm
{
  protected $category = 'OpenPNESites8';

  public function setMemberConfigWidget($name)
  {
    $result = parent::setMemberConfigWidget($name);

    if($name === 'SNSKEYWORD8'){$this->widgetSchema['SNSKEYWORD8']->setAttributes(array('size' => 4));}
    if($name === 'SNSURL8'){$this->widgetSchema['SNSURL8']->setAttributes(array('size' => 45));}
    if($name === 'SNSID8'){$this->widgetSchema['SNSID8']->setAttributes(array('size' => 45));}
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
