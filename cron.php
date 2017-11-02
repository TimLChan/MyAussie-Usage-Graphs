<?php
include_once './libs/HTMLTable2JSON.php';
$outfile = '';
$helper = new HTMLTable2JSON();
$username = ''; //Enter your MyAussie username here
$password = ''; //Enter your MyAussie password here

$headers = array(
    "Content-Type: application/x-www-form-urlencoded",
"User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.100 Safari/537.36",
"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8",
"Referer: https://www.aussiebroadband.com.au/login/",
"Host: my.aussiebroadband.com.au",
);



$data = _fetch('https://my.aussiebroadband.com.au/', $headers, false, '', false);

$data = _fetch('https://my.aussiebroadband.com.au/', $headers, true, 'login_username=' . $username . '&login_password=' . urlencode($password), true);
$headers = array(
"Referer: https://my.aussiebroadband.com.au/",
"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8",
"Host: my.aussiebroadband.com.au",
"User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.100 Safari/537.36"
);
$data = _fetch('https://my.aussiebroadband.com.au/usage.php', $headers, false, '', true);


$output = ($helper->tableToJSON('http://nothingatall/', false, null, null, null, false, null, true, null, false, $data));

//Clean Data
$output = str_replace(" MB", "", $output);
//AussieBB actually don't have complete tables... wtf
$output = str_replace('{"Column1":"<strong>Data Left</strong>"','{"Column0":"<strong>Data Left</strong>"',$output);
$output = str_replace('"<strong>Data Left</strong>", "Column2"','"<strong>Data Left</strong>", "Column1"',$output);
        

//Get current month
$dt = DateTime::createFromFormat('!m', substr($output, 16, 2));
$month = $dt->format('F');
$outfile = './data/'. $month . "_data.json";

//save to archives
if (false == ($out_handle = fopen($outfile, 'w'))) {
    die('Failed to create output file.');
}
fwrite($out_handle, $output);
fclose($out_handle);

//save to latest
if (false == ($out_handle = fopen('./data/latest.json', 'w'))) {
    die('Failed to create output file.');
}
fwrite($out_handle, $output);
fclose($out_handle);




function _fetch($url, $headers = array(), $shouldPost = false, $post = '', $useCookies = true)
{
    $ch = curl_init($url);
    $cookie_file = realpath('aussie.bbinternet');
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
    if ($useCookies) {
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    if ($shouldPost) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    }
    
    if (count($headers) > 0) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    
    $data = curl_exec($ch);
    return $data;
}

