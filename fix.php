<?php
$c = file_get_contents('public/assets/css/style.css');
$c = str_replace("\0", "", $c);
$c = preg_replace('/[^\x20-\x7E\t\r\n]/', '', $c);
// The regex above will strip characters outside ASCII printables. 
// However, maybe there are valid non-ascii characters? Just in case, let's just strip \0
file_put_contents('public/assets/css/style.css', $c);
echo "Done";
?>
