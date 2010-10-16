<?php
class MemberConfigWordpressSitesForm extends MemberConfigForm
{
  protected $category = 'wordpressSites';

  public function setMemberConfigWidget($name)
  {
    $result = parent::setMemberConfigWidget($name);


    if($name === 'WPKEYWORD1'){$this->widgetSchema['WPKEYWORD1']->setAttributes(array('size' => 4));}
    if($name === 'WPURL1'){$this->widgetSchema['WPURL1']->setAttributes(array('size' => 45));}
    if($name === 'WPID1'){$this->widgetSchema['WPID1']->setAttributes(array('size' => 45));}

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
