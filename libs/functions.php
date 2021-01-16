<?php

//エスケープ
function e($txt) {
  if(is_array($txt)) {
    return array_map('e', $txt);
  } else {
    return htmlspecialchars($txt, ENT_QUOTES | ENT_HTML5, 'UTF-8');
  }
}


function checkInput($txt) {

  if(is_array($txt)) {
    return array_map('checkInput', $txt);
  } else {

    //nullバイト攻撃
    if(preg_match('/\0/', $txt)) {
      die('不正な入力です。');
    }
    //文字エンコード
    if(!mb_check_encoding($txt, 'UTF-8')) {
      die('不正な入力です。');
    }
    //制御文字チェック
    if(preg_match('/\A[\r\n\t[:^cntrl:]]*\z/u', $txt) === 0){  
      die('不正な入力です。制御文字は使用できません。');
    }

    return $txt;

  }
}



