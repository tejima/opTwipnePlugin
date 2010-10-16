<?php
class MemberConfigOpenPNEACTForm extends MemberConfigForm
{
  protected $category = 'OpenPNEACT';

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
