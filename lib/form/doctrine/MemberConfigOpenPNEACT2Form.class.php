<?php
class MemberConfigOpenPNEACT2Form extends MemberConfigForm
{
  protected $category = 'OpenPNEACT2';

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
