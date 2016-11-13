<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

use Simplon\Mysql\Mysql;


$options = getopt("t:");

if (empty($options['t'])) {
	error('Title is not set. Please use: -t title');
	return;
}

$keyword = rtrim($options['t']);
if (empty($keyword) === false) {
	if (connectDb()->fetchColumn('SELECT id FROM theme_keyword WHERE name = :name', array('name' => $keyword)) === null) {
		$data[0] = [
			'name' => $keyword,
			'created_at' => time(),
			'updated_at' => time(),
		];
		insertTable('theme_keyword',$data);
		info($keyword.' is saved');
	}else{
		error($keyword.' is allredy saved');
	}
	
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