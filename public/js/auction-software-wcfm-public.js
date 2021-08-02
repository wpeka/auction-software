/**
 * Public JavaScript.
 *
 * @package    Auction_Software
 * @subpackage Auction_Software/public
 * @author     WPeka Club <support@wpeka.com>
 */

(function ($) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	$(window).load(
		function () {
			if ($("#auction_date_from").length || $("#auction_date_to").length) {
				$('#auction_date_from').datetimepicker(
					{
						defaultDate: "",
						dateFormat: "yy-mm-dd",
						timeFormat: 'HH:mm:ss',
						numberOfMonths: 1,
					}
				);
				$('#auction_date_to').datetimepicker(
					{
						defaultDate: "",
						dateFormat: "yy-mm-dd",
						timeFormat: 'HH:mm:ss',
						numberOfMonths: 1,
					}
				);
			}
			$('.options_group_auction_relist_settings .auction_relist').hide();
			$('.options_group_auction_relist_settings .auction_extend').hide();

			// When auction extend or relist option is selected
			$(document).on(
				'change',
				'#auction_extend_or_relist_auction',
				function () {
					if ($(this).val() == 'extend') {
						$('.auction_extend.wcfm-checkbox').each(
							function () {
								$('.auction_relist.wcfm-checkbox').hide();
								$(this).show();
							}
						);
					} else if ($(this).val() == 'relist') {
						$('.auction_relist.wcfm-checkbox').each(
							function () {
								$('.auction_extend.wcfm-checkbox').hide();
								$(this).show();
							}
						);
					}
					else {
						$('.options_group_auction_relist_settings .auction_relist').hide();
						$('.options_group_auction_relist_settings .auction_extend').hide();
					}
				}
			);

			// On load
			var extend_relist = $('#auction_extend_or_relist_auction').val();

			if ('extend' == extend_relist) {
				$('.auction_extend.wcfm-checkbox').each(
					function () {
						if (this.checked) {
							$('.options_group_auction_relist_settings .' + this.id).show();
						} else {
							$('.wcfm-checkbox').removeClass('options_group_auction_relist_settings ' + this.id);
							$('.options_group_auction_relist_settings .' + this.id).hide();
						}
					}
				);
			} else if ('relist' == extend_relist) {
				$('.auction_relist.wcfm-checkbox').each(
					function () {
						if (this.checked) {
							$('.options_group_auction_relist_settings .' + this.id).show();
						} else {
							$('.wcfm-checkbox').removeClass('options_group_auction_relist_settings ' + this.id);
							$('.options_group_auction_relist_settings .' + this.id).hide();
						}
					}
				);
			}

			// When one of the 3 checkboxes of extend is checked
			$('.auction_extend.wcfm-checkbox').change(
				function () {
					if (this.checked) {
						$('.options_group_auction_relist_settings .' + this.id).show();
					} else {
						$('.wcfm-checkbox').removeClass('options_group_auction_relist_settings ' + this.id);
						$('.options_group_auction_relist_settings .' + this.id).hide();
					}
				}
			);

			// When one of the 3 checkboxes of relist is checked
			$('.auction_relist.wcfm-checkbox').change(
				function () {
					if (this.checked) {
						$('.options_group_auction_relist_settings .' + this.id).show();
					} else {
						$('.wcfm-checkbox').removeClass('options_group_auction_relist_settings ' + this.id);
						$('.options_group_auction_relist_settings .' + this.id).hide();
					}
				}
			);

			// When product type auction_simple, auction_reverse or auction_penny is selected
			$(document).on(
				'change',
				'#product_type',
				function () {
					console.log(wcfm_params);
					if ($(this).val() === 'auction_simple' || $(this).val() === 'auction_reverse' || $(this).val() === 'auction_penny') {
						$('.regular_price').hide();
						$('#regular_price').hide();
						$('.sale_price').hide();
						$('#sale_price').hide();	
					}
					else {
						$('.regular_price').show();
						$('#regular_price').show();
						$('.sale_price').show();
						$('#sale_price').show();
					}
				});

			// Submit Product
			$('#wcfm_products_simple_submit_button').click(function (event) {
				event.preventDefault();

				$('.wcfm_submit_button').hide();

				var excerpt = getWCFMEditorContent('excerpt');

				var description = getWCFMEditorContent('description');

				// WC Box Office Support
				var ticket_content = getWCFMEditorContent('_ticket_content');

				var ticket_email_html = getWCFMEditorContent('_ticket_email_html');
				$('#wcfm_products_manage_form').block({
					message: null,
					overlayCSS: {
						background: '#fff',
						opacity: 0.6
					}
				});

				var data = {
					action: 'wcfm_ajax_controller',
					controller: 'wcfm-products-manage',
					wcfm_products_manage_form: $('#wcfm_products_manage_form').serialize(),
					excerpt: excerpt,
					description: description,
					status: 'submit',
					ticket_content: ticket_content,
					ticket_email_html: ticket_email_html,
				}
				$.post(wcfm_params.ajax_url, data, function (response) {

					if (response) {
						const response_json = $.parseJSON(response);
						console.log(response_json)
						$('.wcfm-message').html('').removeClass('wcfm-error').removeClass('wcfm-success').slideUp();
						wcfm_notification_sound.play();
						if (response_json.status) {
							$('#wcfm_products_manage_form .wcfm-message').html('<span class="wcicon-status-completed"></span>' + response_json.message).addClass('wcfm-success').slideDown("slow", function () {
								if (response_json.redirect) window.location = response_json.redirect;
							});
						} else {
							$('#wcfm_products_manage_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + response_json.message).addClass('wcfm-error').slideDown();
						}
						if (response_json.id) $('#pro_id').val(response_json.id);
						$('#wcfm_products_manage_form').unblock();
						$('.wcfm_submit_button').show();
					}
				});
			});


		}
	);

})(jQuery);
