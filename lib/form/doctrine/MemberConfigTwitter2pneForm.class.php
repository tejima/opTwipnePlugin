<?php
class MemberConfigTwitter2pneForm extends MemberConfigForm
{
  protected $category = 'twitter2pne';

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
