<?php
require_once("lib/connector/codebase/scheduler_connector.php");
require_once("config.php");

$res = new PDO($dsn, $username, $password);

$scheduler = new JSONSchedulerConnector($res);

$scheduler->render_table("events", "id", "start_date,end_date,text");