<?php
class MemberConfigWordpressSites2Form extends MemberConfigForm
{
  protected $category = 'wordpressSites2';

  public function setMemberConfigWidget($name)
  {
    $result = parent::setMemberConfigWidget($name);


    if($name === 'WPKEYWORD2'){$this->widgetSchema['WPKEYWORD2']->setAttributes(array('size' => 4));}
    if($name === 'WPURL2'){$this->widgetSchema['WPURL2']->setAttributes(array('size' => 45));}
    if($name === 'WPID2'){$this->widgetSchema['WPID2']->setAttributes(array('size' => 45));}

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
