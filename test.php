<?php

class Test {
  public function encrypt(){
     $userid = "123456";
     $serialize  = false; $wrap  = true;
     $iv = random_bytes(16);
     $data = 'This is a test'; // 加密字符串
     $encrypted  = Pkcs::getInstance()->encrypt($iv, $data,$serialize, $wrap);
     if ($encrypted['code'] == 200) {
         // 完成后把iv的值和$encrypted['data']的值储存，解密的过程中使用
         print_r($encrypted);exit();
     }
  }

  public function decrypt($iv,$encrypted){
      $userid = "123456";
      $serialize  = false; $wrap  = true;
      $decrypted  = Pkcs::getInstance()->decrypt($iv, $encrypted, $serialize, $wrap);
      print_r($decrypted);
  }

}

$test = new Test ();

// 加密
$test->encrypt();

//解密 iv和encrypted是已经储存好的值
$test->decrypt($iv,$encrypted);

