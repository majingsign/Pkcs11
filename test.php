<?php
 public function encrypt(){
    $userid = "123456";
    $serialize  = false; $wrap  = true;
    $iv = random_bytes(16);
    $data = 'This is a test'; // 加密字符串
    $encrypted  = Hsm::getInstance()->encrypt($iv, $data,$serialize, $wrap);
    if ($encrypted['code'] == 200) {
        print_r($encrypted);exit();
    }
 }

  public function decrypt(){
      $userid = "123456";
      $serialize  = false; $wrap  = true;
      $decrypted  = Hsm::getInstance()->decrypt('iv', 'encrypted', $serialize, $wrap);
      print_r($decrypted);
  }



