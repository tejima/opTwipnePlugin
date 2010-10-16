<?php
class MemberConfigOpenPNESites7Form extends MemberConfigForm
{
  protected $category = 'OpenPNESites7';

  public function setMemberConfigWidget($name)
  {
    $result = parent::setMemberConfigWidget($name);

    if($name === 'SNSKEYWORD7'){$this->widgetSchema['SNSKEYWORD7']->setAttributes(array('size' => 4));}
    if($name === 'SNSURL7'){$this->widgetSchema['SNSURL7']->setAttributes(array('size' => 45));}
    if($name === 'SNSID7'){$this->widgetSchema['SNSID7']->setAttributes(array('size' => 45));}
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
