<?php

class Validations
{
    // Name validation
    public function isNameValid($name)
    {
        $name = trim($name);

        if (empty($name)) 
        {
            return "Name is empty.";
        }  
        elseif (!preg_match('/^[a-zA-Z\s]+$/', $name)) 
        {
            return "Invalid name. Only letters and spaces allowed.";
        } 
        else 
        {
            return true;
        }
    }

    // Email validation
    public function isEmailValid($email)
    {
        $email = trim($email);

        if (empty($email)) 
        {
            return "Email address is empty.";
        } 
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) 
        {
            return "Invalid email address.";
        } 
        else 
        {
            return true;
        }
    }

    // Phone validation
    public function isPhoneValid($phone, $length = 10, $hasPrefix = false, $prefix = null)
    {
        $phone = trim($phone);

        if (empty($phone)) 
        {
            return "Phone number is empty.";
        } 
        elseif ($hasPrefix && $prefix !== null && strpos($phone, $prefix) !== 0) 
        {
            return "Phone number must start with the prefix {$prefix}.";
        } 
        elseif (!is_numeric($phone)) 
        {
            return "Phone number must be numeric.";
        } 
        elseif (strlen($phone) !== $length) 
        {
            return "Invalid phone number length. Expected {$length} digits.";
        } 
        else 
        {
            return true;
        }
    }

    // Date validation
    public function isDateValid($date, $format = 'Y-m-d')
    {
        $d = DateTime::createFromFormat($format, $date);

        if (!$d || $d->format($format) !== $date) 
        {
            return "Invalid date format. Expected {$format}.";
        } 
        else 
        {
            return true;
        }
    }
}
