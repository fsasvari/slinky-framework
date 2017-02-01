<?php

namespace Slinky\Encryption;

use RuntimeException;
use Slinky\Encryption\EncryptException;
use Slinky\Encryption\DecryptException;

class Crypt
{
	/**
     * The encryption key
     *
     * @var string
     */
    private $key;
	
	/**
     * The algorithm used for encryption
     *
     * @var string
     */
    private $cipher;
	
	/**
     * Create a new encrypter instance
     *
     * @param string $key
     * @param string $cipher
     * @return void
     *
     * @throws \RuntimeException
     */
    public function __construct($key, $cipher = 'AES-128-CBC')
    {
        $key = (string) $key;
		
        if ($this->supported($key, $cipher)) {
            $this->key = $key;
            $this->cipher = $cipher;
        } else {
            throw new RuntimeException('The only supported ciphers are AES-128-CBC and AES-256-CBC with the correct key lengths.');
        }
    }
	
	/**
     * Determine if the given key and cipher combination is valid
     *
     * @param string $key
     * @param string $cipher
     * @return bool
     */
    private function supported($key, $cipher)
    {
        $length = mb_strlen($key, '8bit');
		
        return ($cipher === 'AES-128-CBC' && $length === 16) || ($cipher === 'AES-256-CBC' && $length === 32);
    }
	
	/**
     * Encrypt the given value
     *
     * @param string $value
     * @return string
     *
     * @throws \Slinky\Encryption\EncryptException
     */
    public function encrypt($value)
    {
        $iv = str_random($this->getIvSize());
		
        $value = openssl_encrypt(serialize($value), $this->cipher, $this->key, 0, $iv);
		
        if ($value === false) {
            throw new EncryptException('Could not encrypt the data.');
        }
		
        $mac = $this->hash($iv = base64_encode($iv), $value);
		
        $json = json_encode(compact('iv', 'value', 'mac'));
		
        if (! is_string($json)) {
            throw new EncryptException('Could not encrypt the data.');
        }
		
        return base64_encode($json);
    }
	
	/**
     * Decrypt the given value
     *
     * @param string $payload
     * @return string
     *
     * @throws \Slinky\Encryption\DecryptException
     */
    public function decrypt($payload)
    {
        $payload = $this->getJsonPayload($payload);
		
        $iv = base64_decode($payload['iv']);
		
        $decrypted = openssl_decrypt($payload['value'], $this->cipher, $this->key, 0, $iv);
		
        if ($decrypted === false) {
            throw new DecryptException('Could not decrypt the data.');
        }
		
        return unserialize($decrypted);
    }
	
	/**
     * Get the IV size for the cipher
     *
     * @return int
     */
    private function getIvSize()
    {
        return 16;
    }
	
	/**
     * Create a MAC for the given value
     *
     * @param string $iv
     * @param string $value
     * @return string
     */
    private function hash($iv, $value)
    {
        return hash_hmac('sha256', $iv.$value, $this->key);
    }
	
	/**
     * Get the JSON array from the given payload
     *
     * @param string $payload
     * @return array
     *
     * @throws \Slinky\Encryption\DecryptException
     */
    protected function getJsonPayload($payload)
    {
        $payload = json_decode(base64_decode($payload), true);
        
        if (! $payload || $this->invalidPayload($payload)) {
            throw new DecryptException('The payload is invalid.');
        }
		
        if (! $this->validMac($payload)) {
            throw new DecryptException('The MAC is invalid.');
        }
		
        return $payload;
    }
	
    /**
     * Verify that the encryption payload is valid
     *
     * @param array|mixed $data
     * @return bool
     */
    protected function invalidPayload($data)
    {
        return ! is_array($data) || ! isset($data['iv']) || ! isset($data['value']) || ! isset($data['mac']);
    }
	
    /**
     * Determine if the MAC for the given payload is valid.
     *
     * @param array $payload
     * @return bool
     */
    protected function validMac(array $payload)
    {
        $bytes = random_bytes(16);
        $calcMac = hash_hmac('sha256', $this->hash($payload['iv'], $payload['value']), $bytes, true);
        return hash_equals(hash_hmac('sha256', $payload['mac'], $bytes, true), $calcMac);
    }
}
