<?php
class MemberConfigOpenPNESites4Form extends MemberConfigForm
{
  protected $category = 'OpenPNESites4';

  public function setMemberConfigWidget($name)
  {
    $result = parent::setMemberConfigWidget($name);

    if($name === 'SNSKEYWORD4'){$this->widgetSchema['SNSKEYWORD4']->setAttributes(array('size' => 4));}
    if($name === 'SNSURL4'){$this->widgetSchema['SNSURL4']->setAttributes(array('size' => 45));}
    if($name === 'SNSID4'){$this->widgetSchema['SNSID4']->setAttributes(array('size' => 45));}
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
