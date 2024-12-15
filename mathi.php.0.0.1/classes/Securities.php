<?php

class Securities
{
    private $systemKey;

    // Constructor to initialize the systemKey from the global configuration
    public function __construct()
    {
        global $config;
        $this->systemKey = hash('sha256', $config['systemKey'] ?? null, true);  
    }

    // Encryption method
    public function encryptThisString($string)
    {
        // Return null if systemKey is null
        if ($this->systemKey === null) 
        {
            return null;
        }

        $key = $this->systemKey;  // Use the systemKey from the private property
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));  // Generate a random IV

        // Encrypt the data using AES-256-CBC
        $encrypted = openssl_encrypt($string, 'aes-256-cbc', $key, 0, $iv);
        
        // Return the encrypted string along with the IV, encoded in base64
        return base64_encode($encrypted . '::' . $iv);
    }

    // Decryption method
    public function decryptString($string)
    {
        // Return null if systemKey is null
        if ($this->systemKey === null) 
        {
            return null;
        }

        $key = $this->systemKey;  // Use the systemKey from the private property
        
        // Check if the string is a valid Base64 string
        if (preg_match('/^[a-zA-Z0-9+\/=]+$/', $string)) 
        {
            // Decode the Base64 encoded string
            $decoded = base64_decode($string, true);

            if ($decoded !== false) 
            {
                // Separate the encrypted data from the IV (stored together in the string)
                list($encryptedData, $iv) = explode('::', $decoded, 2);
                
                // Decrypt the data using AES-256-CBC
                $decrypted = openssl_decrypt($encryptedData, 'aes-256-cbc', $key, 0, $iv);
                
                // Return the decrypted string
                return $decrypted;
            }
        }

        return null;
    }
}
