<?php
require_once("lib/connector/codebase/scheduler_connector.php");
require_once("config.php");

$res = new PDO($dsn, $username, $password);

$scheduler = new JSONSchedulerConnector($res);

function delete_related($action) {
    global $scheduler;

    $status = $action->get_status();
    $type = $action->get_value("rec_type");
    $pid = $action->get_value("event_pid");
    //when series changed or deleted we need to remove all linked events
    if (($status == "deleted" || $status == "updated") && $type!="") {
        $scheduler->sql->query("DELETE FROM events_rec WHERE
        event_pid='".$scheduler->sql->escape($action->get_id())."'");
    }
    if ($status == "deleted" && $pid != 0) {
        $scheduler->sql->query("UPDATE events_rec SET rec_type='none' WHERE
        event_pid='".$scheduler->sql->escape($action->get_id())."'");
        $action->success();
    }
}
function insert_related($action) {
    $status = $action->get_status();
    $type = $action->get_value("rec_type");
    if ($status == "inserted" && $type == "none")
        $action->set_status("deleted");
}

$scheduler->event->attach("beforeProcessing", "delete_related");
$scheduler->event->attach("afterProcessing", "insert_related");


$scheduler->render_table("recurring_events", "id", "start_date,end_date,text,rec_type,event_pid,event_length");

