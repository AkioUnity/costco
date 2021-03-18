<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
function decode($b) {
    $_keyStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
    $a = ""; $c = null; $f = null; $e = null; $n = null; $d = null; $m = 0;
    $b = preg_replace('/[^A-Za-z0-9\+\/\=]/i', '', $b);
    while($m < strlen($b)){
        $c = strpos($_keyStr, charAt($b, $m++));
        $f = strpos($_keyStr, charAt($b, $m++));
        $n = strpos($_keyStr, charAt($b, $m++));
        $d = strpos($_keyStr, charAt($b, $m++));
        $c = $c << 2 | $f >> 4;
        $f = ($f & 15) << 4 | $n >> 2;
        $e = ($n & 3) << 6 | $d;
        $a .= chr($c);
        if($n!=64){
            $a .= chr($f);
        }
        if($d!=64){
            $a .= chr($e);
        }
    }
    return _utf8_decode($a);
}
function _utf8_decode($b) {
    $a = ""; $c = 0;
    while ($c < strlen($b)) {
        $f = charCodeAt($b, $c);
        if($f<128){
            $a .= chr($f);
            $c++;
        }else{
            if(191<$f && $f<224){
                $c2 = charCodeAt($b, $c + 1);
                $a .= chr(($f & 31) << 6 | $c2 & 63);
                $c += 2;
            }else{
                $c2 = charCodeAt($b, $c + 1);
                $c3 = charCodeAt($b, $c + 2);
                $a .= chr(($f & 15) << 12 | ($c2 & 63) << 6 | $c3 & 63);
                $c += 3;
            }
        }
    }
    return $a;
}
function charCodeAt($str, $num) { return utf8_ord(utf8_charAt($str, $num)); }
function utf8_ord($ch) {
    $len = strlen($ch);
    if($len <= 0) return false;
    $h = ord($ch{0});
    if ($h <= 0x7F) return $h;
    if ($h < 0xC2) return false;
    if ($h <= 0xDF && $len>1) return ($h & 0x1F) <<  6 | (ord($ch{1}) & 0x3F);
    if ($h <= 0xEF && $len>2) return ($h & 0x0F) << 12 | (ord($ch{1}) & 0x3F) << 6 | (ord($ch{2}) & 0x3F);
    if ($h <= 0xF4 && $len>3) return ($h & 0x0F) << 18 | (ord($ch{1}) & 0x3F) << 12 | (ord($ch{2}) & 0x3F) << 6 | (ord($ch{3}) & 0x3F);
    return false;
}
function utf8_charAt($str, $num) { return mb_substr($str, $num, 1, 'UTF-8'); }
function charAt($str, $pos){ return $str{$pos}; }

function logFile($title, $conetnt)
{
    $fp = fopen('log.txt', 'a+');
    fwrite($fp, date('Y-m-d H:i:s') . PHP_EOL);
    fwrite($fp, $title . PHP_EOL);
    fwrite($fp, $conetnt . PHP_EOL . PHP_EOL);
    fclose($fp);
}