function set_active_widget(instance_id) {
    self.CW_instance = instance_id;
}

jQuery(document).ready(function($) {
	$('input.source_code_checkbox').each(function() {
		if ($(this).attr('checked') == false) {
			$(this).siblings('div.source_code_wrap').hide();
		} else {
			$(this).siblings('div.source_code_wrap').show();
		}
	});
	$('input.source_code_checkbox').live('click', function() {
		var scvalue = $(this).attr('checked');
		$(this).parents('p').siblings('div.source_code_wrap').toggle(scvalue);
	});			
	$('.select_banner_button').live('click', function () {
		var choice = $(this).attr('id');
		var realBannerKeyId = $(this).parents('div.widget').find('input.keyfieldid').attr('id');
		choice.replace('_Wrapper','');
		console.log(choice);
		tb_remove();
		$('.your-choice').html(choice);
		$('#' + window.bannerListId + ' option').attr('selected','');
		// #widget-loud-compassion-banner-widget-8-banner_key option
		$('#' + window.bannerListId + ' option[value="'+choice+'"]').attr('selected','selected');
		$('input#' + window.realBannerKeyId).attr('value',choice);
	});
	
	$('.show_children_from').live('change', function () {
		var country = $(this).val();
		var bannerKeyFieldId = $(this).parents('div.widget').find('select.banner_key').attr('id');
		var bannerKeyFieldName = $(this).parents('div.widget').find('select.banner_key').attr('name');
		var realBannerKeyId = $(this).parents('div.widget').find('input.keyfieldid').attr('id');
		var newBannerList;
		if (!country.length) {
			newBannerList = $(this).parents('div.widget').find('div.loud_cw_banners').html();
		} else {
			newBannerList = $(this).parents('div.widget').find('div.loud_cw_country_banners').html();
		}
		newBannerList = newBannerList.replace('<select>','<select name="'+bannerKeyFieldName+'" id="'+bannerKeyFieldId+'" class="banner_key"');
		$('select#'+bannerKeyFieldId).replaceWith(newBannerList);
		var newSelectVal = $('select#'+bannerKeyFieldId).val();
		$('input#' + realBannerKeyId).attr('value',newSelectVal);
	})
	.change();

	$('select.banner_key').live('change', function () {
		var realBannerKeyId = $(this).parents('div.widget').find('input.keyfieldid').attr('id');
		var newSelectVal = $(this).val();
		$('input#' + realBannerKeyId).attr('value',newSelectVal);
	})
	.change();
		
	 $("body").click(function(event) {
		if ($(event.target).is('input.banner_select_link')) {
			window.bannerListId = $('div[id$="'+self.CW_instance+'"]').find('select.banner_key').attr('id');
			window.realBannerKeyId = $('div[id$="'+self.CW_instance+'"]').find('input.keyfieldid').attr('id');
			console.log(window.bannerListId);
			console.log(self.CW_instance);
			myTbWrapperId = 'myTBcontent-' + self.CW_instance;
			var currCountry = $('div[id$="'+self.CW_instance+'"]').find('select.show_children_from').val();
			var data = {
				action: 'bannerSelect',
				countryfield: currCountry,
				width: '800',
				height: '700'
			}	
			jQuery.post(ajaxurl, data, function(response){
				if (document.getElementById(myTbWrapperId)) {
					$('#'+myTbWrapperId).html(response);
				}
				else {
					$('body').append('<div id="'+myTbWrapperId+'" style="display:none;">'+response+'</div>');													
				}						
				tb_show("Message", "#TB_inline?height=800&width=700&inlineId="+myTbWrapperId, '');	
				});
	        }
		});
});