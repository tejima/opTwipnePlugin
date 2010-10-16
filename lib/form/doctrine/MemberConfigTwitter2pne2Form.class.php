<?php
class MemberConfigTwitter2pne2Form extends MemberConfigForm
{
  protected $category = 'twitter2pne2';

  public function setMemberConfigWidget($name)
  {
    $result = parent::setMemberConfigWidget($name);

    return $result;
  }

  public function validate($validator,$value)
  {
    return $value;
  }
}
