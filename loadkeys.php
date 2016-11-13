<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

$path_load_file = __DIR__ . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'load' . DIRECTORY_SEPARATOR;

use Elkuku\Console\Helper\ConsoleProgressBar;
use Simplon\Mysql\Mysql;




if (empty($themes)) {
	error('Themes are not found. Please run script theme.php');
}else{
	info('There are themes:');
	foreach ($themes as $theme) {
		info($theme['name'].': '.$theme['id']);
	}
}
$options = getopt("t:f:");

if (empty($options['t'])) {
	error('The theme is null. Please use key: -t');
	return;
}

if (empty($options['f'])) {
	error('The name of folder is null. Please use key: -f');
	return;
}


$theme_id = rtrim($options['t']);

$themes = connectDb()->fetchRow('SELECT * FROM theme_keyword WHERE id = :id',[':id' => $theme_id]);

if (empty($themes)) {
	error('The theme is not found');
	return;
}
info('Load keys for '.$themes['name']);

$folder = rtrim($options['f']);

if (file_exists($path_load_file.$folder.DIRECTORY_SEPARATOR) === false) {
	error($path_load_file.$folder.DIRECTORY_SEPARATOR.' is not exists');
}else{
	$files = scandir($path_load_file.$folder);
	info('Found '.count($files). ' files');
	$bar_files_max = count($files) -1;
	$bar_files = new ConsoleProgressBar('Files %fraction% [%bar%] %percent%', '=>', '-', 100, $bar_files_max);
	$total_keys = 0;
	foreach ($files as $n_file => $file) {
		$keywords = file($path_load_file.$folder.DIRECTORY_SEPARATOR.$file);		
		//info('Load file: '.$file.': '.count($keywords).' keywords');
		$bar_keywords_max = count($keywords) - 1;
		$bar_keywords = new ConsoleProgressBar('Keyword %fraction% [%bar%] %percent%', '=>', '-', 100, $bar_keywords_max);
		
		if (empty($keywords) === false) {
			foreach ($keywords as $n_key => $keyword) {
				if (connectDb()->fetchColumn('SELECT id FROM keyword WHERE keyword = :keyword', array('keyword' => rtrim($keyword))) === null) {
					$data[0] = [
						'keyword' => rtrim($keyword),
						'theme_keyword_id' => $theme_id,
						'created_at' => time(),
						'updated_at' => time(),
					];
					insertTable('keyword',$data);
					$total_keys += count($keywords);
				}
				$bar_keywords->update($n_key);
			}
		}
		$bar_files->update($n_file);
	}
}
info('Loaded '.$total_keys.' keys');



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
?>