<?php

// time zone
date_default_timezone_set('Africa/Nairobi');

// contents
header("Content-Type: application/json");

// primary files
require('cors.php');
require('manifest.php');

// primary classes
require('../classes/Database.php');
require('../classes/Responses.php');
require('../classes/Securities.php');
require('../classes/Validations.php');
require('../classes/Data.php');
require('../classes/Operations.php');
require('../classes/Requests.php');
require('../classes/Mails.php');

// secondary classes


// constructs
// $delete    =
// [
//     'table'     => [$tables['']],
//     'matches'   => [['', '']],
// ];

// $update    =
// [
//     'table'     => [$tables['']],
//     'set'       => [['', '']],
//     'where'     => [['', '']],
// ];

// $insert    =
// [
//     'table'         => [$tables['']],
//     'columns'       => ['', ''],
//     'values'        => ['', ''],
//     'where'         => [['', '']],
//     'primary'       => ['']
// ];

// $select    =
// [
//     'returns'   => [],
//     'table'     => [$tables['']],
//     'where'     => [['', '', ''], ['', '']],
//     'operator'  => ['AND'],
//     'logics'    => [['', ''], ['', '', '']]
// ];