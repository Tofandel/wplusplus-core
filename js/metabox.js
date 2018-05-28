/*----------------------------------------------------------------------------------*/
/* Meta Tabs
/*----------------------------------------------------------------------------------*/

// First tab onload
jQuery(window).on('load', function(){
	if(jQuery('.wpp-meta-tabs').length > 0){
		jQuery('.wpp-meta-tabs li:not(.divider):first').click();
	}
});

jQuery('.wpp-meta-container.has-tabs ul li:not(.divider)').click(function(e){
	e.preventDefault();
	var tab = jQuery(this).attr('data-tab');
	jQuery('.wpp-meta-container.has-tabs ul li').removeClass('active');
	jQuery(this).addClass('active');
	jQuery(this).closest('.wpp-meta-container.has-tabs').find('div[data-tab]').removeClass('active');
	jQuery(this).closest('.wpp-meta-container.has-tabs').find('div[data-tab="' + tab + '"]').addClass('active');
});

