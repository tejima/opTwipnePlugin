<?php
class MemberConfigPne2twitter2Form extends MemberConfigForm
{
  protected $category = 'pne2twitter2';

  public function setMemberConfigWidget($name)
  {
    $result = parent::setMemberConfigWidget($name);

    if($name === 'blog_url')
    {
      $this->widgetSchema['blog_url']->setAttributes(array('size' => 40));
      $this->mergePostValidator(new sfValidatorCallback(array(
        'callback' => array($this, 'validate'),
      )));
    }
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
