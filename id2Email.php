<?php
$filename = "xab";
$dictfile = __DIR__.'/result_'.$filename.'_listIdEmail_seri.log';
$targetfile = __DIR__.'/result_'.$filename.'_listIds_seri.log';

function writeData($data, $filename){
    file_put_contents(__DIR__.'/result_'.$filename.'_extraemails_seri.log',serialize($data));
    file_put_contents(__DIR__.'/result_'.$filename.'_extraemails_dump.log',var_export($data, true));
}

$targets = unserialize(file_get_contents($targetfile));
$dict = unserialize(file_get_contents($dictfile));

$emails = array();
foreach ($targets as $id) {
    if (!isset($dict[$id])) continue;

    $e = $dict[$id];
    $hash = md5($e);
    if (!isset($emails[$hash])) $emails[$hash] = $e;

}
writeData($emails, $filename);