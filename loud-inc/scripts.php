<?php require_once('arrays.php'); ?>
<head>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8">
	<title>Select a Banner</title>
	<style>
	div.cw_banner_outer{padding:18px;}
	ul#cw_banner_list{list-style-type:none;}
	li.cw_banner{position:relative;padding:9px 0 9px 0;min-height:100px;border-top:1px solid #aaa;}
	li.cw_banner:first-child{border-top:none;}
	</style>
	<script type="text/javascript" charset="utf-8">

	</script>
</head>
<body>
	<p style="text-align:center"><input type="submit" id="Login" value="&nbsp;&nbsp;Ok&nbsp;&nbsp;" onclick="self.parent.tb_remove();" /></p> 
	<ul id="cw_banner_list">
	<?php
	foreach ($loud_cw_banners as $bannerkey => $banner){
		$banner_output = '';
		$banner_output.='<li class="cw_banner">';
		$banner_output.='<h2>'.$banner['width'].'x'.$banner['height'].' '.$banner['type'].'</h2>';
		$banner_output.=$banner['code'];
		$banner_output.='<input type="button" class="select_banner button-primary" id="'.$bannerkey.'" value="Select This Banner" />';
		$banner_output.='</li>';
		echo $banner_output;
	}
	?>
	</ul>
</body>