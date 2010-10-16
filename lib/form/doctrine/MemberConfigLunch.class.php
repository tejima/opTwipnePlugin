<?php
class MemberConfigLunchForm extends MemberConfigForm
{
  protected $category = 'lunch';

  public function setMemberConfigWidget($name)
  {
    $result = parent::setMemberConfigWidget($name);

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
