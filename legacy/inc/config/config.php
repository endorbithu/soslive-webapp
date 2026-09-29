<?php

const APIS = [
    "SOSlive" => "DdZCkKePs19G6rIoMXqBltDQ9oBpgGINgNZC9CSXk6nsZCZCRV1ZBQKzp3ZA28ANrTrReOglWpGZ7KLtyCgb9NiwawaQ1ZBii1aE72LIddowZDZDJBEpW0NXSCFeXHZA"
    ];


const ACCESS_TOKEN_SALT = '32p4rifgjdfgo324i23jrigf';

const TIMEZONE = 'Europe/Budapest';

const FTP = [
    '1' => ['host'=>'79.172.252.61','username'=>'soslivevideos@soslive.info','password'=>'qweqwrwwesdfxferrdfgtrzzjjklfsdfsfewfwesdf'],
    '2' => ['host'=>'79.172.252.61','username'=>'soslivevideos@soslive.info','password'=>'qweqwrwwesdfxferrdfgtrzzjjklfsdfsfewfwesdf'],
    '3' => ['host'=>'79.172.252.61','username'=>'soslivevideos@soslive.info','password'=>'qweqwrwwesdfxferrdfgtrzzjjklfsdfsfewfwesdf'],
    ];
    
    
const STREAM_SERVER_IP = ['185.51.191.156'];
    
const DB_HOST = "localhost"; // Host name
//const DB_USERNAME = "postamos_soslive"; // Mysql username
//const DB_PW = "sk7ujsa7363HZTTDFhsgJ89998kssh98"; // Mysql password

//const DB_USERNAME = "postamos_sosread"; // Mysql username
//const DB_PW = "yKpc;-oj4wxM"; // Mysql password

const DB_USERNAME = "endorbit__general"; // Mysql username
const DB_PW = "Almafa2012!"; // Mysql password


const DB_USERNAME_FOR_UPDATE = "endorbit__general"; // Mysql username
const DB_PW_FOR_UPDATE = "Almafa2012!"; // Mysql password

const DB_NAME = "endorbit_soslivestream"; // Database name    
const DB_PREFIX = ""; //***PLANNED FEATURE, LEAVE VALUE BLANK FOR NOW*** Prefix for all database tables
const DB_MEMBERS = DB_PREFIX . "members";
const DB_ATTEMPTS = DB_PREFIX . "loginattempts";
    
const DAY_TO_DELETE = 365 * 50;
const DELETE_FILE_WITH_POST = true;
    
$onlySos = null;

const SYSHOST = "https://soslive.endorbit.hu";
