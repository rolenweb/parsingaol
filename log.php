<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
use Simplon\Mysql\Mysql;
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>Domain log</title>

    <!-- Bootstrap -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>
    
<?php
$logs = connectDb()->fetchRowMany('
	SELECT site.domain,site.registered,site.created_at,keyword.keyword
	FROM site
	LEFT JOIN keyword
	ON site.keyword_id=keyword.id
	ORDER BY site.created_at DESC
	LIMIT 50
	');

$list_theme = connectDb()->fetchRowMany('
	SELECT theme_keyword.id,theme_keyword.name
	FROM theme_keyword
	');

$spiders = connectDb()->fetchRowMany('
	SELECT keyword.keyword,keyword.id,theme_keyword.name,spider_keyword.updated_at
	FROM spider_keyword
	LEFT JOIN keyword
	ON spider_keyword.keyword_id=keyword.id
	LEFT JOIN theme_keyword
	ON spider_keyword.theme_keyword_id=theme_keyword.id
	
	');

?>
	<div class="container">
		<div class="row">
			<div class="col-sm-12">
				<div class="dropdown">
					<button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
				    	Theme
				    	<span class="caret"></span>
					</button>
					<ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
						<li><a href="index.php">Home</a></li>
						<li role="separator" class="divider"></li>
						<?php foreach ($list_theme as $theme): ?>
				    	<li><a href="index.php?theme=<?= $theme['id'] ?>"><?= $theme['name'] ?></a></li>
				    	<?php endforeach; ?>
				    	<li role="separator" class="divider"></li>
				    	<li><a href="log.php">Logs</a></li>
				    	<li role="separator" class="divider"></li>
				    	<li><a href="setting.php">Setting</a></li>
				  	</ul>
				</div>
			</div>
			<div class="col-sm-12">
				<h3>Spider position</h3>
				<table class="table table-bordered">
					<thead>
						<tr>
							<th>Theme</th>
							<th>Keys</th>
							<th>Current ID</th>
							<th>Last ID</th>
							<th>Updated</th>
						</tr>
					</thead>
					<tbody>
						<?php if (empty($spiders) === false) :?>
							<?php foreach ($spiders as $spider): ?>
							<tr>
								<td>
									<?= $spider['name'] ?>
								</td>
								<td>
									<?= $spider['keyword'] ?>
								</td>
								<td>
									<?= $spider['id'] ?>
								</td>
								<td>
									
								</td>
								<td>
									<?= date("Y-m-d H:i:s",$spider['updated_at']) ?>
								</td>
							</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
			<div class="col-sm-12">
				<h3>Last records</h3>
				<table class="table table-bordered">
					<thead>
						<tr>
							<th>Domain</th>
							<th>Keyword</th>
							<th>Registered</th>
							<th>Date</th>
						</tr>
					</thead>
					<tbody>
						<?php if (empty($logs) === false) :?>
							<?php foreach ($logs as $item): ?>
							<tr>
								<td>
									<?= $item['domain'] ?>
								</td>
								<td>
									<?= $item['keyword'] ?>
								</td>
								<td>
									<?= $item['registered'] ?>
								</td>
								<td>
									<?= date("Y-m-d H:i:s",$item['created_at']) ?>
								</td>
							</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>
		
	</div>
	

	
	</table>
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>
  </body>
</html>
<?php
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
?>