<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>PHP SSDB Admin<?php if($title){echo ' - ' . $title;} ?></title>
	<meta name="description" content="iphp framework">
	<meta name="keywords" content="iphp framework">
	<link href="/ssdb/css/bootstrap.min.css" rel="stylesheet">
	<link href="/ssdb/css/main.css" rel="stylesheet">
	<script src="/ssdb/js/jquery-1.9.1.min.js"></script>
	<script src="/ssdb/js/bootstrap.min.js"></script>
	<script src="/ssdb/js/PagerView.js"></script>
</head>
<body>

<!-- Fixed navbar -->
<div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
	<div class="container">
		<div class="navbar-header">
			<a class="navbar-brand" href="/ssdb/">PSA</a>
		</div>
		<ul class="nav navbar-nav">
			<li class="divider-vertical"></li>
			<li><a href="/ssdb/kv/scan">kv</a></li>
			<li><a href="/ssdb/hash">hash</a></li>
			<li><a href="/ssdb/zset">zset</a></li>
			<li><a href="/ssdb/queue">queue</a></li>
			<li style="line-height: 40px;">
				<select name="PHPSSDBADMIN_SERVER">
				<?php foreach((array)$confs as $k=>$conf){ ?>
					<option value="<?php echo $k;?>"<?php echo $k==$conf_k? ' selected="selected"' : ''; ?>><?php echo $k; ?></option>
				<?php } ?>
				</select>
			</li>
		</ul>
		<ul class="nav navbar-nav navbar-right">
		<?php if($user){ ?>
			<li><a href="<?php echo _url('logout')?>">Logout</a></li>
		<?php } ?>
			<li><a target="_blank" href="http://ssdb.io?psa">ssdb.io</a></li>
			<li><a target="_blank" href="https://github.com/ssdb/phpssdbadmin">psa</a></li>
		</ul>
	</div>
</div>


<script>
$(function(){
	$('select[name=PHPSSDBADMIN_SERVER]').change(function(){
		var url = '?PHPSSDBADMIN_SERVER=' + $(this).val();
		location.href = url;
		return false;
	});
});
</script>


<div class="container">
