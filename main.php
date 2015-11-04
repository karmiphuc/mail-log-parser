<?php
$DIR_QUEUE = __DIR__. "/queue/";
$DIR_DONE = __DIR__. "/processed/";

$filename = "xxxxxx";
$datafile = $DIR_QUEUE . $filename;
$hdl = fopen($datafile, 'r');
if (!$hdl) die('Data file error!');

$lines = array();
$emails = array();
$lstMsgIdToEmail = array();
$failureMsgIds = array();

while ($line = fgets($hdl)) {
    if ($pos = getFailureText($line)) {
        $email = detectEmailFromStr($line);
        if (!$email) {
            if (!isSpamMailbox($line)) continue;
            $pos_start = getDeliveryText($line);
            $failureMsgId = getFailureIdFromLine($line, $pos_start, $pos);
            if ($failureMsgId) $failureMsgIds[] = $failureMsgId;
        } else {
            $emailHash = md5($email);
            if (!isset($emails[$emailHash])) {
                $emails[$emailHash] = $email;
                $lines[] = $line;
            }
        }
        continue;
    }
    if ($pos = getStartDeliveryText($line)){
        $pos_end = getMsgidText($line);
        $failureMsgId = getFailureIdFromLine($line, $pos, $pos_end);
        $email = detectEmailFromStr($line);
        if ($failureMsgId && $email) {
            if (!isset($lstMsgIdToEmail[$failureMsgId])) $lstMsgIdToEmail[$failureMsgId] = $email;
        }
    }
}

writeData($lines, $filename.'_lines');
writeData($emails, $filename.'_emails');
writeData($failureMsgIds, $filename.'_listIds');
writeData($lstMsgIdToEmail, $filename.'_listIdEmail');

// echo '<h1>MAILBOX ERROR: EMAILS</h1>';
// echo '<pre>',var_dump($emails),'</pre>';
// echo '<h1>MAILBOX ERROR: FAILED IDS</h1>';
// echo '<pre>',var_dump($failureMsgIds),'</pre>';
// echo '<h1>MAILBOX ERROR: FAILED IDS MAPPING TO EMAIL</h1>';
// echo '<pre>',var_dump($lstMsgIdToEmail),'</pre>';

/**
 *  ----------------------------------
 */
function writeData($data, $filename){
    file_put_contents(__DIR__.'/result_'.$filename.'_seri.log',serialize($data));
    file_put_contents(__DIR__.'/result_'.$filename.'_dump.log',var_export($data, true));
}

function getFailureText($line){
    $TEXT = 'failure:';
    $fpos = strpos($line, $TEXT);
    return $fpos;
    // if (!$fpos) return FALSE;
    // $CHECK = 'mailbox_unavailable';
    // $pos = strpos($line, $CHECK, $fpos);
    // return $pos?$fpos:FALSE;
}

function isSpamMailbox($line){
    $TEXT = '_mailbox_unavailable';
    $fpos = strpos($line, $TEXT);
    return $fpos!==FALSE;
    // if (!$fpos) return FALSE;
    // $CHECK = 'mailbox_unavailable';
    // $pos = strpos($line, $CHECK, $fpos);
    // return $pos?$fpos:FALSE;
}

function getMsgidText($line){
    $TEXT = ': msg ';
    return strpos($line, $TEXT);
}

function getDeliveryText($line){
    $TEXT = ' delivery ';
    $rslt = strpos($line, $TEXT);
    return ($rslt !== FALSE)?$rslt + strlen($TEXT):FALSE;
}

function getStartDeliveryText($line){
    $TEXT = 'starting delivery ';
    $rslt = strpos($line, $TEXT);
    return ($rslt !== FALSE)?$rslt + strlen($TEXT):FALSE;
}

function detectEmailFromStr($str) {
    preg_match("/.*\ ([a-zA-Z0-9\.\_]+\@[a-zA-Z0-9\.\_\-]+)[\ \]]/",$str,$emails);
    if(count($emails)>1) return trim($emails[1]);
    preg_match("/.*\<([a-zA-Z0-9\.\_]+\@[a-zA-Z0-9\.\_\-]+)\>/",$str,$emails);
    return count($emails)>1?trim($emails[1]):FALSE;
}

function getFailureIdFromLine($line, $pos_start, $pos) {
    if ($pos_start !== FALSE && $pos !== FALSE) {
        return intval(substr($line, $pos_start, $pos-$pos_start));
    } else return false;
}

function getLatestMsgById(){

}