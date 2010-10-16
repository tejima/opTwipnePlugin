<?php
class MemberConfigOpenPNESites3Form extends MemberConfigForm
{
  protected $category = 'OpenPNESites3';

  public function setMemberConfigWidget($name)
  {
    $result = parent::setMemberConfigWidget($name);

    if($name === 'SNSKEYWORD3'){$this->widgetSchema['SNSKEYWORD3']->setAttributes(array('size' => 4));}
    if($name === 'SNSURL3'){$this->widgetSchema['SNSURL3']->setAttributes(array('size' => 45));}
    if($name === 'SNSID3'){$this->widgetSchema['SNSID3']->setAttributes(array('size' => 45));}
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
