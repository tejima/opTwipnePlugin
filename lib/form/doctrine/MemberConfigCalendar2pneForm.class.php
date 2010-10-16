<?php
class MemberConfigCalendar2pneForm extends MemberConfigForm
{
  protected $category = 'calendar2pne';

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
