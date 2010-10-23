<?php

include(dirname(__FILE__).'/../bootstrap/Doctrine.php');
require_once dirname(__FILE__).'/../bootstrap/unit.php';

$t = new lime_test();

//setup 
//TwipneQueue::deleteQueue();




$expected  =  array(
  array (
    'MEMBER_ID' => 2,
    'BODY' => 'つ gatagatamichi',
  ),
);


$result = TwipneQueue::processGoogleCalendar();

$t->todo("カレンダーテストもちゃんと実行する");
//$t->ok($result,'null でない値が返る');
//$t->is($result,$expected,'狙ったとおりの形で配列が返ってくること');

$result = null;
$expected = null;


$t->todo("繰り返し予定は、ひとつだけ済みになるように");

//$t->ok(TwipneQueue::deleteQueue(),'キューをクリアできるか？');

$t->ok(TwipneQueue::isAWSKeysCorrect(),'AWS keys are correct.');

$t->ok(TwipneQueue::postQueue('TEST_QUEUE'),"return true");

//test
$t->is(TwipneQueue::postQueue('TEST_QUEUE'),'TEST_QUEUE','Return same string that I post.');


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
$result = TwipneQueue::processQueing(1,'あ つ ぶ がたがたがたがた');

$t->ok($result,'結果はNULLでない');
$t->is(sizeof($result),3,'キーワードが三文字なので配列も３');

//ポストされたキューの数はアテにならない
//$t->is(TwipneQueue::countQueue(),3,'Amazon SQSのキューは３');
$t->is($result,$expected,'狙ったとおりの形で配列が返ってくること');



//tear down
//TwipneQueue::deleteQueue();


?>
