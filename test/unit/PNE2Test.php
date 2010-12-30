<?php

include(dirname(__FILE__).'/../bootstrap/Doctrine.php');
require_once dirname(__FILE__).'/../bootstrap/unit.php';

$t = new lime_test();
$message_arr =
  array (
    'MEMBER_ID' => 2,
    'STATUS' => 'gatagatamichi',
    'KEYWORD' => 'つ',
  );

$t->todo("すでに入っているキューをカラにする");
$t->todo("テスト用のキューをセットする");

try{
  $result = PNE2::pne2twitter($message_arr);
  $t->todo("Twitter設定済み、未設定２つのテストメンバーを自動的につくる");

  $t->pass("このテストメンバーは例外が発生しない");
  $t->ok($result,"正常に投稿できた場合、戻り値はtrue");

}catch(Exception $e){
  $t->fail("このテストメンバーは例外が発生してはならない");
}


$message_arr =
  array (
    'MEMBER_ID' => 2,
    'STATUS' => 'gatagatamichi',
    'KEYWORD' => 'つ',
  );

try{
  $result = PNE2::pne2twitter($message_arr);
  $t->fail("このテストメンバーは例外が発生しなければならない");
}catch(Exception $e){
  $t->pass("このテストメンバーは例外が発生する");
}


//tear down
//TwipneQueue::deleteQueue();
?>
