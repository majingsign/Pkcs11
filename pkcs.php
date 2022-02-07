<?php

namespace pkcs;

use Pkcs11\Key;
use Pkcs11\Module;
use Pkcs11\Session;

class Pkcs {

    private static $instance;

    protected static $module;

    protected static $path = '';

    protected $user_name   = '';

    private function __construct() {
        parent::__construct();
        $this->slot     = 1;
        $this->pin      = 123456;;
        $this->keyLabel = "aes128";
        $this->user_name= 'test:123456';
        self::$path     = '/opt/lib/libcloudhsm_pkcs11.so';
    }

    private function __clone(){}

    public function encrypt(string $iv, string $data, bool $serialize = false, bool $wrap = false) {
        try {
            $session  = $this->openSession();
            $key      = $this->getKeyFromSession($session);
            $mechanism = new \Pkcs11\Mechanism(\Pkcs11\CKM_AES_CBC_PAD, $iv);
            if ($wrap) {
                $data = base64_encode($data);
            }
            $data      = $serialize ? serialize($data) : $data;
            $encrypted = $key->encrypt($mechanism, $data);
            $encrypted = base64_encode($encrypted);
            return $this->returnJsonData(self::SUCCESS_CODE,'ok',['sign'=>$encrypted]);
        }catch (\Exception $e){
            return $this->returnJsonData(self::ERROR_CODE,$e->getMessage());
        } finally {
            $session->logout();
            unset($session);
        }
    }

    public function decrypt(string $iv, string $data, bool $serialize = false, bool $unwrap = false) {
        try {
            $session  = $this->openSession();
            $key      = $this->getKeyFromSession($session);
            $mechanism = new \Pkcs11\Mechanism(\Pkcs11\CKM_AES_CBC_PAD, hex2bin($iv));
            $ciphertext = $key->decrypt($mechanism, base64_decode($data));
            $ciphertext = $serialize ? unserialize($ciphertext) : $ciphertext;
            return $this->returnJsonData(self::SUCCESS_CODE,'ok',['text'=>$ciphertext]);
        }catch (\Exception $e){
            return $this->returnJsonData(self::ERROR_CODE,$e->getMessage());
        } finally {
            $session->logout();
            unset($session);
        }
    }

    private function getKeyFromSession(Session $session): Key {
        $objects = $session->findObjects([
            \Pkcs11\CKA_CLASS => \Pkcs11\CKO_SECRET_KEY,
            \Pkcs11\CKA_KEY_TYPE  => \Pkcs11\CKK_AES,
            \Pkcs11\CKA_VALUE_LEN => 32,
        ]);
        return reset($objects);
        //        throw new \Exception('No key found,please login using the CU user type');
    }

    protected function getMechanism(string $iv): \Pkcs11\Mechanism {
        $gcmParams = new \Pkcs11\GcmParams($iv, '', 128);
        return new \Pkcs11\Mechanism(\Pkcs11\CKM_AES_GCM, $gcmParams);
    }

    private function openSession(): Session {
        $module  = $this->getModule();
        $slotId  = $module->getSlotList()[0];
        $session = $module->openSession($slotId, \Pkcs11\CKF_RW_SESSION);
        $session->login(\Pkcs11\CKU_USER, $this->user_name);
        return $session;
    }

    public static function getInstance() {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    protected static function getModule(): Module {
        if (null === self::$module) {
            self::$module = new Module(self::$path);
        }
        return self::$module;
    }

}
