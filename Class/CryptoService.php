<?php
/*
 * Encrypts and decrypts data
 */
class CryptoService
{
    private $privateKey;

    /**
     * Sets the private key location
     * @param string $privateKeyLocation
     * @return void
     */
    public function setPrivateKeyLocation($privateKeyLocation) {
        $this->privateKey = file_get_contents($privateKeyLocation);
    }

    /**
     * @param string $input
     * @return string
     */
    public function encryptData($input = "") {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt($input, 'aes-256-cbc', $this->privateKey, 0, $iv);
        return base64_encode($encrypted . '::' . $iv);
    }

    /**
     * @param string $input
     * @return false|string
     */
    public function decryptData($input = "") {
        list($encrypted_data, $iv) = explode('::', base64_decode($input), 2);
        return openssl_decrypt($encrypted_data, 'aes-256-cbc', $this->privateKey, 0, $iv);
    }

    /**
     * @return string
     */
    public function generateUUID() {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * @return string
     */
   public function generatePassword() {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }

    /**
     * @param string $content
     * @param string $privatekey
     * @return string
     */
    public function signData($content, $privatekey) {

        $privateKeyId = openssl_pkey_get_private($privatekey);

        openssl_sign($content, $signature, $privateKeyId, 'RSA-SHA256');

        $base64Str = base64_encode($signature);

        return $base64Str;

    }

    /**
     * @param string $content
     * @param string $signature
     * @param string $publickey
     * @return bool
     */
    public function verifySignature($content, $signature, $publickey) {

        $publicKeyId = openssl_pkey_get_public($publickey);

        $result = openssl_verify($content, base64_decode($signature), $publicKeyId, 'RSA-SHA256');

        return $result == 1;
    }

    /**
     * @return array
     */
    public function generateKeyPair() {
        $keys = openssl_pkey_new(array("private_key_bits" => 4096,"private_key_type" => OPENSSL_KEYTYPE_RSA));
        $public_key_pem = openssl_pkey_get_details($keys)['key'];
        openssl_pkey_export($keys, $private_key_pem);
        return array("private_key" => $private_key_pem, "public_key" => $public_key_pem);
    }
}
