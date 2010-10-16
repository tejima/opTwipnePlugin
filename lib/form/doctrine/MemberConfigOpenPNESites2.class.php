<?php
class MemberConfigOpenPNESites2Form extends MemberConfigForm
{
  protected $category = 'OpenPNESites2';

  public function setMemberConfigWidget($name)
  {
    $result = parent::setMemberConfigWidget($name);

    if($name === 'SNSKEYWORD2'){$this->widgetSchema['SNSKEYWORD2']->setAttributes(array('size' => 4));}
    if($name === 'SNSURL2'){$this->widgetSchema['SNSURL2']->setAttributes(array('size' => 45));}
    if($name === 'SNSID2'){$this->widgetSchema['SNSID2']->setAttributes(array('size' => 45));}
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
