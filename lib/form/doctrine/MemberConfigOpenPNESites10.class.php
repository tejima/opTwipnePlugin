<?php
class MemberConfigOpenPNESites10Form extends MemberConfigForm
{
  protected $category = 'OpenPNESites10';

  public function setMemberConfigWidget($name)
  {
    $result = parent::setMemberConfigWidget($name);

    if($name === 'SNSKEYWORD10'){$this->widgetSchema['SNSKEYWORD10']->setAttributes(array('size' => 4));}
    if($name === 'SNSURL10'){$this->widgetSchema['SNSURL10']->setAttributes(array('size' => 45));}
    if($name === 'SNSID10'){$this->widgetSchema['SNSID10']->setAttributes(array('size' => 45));}
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
