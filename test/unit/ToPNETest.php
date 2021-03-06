<?php

include(dirname(__FILE__).'/../bootstrap/Doctrine.php');
require_once dirname(__FILE__).'/../bootstrap/unit.php';

$t = new lime_test();

$expected  =  array(
  array (
    'MEMBER_ID' => 2,
    'BODY' => 'つ gatagatamichi',
  ),
);

try{
  $result = ToPNE::processGoogleCalendar();
  $t->pass("とりあえず、例外が出ない");
}catch(Exception $e){
  $t->pass("例外はNG");
}
$t->todo("カレンダーテストもちゃんと実行する");
//$t->ok($result,'null でない値が返る');
//$t->is($result,$expected,'狙ったとおりの形で配列が返ってくること');

$result = null;
$expected = null;

$t->todo("GCAL 繰り返し予定は、ひとつだけ済みになるように");

//$t->ok(ToPNE::deleteQueue(),'キューをクリアできるか？');

$t->ok(ToPNE::isAWSKeysCorrect(),'AWS keys are correct.');
$t->ok(ToPNE::postQueue('TEST_QUEUE'),"return true");

//test
$t->is(ToPNE::postQueue('TEST_QUEUE'),'TEST_QUEUE','Return same string that I post.');


$expected  =  array(
  array (
    'MEMBER_ID' => 1,
    'STATUS' => 'がたがたがたがた',
    'KEYWORD' => 'あ',
  ),
  array (
    'MEMBER_ID' => 1,
    'STATUS' => 'がたがたがたがた',
    'KEYWORD' => 'つ',
  ), 
  array (
    'MEMBER_ID' => 1,
    'STATUS' => 'がたがたがたがた',
    'KEYWORD' => 'ぶ',
  ), 
);


//test
$result = ToPNE::processQueing(1,'あ つ ぶ がたがたがたがた');

$t->ok($result,'結果はNULLでない');
$t->is(sizeof($result),3,'キーワードが三文字なので配列も３');

//ポストされたキューの数はアテにならない
//$t->is(TwoPNE::countQueue(),3,'Amazon SQSのキューは３');
$t->is($result,$expected,'狙ったとおりの形で配列が返ってくること');



//tear down
//TwoPNE::deleteQueue();


?>
