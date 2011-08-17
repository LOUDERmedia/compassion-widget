<head>
	<style type="text/css">
	body {padding:10px 10px 0;margin:0;background-color:transparent;}
	</style>
</head>
<body>
	<?php 
	require_once('arrays.php');
	if (!empty($_GET['country']) && array_key_exists($_GET['bannerkey'], $loud_cw_country_banners)) {		
		echo str_replace('cboCountry=','cboCountry='.$_GET['country'], $loud_cw_country_banners[$_GET['bannerkey']]['code']);
	}
	elseif (array_key_exists($_GET['bannerkey'], $loud_cw_banners)) {
		echo $loud_cw_banners[$_GET['bannerkey']]['code'];	
	}
	else {
		echo '<p>Preview not available</p>';
	}
	?>
</body>