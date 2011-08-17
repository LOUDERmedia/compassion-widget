<?php
/*
Plugin Name: Compassion Widget
Plugin URI: http://compassion.org
Description: A widget to display Compassion sponsorship banners
Version: 0.1
Author: LOUDERmedia
Author URI: http://loudermedia.com
License: GPL2
*/

/*  Copyright 2011  LOUDERmedia  (email : admin@loudermedia.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

	Major shout out to Shane & Peter, Inc. (Peter Chester) for figuring out
	how to deal with the Wordpress upload thickbox!
*/

require_once('loud-inc/arrays.php');

wp_enqueue_style('thickbox'); // <!-- inserting style sheet for Thickbox.  -->
wp_enqueue_script('jquery'); //<!--  including jquery. -->
wp_enqueue_script('thickbox'); //<!--  including Thickbox javascript. -->
// $plugin_url = plugins_url().'/compassion-widget';
// $cw_form_url = '';
// wp_enqueue_script('cw_forms', $plugin_url.'/js/cw_forms.js','','',true);

add_action( 'widgets_init', 'loud_load_compassion_banner_widget' );


if (!function_exists('loud_cw_string_limit_words')) {
	function loud_cw_string_limit_words($string, $word_limit) {
		$words = explode(' ', $string);
		return implode(' ', array_slice($words, 0, $word_limit));
	}
}

function loud_load_compassion_banner_widget() {
	register_widget( 'Loud_Compassion_Banner_Widget' );
}

class Loud_Compassion_Banner_Widget extends WP_Widget {	
	
	var $pluginDomain = 'loud_compassion_banner_widget';
	
	function Loud_Compassion_Banner_Widget() {
		$widget_ops = array( 'classname' => 'loud_compassion_banner_widget', 'description' => 'Generates a Compassion banner' );
		$control_ops = array( 'id_base' => 'loud-compassion-banner-widget' );
		$this->WP_Widget( 'loud-compassion-banner-widget', __('Compassion Banners', $this->pluginDomain), $widget_ops, $control_ops );
		
		global $pagenow;
		if (WP_ADMIN) {
    		add_action( 'admin_init', array( $this, 'fix_async_upload_image' ) );
			if ( 'widgets.php' == $pagenow ) {
				wp_enqueue_style( 'thickbox' );
				wp_enqueue_script( $control_ops['id_base'], WP_PLUGIN_URL.'/compassion-widget/js/cw-global.js',array('thickbox'), false, true );
				add_action( 'admin_head-widgets.php', array( $this, 'admin_head' ) );
			} elseif ( 'media-upload.php' == $pagenow || 'async-upload.php' == $pagenow ) {
				add_filter( 'image_send_to_editor', array( $this,'image_send_to_editor'), 1, 8 );
				add_filter( 'gettext', array( $this, 'replace_text_in_thickbox' ), 1, 3 );
				add_filter( 'media_upload_tabs', array( $this, 'media_upload_tabs' ) );
			}
			add_action('wp_ajax_bannerSelect', array(&$this, 'bannerSelect'));
		}
	}
	
	function fix_async_upload_image() {
		if(isset($_REQUEST['attachment_id'])) {
			$GLOBALS['post'] = get_post($_REQUEST['attachment_id']);
		}
	}
	
	function loadPluginTextDomain() {
		load_plugin_textdomain( $this->pluginDomain, false, trailingslashit(basename(dirname(__FILE__))) . 'lang/');
	}
	
