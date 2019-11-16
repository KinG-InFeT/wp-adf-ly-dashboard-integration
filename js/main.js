jQuery(document).ready(function($) {

	jQuery('#adf_ly_dashboard_widget_filter_month').on('change', function() {
		
		var filterMonth = $(this).val();
		
		var data = {
			'action': 'wp_adfly_update_month_filter',
			'filter_month': filterMonth
		};
		
		jQuery.post(ajaxurl, data, function(response) {
			//alert('Got this from the server: ' + response);
			window.location.reload();
		});
	});
});