<?php

namespace App\Http\Controllers;

use RuntimeException;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\Encryption\EncryptException;
use Illuminate\Contracts\Encryption\Encrypter as EncrypterContract;

class AesController extends Controller
{
    public $iv = 'yZQWSKxrM2bg3dDl';
    public $key = 'q96kf2xXnHOgp1AJ';
    private $cipher;

    public function __construct($cipher = 'AES-128-CBC')
    {
        if (static::supported($this->key, $cipher)) {
            $this->cipher = $cipher;
        } else {
            throw new RuntimeException('The only supported ciphers are AES-128-CBC and AES-256-CBC with the correct key lengths.');
        }
    }

    public function encrypt($value, $serialize = false)
    {

        // First we will encrypt the value using OpenSSL. After this is encrypted we
        // will proceed to calculating a MAC for the encrypted value so that this
        // value can be verified later as not having been changed by the users.
        $value = \openssl_encrypt(
            $serialize ? serialize($value) : $value,
            $this->cipher, $this->key, 0, $this->iv
        );

        if ($value === false) {
            throw new EncryptException('Could not encrypt the data.');
        }

        return (string) $value;
    }

    public static function supported($key, $cipher)
    {
        $length = mb_strlen($key, '8bit');

        return ($cipher === 'AES-128-CBC' && $length === 16) ||
            ($cipher === 'AES-256-CBC' && $length === 32);
    }
}