	function is_cw_widget_context() {
		if ( isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],$this->id_base) !== false ) {
			return true;
		} elseif ( isset($_REQUEST['_wp_http_referer']) && strpos($_REQUEST['_wp_http_referer'],$this->id_base) !== false ) {
			return true;
		} elseif ( isset($_REQUEST['widget_id']) && strpos($_REQUEST['widget_id'],$this->id_base) !== false ) {
			return true;
		}
		return false;
	}
	
	function replace_text_in_thickbox($translated_text, $source_text, $domain) {
		if ( $this->is_cw_widget_context() ) {
			if ('Insert into Post' == $source_text) {
				return __('Insert Into Widget', $this->pluginDomain );
			}
		}
		return $translated_text;
	}
	
	function image_send_to_editor( $html, $id, $caption, $title, $align, $url, $size, $alt = '' ) {
		// Normally, media uploader return an HTML string (in this case, typically a complete image tag surrounded by a caption).
		// Don't change that; instead, send custom javascript variables back to opener.
		// Check that this is for the widget. Shouldn't hurt anything if it runs, but let's do it needlessly.
		if ( $this->is_cw_widget_context() ) {
			if ($alt=='') $alt = $title;
			?>
			<script type="text/javascript">
				// send image variables back to opener
				var win = window.dialogArguments || opener || parent || top;
				win.IW_html = '<?php echo addslashes($html) ?>';
				win.IW_img_id = '<?php echo $id ?>';
				win.IW_alt = '<?php echo addslashes($alt) ?>';
				win.IW_caption = '<?php echo addslashes($caption) ?>';
				win.IW_title = '<?php echo addslashes($title) ?>';
				win.IW_align = '<?php echo $align ?>';
				win.IW_url = '<?php echo $url ?>';
				win.IW_size = '<?php echo $size ?>';
			</script>
			<?php
		}
		return $html;
	}

	function media_upload_tabs($tabs) {
		if ( $this->is_cw_widget_context() ) {
			unset($tabs['type_url']);
		}
		return $tabs;
	}
	
	function admin_head() {
		?>
		<style type="text/css">
			.aligncenter {
				display: block;
				margin-left: auto;
				margin-right: auto;
			}
			ul.cw_banner_list {
				list-style-type:none;
				padding:0;
				margin:0;
			}
			ul.cw_banner_list li {
				padding:9px 0;
				margin:0;
				border-top:1px solid #ccc;
			}
			ul.cw_banner_list li:first-child {
				border-top:none;
			}
			ul.cw_banner_list li div.banner_preview {
				min-height:154px;
				width:100%;
				position:relative;
				border:1px solid #ccc;
				background-color:#eee;
			}
			div.source_code_wrap {margin:0 0 1em 0.5em;}
			div.source_code_wrap label {margin-right:2px;position:relative;top:-1px;}
		</style>
		<?php
	}
	
	function bannerSelect() {
		global $loud_cw_banners;
		global $loud_cw_country_banners;
		// echo '<pre>'; var_dump($_POST); die();
		$country_field = $_POST['countryfield'];
		// echo '<p>'.$country_field.'</p>';
		$current_banners = $loud_cw_banners;
		if (!empty($country_field)) $current_banners = $loud_cw_country_banners;
		?>
		<ul class="cw_banner_list" id="loud_cw_banners">
		<?php
		foreach ($current_banners as $bannerkey => $banner){
			$banner_output = '';
			$banner_output.='<li class="cw_banner" id="'.$bannerkey.'-wrap">';
			$banner_output.='<h2>'.$banner['width'].'x'.$banner['height'].' '.$banner['type'].'</h2>';
			$banner_output.='<div class="banner_preview"><iframe id="'.$bannerkey.'"src="'.WP_PLUGIN_URL.'/compassion-widget/loud-inc/frame.php?bannerkey='.$bannerkey.'&country='.$country_field.'" width="100%" height="'.($banner['height']+20).'"></iframe></div>';
			//$banner_output.='<div class="banner_preview">'.$banner['code'].'</div>';
			$banner_output.='<p><input type="button" class="select_banner_button button" id="'.$bannerkey.'" value="Select This Banner" /></p>';
			$banner_output.='</li>';		
			echo $banner_output;
		}
		?>
		</ul>
		<p class="aligncenter"><input type="submit" class="button" value="Close Window" onclick="self.parent.tb_remove();" /></p> 
		<?php
		die();
	}
	
	function widget( $args, $instance ) {
		global $loud_cw_banners;
		global $loud_cw_country_banners;
		global $loud_cw_text_links;
		extract( $args );
		$output = '';
		
		/* User-selected settings. */
		$use_source_code = $instance['use_source_code'];
		$source_code = $instance['source_code'];
		$show_children_from = $instance['show_children_from'];
		$banner_key = $instance['banner_key'];
		$add_text_link = $instance['add_text_link'];
		$widget_title = $instance['title'];
		$cw_array = (!empty($show_children_from))?$loud_cw_country_banners:$loud_cw_banners;
		$referer_code = '';
		if ($use_source_code == "on" && !empty($source_code)) $referer_code .= '?referer='.$source_code; //we need to account for a ? or a & depending on whether this follows another query string or not
		
		/* Before widget (defined by themes). */
		$output .= $before_widget;

		/* Title of widget (before and after defined by themes). */
		// if ($widget_title) $output .= $before_title . $widget_title . $after_title;
		
		if (!empty($show_children_from)) { //country-specific banners
			$referer_code = str_replace('?','&',$referer_code);
			$banner_code = str_replace('cboCountry=','cboCountry='.$show_children_from.$referer_code,$loud_cw_country_banners[$banner_key]['code']);
		} else { //non-country-specific banners
			$pattern = '/<([a|(script)|(object)|(embed)|(map)])(.*?)([(src)|(href)|(value)])="http(.*?)"/i';
			$replace = '<$1$2$3="http$4'.$referer_code.'"';
			$banner_code = preg_replace($pattern,$replace,$loud_cw_banners[$banner_key]['code']);
		}
		$output .= $banner_code;
		
		if (!empty($add_text_link)) {
			$output .= '<p style="text-align:center;"><a href="'.$loud_cw_text_links[$add_text_link].'" title="'.$add_text_link.'">'.$add_text_link.'</a></p>'."\n";	
		}

		/* After widget (defined by themes). */
		$output .= $after_widget;
		echo $output;
		
	}
	
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['use_source_code'] = ($new_instance['use_source_code'] == 'on')?'on':'off';
		$instance['source_code'] = ($new_instance['use_source_code'] == 'on')?strip_tags($new_instance['source_code']):'';
		$instance['show_children_from'] = $new_instance['show_children_from'];
		$instance['banner_key'] = $new_instance['real_banner_key'];
		$instance['add_text_link'] = $new_instance['add_text_link'];
		$instance['real_banner_key'] = $new_instance['real_banner_key'];
		$instance['title'] = strip_tags($new_instance['title']);
		return $instance;
	}
	
	function form( $instance ) {
		global $loud_cw_banners;
		global $loud_cw_country_banners;
		global $loud_cw_text_links;
		global $loud_cw_countries;
		$loud_cw_banners_keys = array_keys($loud_cw_banners);
		/* Set up some default widget settings. */
		$defaults = array(
			'title' => '', 
			'use_source_code' => 'off', 
			'source_code' => '', 
			'show_children_from' => '', 
			'banner_key' => $loud_cw_banners_keys[0], 
			'add_text_link' => '', 
			'real_banner_key' => $loud_cw_banners_keys[0]
		);
		$instance = wp_parse_args( (array) $instance, $defaults );
		$current_banners = $loud_cw_banners;
		if (!empty($instance['show_children_from'])) $current_banners = $loud_cw_country_banners;
		?>
		<!-- <p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">Title:</label>
			<input class="widefat" type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" />
		</p> -->
		<p class="use_sc_wrap">
			<input type="checkbox" id="<?php echo $this->get_field_id( 'use_source_code' ); ?>" name="<?php echo $this->get_field_name( 'use_source_code' ); ?>" value="on" class="checkbox source_code_checkbox"<?php if ($instance['use_source_code'] == 'on') echo ' checked="checked" '; ?>/>
			<label for="<?php echo $this->get_field_id( 'use_source_code' ); ?>">Use Source Code?</label><!-- <br /> -->
		</p>
		<div class="source_code_wrap" id="<?php echo $this->get_field_id( 'source_code' ); ?>_wrap"<?php if ($instance['use_source_code'] != 'on') echo ' style="display:none;"'; ?>>
			<label for="<?php echo $this->get_field_id( 'source_code' ); ?>">Source Code:</label>
			<input type="text" name="<?php echo $this->get_field_name( 'source_code' ) ?>" value="<?php echo $instance['source_code']; ?>" id="<?php echo $this->get_field_id( 'source_code' ); ?>" size="8">
		</div>

		<p class="show_children_from_wrap">
			<label for="<?php echo $this->get_field_id( 'show_children_from' ) ?>">Show Children From:</label><br/>
			<select name="<?php echo $this->get_field_name('show_children_from'); ?>" id="<?php echo $this->get_field_id('show_children_from'); ?>" class="show_children_from widefat">
				<?php
				foreach ($loud_cw_countries as $code => $country) {
					$output = '<option value="'.$code.'"';
					if ($code == $instance['show_children_from']) $output.=' selected="selected"';
					$output.='>'.$country.'</option>';
					echo $output;	
				}
				?>
			</select>
		</p>
		<p id="banners_link_wrap">
			<label for="banner_select_button">Select Banner:</label><br/>
			<input type="button" class="button banner_select_link" name="banner_select_button" value="View Available Banners" id="" title="Select a Banner" onClick="set_active_widget('<?php echo $this->id; ?>');return false;">
			<!-- <a class="button banner_select_link" href="#" title="Select a Banner" id="show_banners_button_country">View Available Banners</a> -->
		</p>
		<!-- <p class="selected_banner_wrap">
			<label for"<?php echo $this->get_field_id('banner_key'); ?>">Selected Banner:</label>
			<select name="<?php echo $this->get_field_name('banner_key'); ?>" id="<?php echo $this->get_field_id('banner_key'); ?>" class="banner_key">
			<?php
				foreach ($current_banners as $bannerkey => $banner) {
					$output = '<option value="'.$bannerkey.'"';
					if ($bannerkey == $instance['banner_key']) $output.=' selected="selected"';
					$output.='>'.$banner['width'].'&times;'.$banner['height'].' - '.$banner['type']."</option>\n";
					echo $output;	
				}
				?>
			</select>
		</p> -->
		<p>
			<label for="<?php echo $this->get_field_id( 'add_text_link' ); ?>">Text Link:</label><br/>
			<select name="<?php echo $this->get_field_name('add_text_link'); ?>" id="<?php echo $this->get_field_id('add_text_link'); ?>" class="widefat">
				<option value=""<?php if ( '' == $instance['add_text_link'] ) echo ' selected="selected"'; ?>>None</option>
				<?php
				foreach ($loud_cw_text_links as $link_text => $link_url) {
					$output = '<option value="'.$link_text.'"';
					if ($link_text == $instance['add_text_link']) $output.=' selected="selected"';
					$output.='>'.$link_text.'</option>';
					echo $output;	
				}
				?>				
			</select>
			<small>Including a text link helps Compassion show up on search engines.</small>
		</p>
		<input class="keyfieldid" type="hidden" id="<?php echo $this->get_field_id( 'real_banner_key' ); ?>" name="<?php echo $this->get_field_name( 'real_banner_key' ); ?>" value="<?php echo $instance['real_banner_key']; ?>" />
		<div class="loud_cw_banners" style="display:none">
			<select>
			<?php
			foreach ($loud_cw_banners as $bannerkey => $banner) {
				$output = '<option value="'.$bannerkey.'"';
				if ($bannerkey == $instance['banner_key']) $output.=' selected="selected"';
				$output.='>'.$banner['width'].'&times;'.$banner['height'].' - '.$banner['type'].'</option>';
				echo $output;	
			}
			?>
			</select>
		</div>
		<div class="loud_cw_country_banners" style="display:none">
			<select>
			<?php
			foreach ($loud_cw_country_banners as $bannerkey => $banner) {
				$output = '<option value="'.$bannerkey.'"';
				if ($bannerkey == $instance['banner_key']) $output.=' selected="selected"';
				$output.='>'.$banner['width'].'&times;'.$banner['height'].' - '.$banner['type'].'</option>';
				echo $output;	
			}
			?>
			</select>
		</div>
		<?php 
	}
}