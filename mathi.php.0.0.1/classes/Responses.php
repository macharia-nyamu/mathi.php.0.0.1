<?php

class Responses
{
    public $response;

    // constructor
    public function __construct()
    {
        $this->response = 
        [
            'isSuccess'     => false,
            'payLoad'       => [],
            'showText'      => "",
            'mainSession'   => 
            [
                'clear'     => false,
                'display'   => true
            ]
        ];
    }

    // echo response values
    public function returnResponse($echoResponse) 
    {
        // Check if the 'message' key exists and contains the delimiter
        // if (isset($echoResponse['message']) && strpos($echoResponse['message'], '::') !== false) 
        // {
        //     // Split the message at the delimiter and keep only the first part
        //     $parts = explode('::', $echoResponse['message'], 2);
        //     $echoResponse['message'] = $parts[0];
        // }

        // // check if
        // $displayMessage = $echoResponse['session']['display'] ?? null;

        // if(!$displayMessage || $displayMessage === null)
        // {
        //     $echoResponse['message'] = "Internal server error";
        // }

        // // unset the display
        // unset($echoResponse['session']['display']);

        echo json_encode($echoResponse);
    }  
}