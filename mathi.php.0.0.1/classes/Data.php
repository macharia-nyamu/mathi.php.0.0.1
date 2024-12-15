<?php

class Data
{
    // Clean data function
    public function sanitizeData($data, $convertNewlines = true)
    {
        // Ensure input is a string
        if (!is_string($data)) 
        {
            return $data; // Return as-is if not a string
        }

        $sanitized = htmlentities(trim($data), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        // Optional newline conversion
        if ($convertNewlines) 
        {
            $sanitized = nl2br($sanitized, false); // Use `false` to avoid adding `xhtml` self-closing tags
        }
        
        return $sanitized;
    }

    // Revert sanitized data to original format
    public function revertSanitizedData($cleanData)
    {
        if (!is_string($cleanData)) 
        {
            return $cleanData; // Return as-is if not a string
        }
    
        $decoded = html_entity_decode($cleanData, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $original = preg_replace('/<br\s*\/?>/i', "\n", $decoded);
    
        return $original;
    }

    // Generate unique ID with specified length
    public function generateIdentity( $hasDelimeters = true, $configuration = [4, 4, 4])
    {
        $identityParts = [];
        $returnValue = null;
        
        foreach ($configuration as $length) 
        {
            // Generate random string of specified length
            $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
            $identityParts[] = substr(str_shuffle($characters), 0, $length);
        }

        // Join parts with '::'
        if($hasDelimeters)
        {
            $returnValue = implode('::', $identityParts);
        }
        else
        {
            $returnValue = implode('_', $identityParts);
        }

        return strtoupper($returnValue);
    }

    // Format date to relative time (e.g., "2 days ago")
    public function structureDateOutput($date)
    {
        $now    = time();
        $diff   = $now - strtotime($date);

        $units  = 
        [
            "year"      => 365 * 24 * 60 * 60,
            "month"     => 30 * 24 * 60 * 60,
            "day"       => 24 * 60 * 60,
            "hour"      => 60 * 60,
            "minute"    => 60
        ];

        foreach ($units as $unit => $value) 
        {
            if ($diff >= $value) 
            {
                $count = floor($diff / $value);
                return $count . ' ' . ($count === 1 ? $unit : $unit . 's') . ' ago';
            }
        }

        return 'just now';
    }
}