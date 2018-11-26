<?php

//   ini_set('max_execution_time', 300); //300 seconds = 5 minutes

  class db{
    //properties
    // private $dbhost = getenv('DB_HOST') ?: 'rsmartbizapi.rsmartbiz.com';
    // private $dbuser = getenv('DB_USER') ?: 'admin_rsbiz_tusr';
    // private $dbpass = getenv('DB_PASSWORD') ?: 'rSb94%75@2';
    // private $dbname = getenv('DB_NAME') ?: 'admin_rsmartbiz_test';

    //connect
    public function connect(){
      $mysql_connect_str = "mysql:host=$this->dbhost; dbname=$this->dbname";
      $dbConnection = new PDO($mysql_connect_str, $this->dbuser, $this->dbpass);
      $dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      return $dbConnection;
    }
  }