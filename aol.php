<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'CurlClient.php';

use Elkuku\Console\Helper\ConsoleProgressBar;
use phpWhois\Whois;
use Simplon\Mysql\Mysql;
use Goutte\Client;
use tools\CurlClient;

$options = getopt("t:");

if (empty($options['t'])) {
	error('The theme is null. Please use key: -t');
	return;
}

$theme_id = rtrim($options['t']);

$themes = connectDb()->fetchRow('SELECT * FROM theme_keyword WHERE id = :id',[':id' => $theme_id]);

if (empty($themes)) {
	error('The theme is not found');
	return;
}

$theme = rtrim($options['t']);

if (empty($theme)) {
	error('Theme is not set');
	return;
}
for (;;) {
	$start_time = time();
	$keyword = getKeyword($theme);
	if (empty($keyword)) {
		error('Keyword is not set');
	}else{
		info('Parsing keyword: '.$keyword['keyword']);	
		$urls = parsingKeyword($keyword['keyword']);
		if (empty($urls) === false) {
			$bar = new ConsoleProgressBar('Keyword %fraction% [%bar%] %percent%', '=>', '-', 100, count($urls));
			foreach ($urls as $n => $url) {
				$n_link = $n+1;
				analysisUrl($url,$keyword);
				$bar->update($n_link);
			}
			
		}else{
			error('There are not links');
		}
	}
	changeSpiner($keyword);	
	$end_time = time();
	$dif_time = $end_time-$start_time;
	if ($dif_time < 3) {
		info('sleep: 5 sec');
		sleep(5);
	}
	newLine();
}


//database
function connectDb()
{
	require 'config/db.php';
	
	return new Mysql(
	    $config['host'],
	    $config['user'],
	    $config['password'],
	    $config['database']
	);
}

function insertTable($table,$data)
{
	return connectDb()->insertMany($table, $data);	
}
function updateTable($table, $condr, $data)
{
	connectDb()->update($table, $condr, $data);	
}
//database

function chooseTheme()
{
	$themes = connectDb()->fetchRowMany('SELECT * FROM theme_keyword');

	if (empty($themes)) {
		error('Themes are not found. Please run script theme.php');
	}else{
		info('There are themes:');
		foreach ($themes as $theme) {
			info($theme['name'].': '.$theme['id']);
		}
	}
	$line = readline("Choose the theme: ");
	readline_add_history($line);
	$theme_id = rtrim(readline_info()['line_buffer']);
	return $theme_id;
}

function getKeyword($theme)
{
	$spider = connectDb()->fetchRow('SELECT * FROM spider_keyword WHERE theme_keyword_id =:theme',[':theme' => $theme]);

	if (empty($spider)) {
		return connectDb()->fetchRow('SELECT * FROM keyword WHERE theme_keyword_id =:theme',[':theme' => $theme]);
	}else{
		return connectDb()->fetchRow('SELECT * FROM keyword WHERE theme_keyword_id =:theme and id = :id',[':theme' => $theme,':id' => $spider['keyword_id']]);
	}

}

function changeSpiner($keyword)
{
	$next_keyword = connectDb()->fetchRow('SELECT * FROM keyword WHERE theme_keyword_id =:theme and id > :id',[':theme' => $keyword['theme_keyword_id'],':id' => $keyword['id']]);
	if (empty($next_keyword)) {
		info('List of keys is finished');
		die;
	}
	$condr = [
		'theme_keyword_id' => $keyword['theme_keyword_id'],		
	];
	$data = [
		'keyword_id' => $next_keyword['id'],
		'updated_at' => time(),
	];
	updateTable('spider_keyword', $condr, $data);
}

function parsingKeyword($keyword)
{

	$url = 'http://search.aol.com/aol/search?s_it=sb-top&s_chn=prt_bon&v_t=comsearch&q='.urlencode($keyword);
	
	$content = contentAol($url);
   	
	$client = new CurlClient();

	$links = $client->parseProperty($content,'link','h3.hac a[rel = "f:url"]',$url,null);
	
	return $links;
	
}

function contentAol($url)
{
	$useragent = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.90 Safari/537.36';
    array_map('unlink', glob("cookiefile/*"));
    $ckfile = tempnam("cookiefile", "CURLCOOKIE");
    $f = fopen(__DIR__ . DIRECTORY_SEPARATOR .'cookiefile/log.txt', 'w'); 
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $ckfile);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $ckfile);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_REFERER, 'http://www.aol.com/');
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    curl_setopt($ch,CURLOPT_STDERR ,$f);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    	//"Connection: keep-alive",
    	//"Cache-Control: max-age=0",
    	"Upgrade-Insecure-Requests: 1",
    	"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8",
    	"Accept-Encoding: deflate, sdch",
    	"Accept-Language: en-US,en;q=0.8"
    	));        

    return curl_exec($ch);
}

function analysisUrl($url,$keyword)
{
	$details = parse_url($url);
	if (empty($details['host'])) {
		return;
	}else{
		$domain = str_replace('www.','',$details['host']);
		$whois = new Whois();
		$result = $whois->lookup($domain,false);
		$date = creationDate($result['rawdata']);
		$old = time()-525600*60;
		if (strtotime($date) > $old) {
			$data[0] = [
				'domain' => $domain,
				'url' => $url,
				'keyword_id' => $keyword['id'],
				'registered' => date("Y-m-d",strtotime($date)),
				'created_at' => time(),
				'updated_at' => time(),
			];
			insertTable('site',$data);
		}
	}
	return;
}

function creationDate($data)
{
	if (empty($data)) {
		return;
	}else{
		foreach ($data as $item) {
			if (stripos($item,'Creation Date:') !== false) {
				$date = trim(str_replace('Creation Date:','',$item));
				break;
			}
		}
	}
	return (empty($date) === false) ? $date : null;
}

function error($string)
{
	echo "\033[31m".$string."\033[0m".PHP_EOL;
}

function success($string)
{
	echo "\033[32m".$string."\033[0m".PHP_EOL;
}

function info($string)
{
	echo "\033[33m".$string."\033[0m".PHP_EOL;
}
function newLine()
{
	echo PHP_EOL;
}
?>