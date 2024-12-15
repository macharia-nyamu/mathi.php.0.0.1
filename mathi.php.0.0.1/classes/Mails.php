<?php

class Mails extends Responses
{
    private $systemName;
    private $endpoint;
    private $accessToken;
    private $passkey;

    // constructor
    public function __construct()
    {
        parent::__construct();

        global $config;

        $this->systemName   = $config['systemName'] ?? null;
        $this->endpoint     = $config['mailEndpoint'] ?? null;
        $this->accessToken  = $config['accessToken'] ?? null;
        $this->passkey      = $config['passkey'] ?? null;
    }

    // get curl response
    public function curlResponse($data)
    {
        $hasErrors  = false;
        $response   = '';

        $ch = curl_init($this->endpoint);

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // comment this line in production
        // curl_setopt($ch, CURLOPT_CAINFO, __DIR__ . '/cacert-2024-07-02.pem');
        // comment this line in production

        $response = curl_exec($ch);
        $response = json_decode($response, true);

        if (curl_errno($ch))
        {
            $error_msg = curl_error($ch);
            curl_close($ch);
            $response   = $error_msg;
            $hasErrors  = true;
        }

        curl_close($ch);

        return 
        [
            'response'  => $response,
            'status'    => $hasErrors
        ];
    }

    // send the actual mail
    public function sendMail($recipient, $subject, $body)
    {
        $mailResponse = $this->response;

        // initialize the data
        $data = array(
            'receiver'  => $recipient,
            'subject'   => $subject,
            'body'      => $body,
            'token'     => $this->accessToken,
            'passkey'   => $this->passkey,
            'source'    => $this->systemName
        );

        $response       = $this->curlResponse($data);

        // if has errors
        if(!$response['status'])
        {
            $mailResponse['isSuccess']  = $response['response']['status'] ?? null;
            $mailResponse['showText']   = $response['response']['message'] ?? null;
        }
        else
        {
            $mailResponse['showText'] = "Internal error sending mail"."::".$response['response'];
        }

        return $mailResponse;
    }
}