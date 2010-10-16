<?php
class MemberConfigOpenPNESites9Form extends MemberConfigForm
{
  protected $category = 'OpenPNESites9';

  public function setMemberConfigWidget($name)
  {
    $result = parent::setMemberConfigWidget($name);

    if($name === 'SNSKEYWORD9'){$this->widgetSchema['SNSKEYWORD9']->setAttributes(array('size' => 4));}
    if($name === 'SNSURL9'){$this->widgetSchema['SNSURL9']->setAttributes(array('size' => 45));}
    if($name === 'SNSID9'){$this->widgetSchema['SNSID9']->setAttributes(array('size' => 45));}
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
