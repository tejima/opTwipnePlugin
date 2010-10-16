<?php
class MemberConfigAmebaForm extends MemberConfigForm
{
  protected $category = 'Ameba';

  public function setMemberConfigWidget($name)
  {
    $result = parent::setMemberConfigWidget($name);

    if($name === 'SNSKEYWORD1'){$this->widgetSchema['SNSKEYWORD1']->setAttributes(array('size' => 4));}
    if($name === 'SNSURL1'){$this->widgetSchema['SNSURL1']->setAttributes(array('size' => 45));}
    if($name === 'SNSID1'){$this->widgetSchema['SNSID1']->setAttributes(array('size' => 45));}
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
