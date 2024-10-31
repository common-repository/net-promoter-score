$nps_js = jQuery.noConflict();
jQuery(document).ready(function($nps_js) {
	$nps_js("#;;formid;;").submit(function( event ) {
		$nps_js("html,body").css("cursor", "progress");
		event.preventDefault();
		$nps_js.ajax({
			type:	"POST",
			url:	";;ajaxurl;;",
			data:	{
				form_data: $nps_js(this).serialize(), 
				action: "nps_ajaxActions", 
			},
			dataType: "text",
			success: function(msg) {
				$nps_js("html,body").css("cursor", "default");
				$nps_js("#nps_shortcode_container").hide();
				alert(msg);
			}
		});
		return false;
	});
});