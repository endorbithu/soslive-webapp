<?php

const APIS = [
    "SOSlive" => "***REMOVED***"
    ];


const ACCESS_TOKEN_SALT = '***REMOVED***';

const TIMEZONE = 'Europe/Budapest';

const FTP = [
    '1' => ['host'=>'79.172.252.61','username'=>'soslivevideos@soslive.info','password'=>'***REMOVED***'],
    '2' => ['host'=>'79.172.252.61','username'=>'soslivevideos@soslive.info','password'=>'***REMOVED***'],
    '3' => ['host'=>'79.172.252.61','username'=>'soslivevideos@soslive.info','password'=>'***REMOVED***'],
    ];
    
    
const STREAM_SERVER_IP = ['185.51.191.156'];
    
const DB_HOST = "localhost"; // Host name
//const DB_USERNAME = "***REMOVED***"; // Mysql username
//const DB_PW = "***REMOVED***"; // Mysql password

//const DB_USERNAME = "***REMOVED***"; // Mysql username
//const DB_PW = "***REMOVED***"; // Mysql password

const DB_USERNAME = "***REMOVED***"; // Mysql username
const DB_PW = "***REMOVED***"; // Mysql password


const DB_USERNAME_FOR_UPDATE = "***REMOVED***"; // Mysql username
const DB_PW_FOR_UPDATE = "***REMOVED***"; // Mysql password

const DB_NAME = "endorbit_soslivestream"; // Database name    
const DB_PREFIX = ""; //***PLANNED FEATURE, LEAVE VALUE BLANK FOR NOW*** Prefix for all database tables
const DB_MEMBERS = DB_PREFIX . "members";
const DB_ATTEMPTS = DB_PREFIX . "loginattempts";
    
const DAY_TO_DELETE = 365 * 50;
const DELETE_FILE_WITH_POST = true;
    
$onlySos = null;

const SYSHOST = "https://soslive.endorbit.hu";
