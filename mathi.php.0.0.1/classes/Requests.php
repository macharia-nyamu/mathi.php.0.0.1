<?php

class Requests
{
    private $requestsResponse;
    private $requestsData;
    
    // constructor
    public function __construct() 
    {
        // responses
        $inlineResponse = new Responses();
        $this->requestsResponse = $inlineResponse->response;

        // data
        $inlineData = new Data();
        $this->requestsData = $inlineData;
    }

    private function getToken($headerData)
    {
        $returnToken    = null;
        $inlineHeader   = null;

        // Check if the Authorization header is set
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) 
        {
            $inlineHeader = $_SERVER['HTTP_AUTHORIZATION'];
        } 
        elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) 
        {
            $inlineHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        }
        elseif(isset($headerData['Authorization']))
        {
            $inlineHeader = $headerData['Authorization'];
        }

        if ($inlineHeader !== null) 
        {
            // Authorization header format: "Bearer <token>"
            $bearerParts = explode(' ', $inlineHeader);
            if (count($bearerParts) === 2 && $bearerParts[0] === 'Bearer') 
            {
                $returnToken = $bearerParts[1]; // assign the actual token
            }
        }

        return $returnToken;
    }

    // incoming data
    public function processServerRequest($structureArray = null, $method = "POST")
    {
        // check if the parameter is sent
        if($structureArray === null)
        {
            $this->requestsResponse['showText'] = 'Invalid data detected';
            $this->requestsResponse['mainSession']['display'] = false;
            return $this->requestsResponse;
        }

        // check for the incoming server method
        if ($_SERVER['REQUEST_METHOD'] !== $method) 
        {
            $this->requestsResponse['showText'] = 'Restructuring failed';
            $this->requestsResponse['mainSession']['display'] = false;
            return $this->requestsResponse;
        }

        /* check for auth data */
        $returnError    = null;
        $returnData     = [];
        $missingData    = [];
        $filesData      = [];

        $headerData     = getallheaders();
        $bearerToken    = $this->getToken($headerData);

        // collect the incoming data into a variable
        $inputContents  = file_get_contents('php://input');
        $dataContents   = json_decode($inputContents, true);

        // Check if all expected keys are present
        foreach ($structureArray as $eachEntry) 
        {
            $keyData    = $eachEntry['key'] ?? null;

            if ($keyData === null || !isset($dataContents[$keyData])) 
            {
                $missingData[]  = $keyData;
            }
        }

        // If any keys are missing, return error
        if (!empty($missingData)) 
        {
            $this->requestsResponse['showText'] = 'Server request error. Try again later';
            $this->requestsResponse['mainSession']['display'] = false;
            return $this->requestsResponse;
        }

        // check if any key is empty
        foreach ($structureArray as $eachEntry2) 
        {
            $keyData        = $eachEntry2['key'] ?? null;
            $errorMessage   = $eachEntry2['showText'] ?? null;
            $emptyAccepted  = $eachEntry2['emptyAccepted'] ?? false;
            $sanitizeValue  = $eachEntry2['sanitizeValue'] ?? true;

            // empty checker
            if (!$emptyAccepted && empty($dataContents[$keyData]))
            {
                $returnError = $errorMessage;
                break;
            }  

            // Sanitize the data
            $returnData[$keyData] = $sanitizeValue? $this->requestsData->sanitizeData($dataContents[$keyData]) : $dataContents[$keyData];
        }

        if($returnError !== null)
        {
            $this->requestsResponse['showText'] = $returnError;
            return $this->requestsResponse;
        }

        // Return success data with sanitized values
        $headersContents = 
        [
            'requestAddress' => !empty($_SERVER['REMOTE_ADDR']) ? $this->requestsData->sanitizeData($_SERVER['REMOTE_ADDR']) : null,
            'requestAgent'   => !empty($_SERVER['HTTP_USER_AGENT']) ? $this->requestsData->sanitizeData($_SERVER['HTTP_USER_AGENT']) : null,
            'requestStamp'   => !empty($_SERVER['REQUEST_TIME']) ? $this->requestsData->sanitizeData($_SERVER['REQUEST_TIME']) : null
        ];

        // check for files
        if (!empty($_FILES)) 
        {
            foreach ($_FILES as $key => $file) 
            {
                $filesData[$key] = $file;
            }
        }

        // prepare the return data
        $this->requestsResponse['isSuccess']            = true;
        $this->requestsResponse['showText']             = "Success";
        $this->requestsResponse['payLoad']['server']    = $returnData;
        $this->requestsResponse['payLoad']['auth']      = $bearerToken;
        $this->requestsResponse['payLoad']['files']     = $filesData;
        $this->requestsResponse['payLoad']['headers']   = $headersContents;
        return $this->requestsResponse;
    }  
}