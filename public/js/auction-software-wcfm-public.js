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
			if ($('#product_type').val() === 'auction_simple' || $('#product_type').val() === 'auction_reverse' || $('#product_type').val() === 'auction_penny') {
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
						$('.options_group_auction_relist_settings .auction_relist').hide();
					} else if ($(this).val() == 'relist') {
						$('.auction_relist.wcfm-checkbox').each(
							function () {
								$('.auction_extend.wcfm-checkbox').hide();
								$(this).show();
							}
						);
						$('.options_group_auction_relist_settings .auction_extend').hide();

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

				$is_valid = wcfm_products_manage_form_validate();
				$is_valid = is_valid(true);

				function is_valid($is_publish) {
					if ($is_publish) {
						$(document.body).trigger('wcfm_products_manage_form_validate', $('#wcfm_products_manage_form'));
						$wcfm_is_valid_form = product_form_is_valid;
					}
					return product_form_is_valid;
				}

				function wcfm_products_manage_form_validate() {
					product_form_is_valid = true;
					$('.wcfm-message').html('').removeClass('wcfm-error').removeClass('wcfm-success').slideUp();

					//Title field
					var title = $.trim($('#wcfm_products_manage_form').find('#pro_title').val());
					$('#wcfm_products_manage_form').find('#pro_title').removeClass('wcfm_validation_failed').addClass('wcfm_validation_success');

					if (title.length == 0) {
						$('#wcfm_products_manage_form').find('#pro_title').removeClass('wcfm_validation_success').addClass('wcfm_validation_failed');
						product_form_is_valid = false;
						$('#wcfm_products_manage_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + wcfm_products_manage_messages.no_title).addClass('wcfm-error').slideDown();
						wcfm_notification_sound.play();
						return;
					}

					// Start price field
					var start_price = $.trim($('#wcfm_products_manage_form').find('#auction_start_price').val());
					$('#wcfm_products_manage_form').find('#auction_start_price').removeClass('wcfm_validation_failed').addClass('wcfm_validation_success');

					if (start_price < 0 || start_price === '' || start_price === null) {
						$('#wcfm_products_manage_form').find('#auction_start_price').removeClass('wcfm_validation_success').addClass('wcfm_validation_failed');
						product_form_is_valid = false;
						$('#wcfm_products_manage_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + 'Start Price should not be negative or empty.').addClass('wcfm-error').slideDown();
						wcfm_notification_sound.play();
						return;
					}

					// Bid Increment field
					var bid_incr = $.trim($('#wcfm_products_manage_form').find('#auction_bid_increment').val());
					$('#wcfm_products_manage_form').find('#auction_bid_increment').removeClass('wcfm_validation_failed').addClass('wcfm_validation_success');

					if (bid_incr < 0) {
						$('#wcfm_products_manage_form').find('#auction_bid_increment').removeClass('wcfm_validation_success').addClass('wcfm_validation_failed');
						product_form_is_valid = false;
						$('#wcfm_products_manage_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + 'Start Price should not be negative or empty.').addClass('wcfm-error').slideDown();
						wcfm_notification_sound.play();
						return;
					}


					// Date from and date to field 
					var auction_date_from = $.trim($('#wcfm_products_manage_form').find('#auction_date_from').val());
					var auction_date_to = $.trim($('#wcfm_products_manage_form').find('#auction_date_to').val());
					$('#wcfm_products_manage_form').find('#auction_date_from').removeClass('wcfm_validation_failed').addClass('wcfm_validation_success');
					$('#wcfm_products_manage_form').find('#auction_date_to').removeClass('wcfm_validation_failed').addClass('wcfm_validation_success');
					if (auction_date_from === '') {
						$('#wcfm_products_manage_form').find('#auction_date_from').removeClass('wcfm_validation_success').addClass('wcfm_validation_failed');
						product_form_is_valid = false;
						$('#wcfm_products_manage_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + 'Date From should not be empty.').addClass('wcfm-error').slideDown();
						wcfm_notification_sound.play();
						return;
					}

					if (auction_date_to === '') {
						$('#wcfm_products_manage_form').find('#auction_date_to').removeClass('wcfm_validation_success').addClass('wcfm_validation_failed');
						product_form_is_valid = false;
						$('#wcfm_products_manage_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + 'Date To should not be empty.').addClass('wcfm-error').slideDown();
						wcfm_notification_sound.play();
						return;
					}


					if (auction_date_from > auction_date_to) {
						$('#wcfm_products_manage_form').find('#auction_date_from').removeClass('wcfm_validation_success').addClass('wcfm_validation_failed');
						product_form_is_valid = false;
						$('#wcfm_products_manage_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + 'Date From should not be greater than Date To.').addClass('wcfm-error').slideDown();
						wcfm_notification_sound.play();
						return;
					}

					if ($('#product_type').val() === 'auction_simple') {
						// Reserve price field 
						var reserve_price = $.trim($('#wcfm_products_manage_form').find('#auction_reserve_price').val());
						$('#wcfm_products_manage_form').find('#auction_reserve_price').removeClass('wcfm_validation_failed').addClass('wcfm_validation_success');

						if (reserve_price < 0 || reserve_price === '' || reserve_price === null) {
							$('#wcfm_products_manage_form').find('#auction_reserve_price').removeClass('wcfm_validation_success').addClass('wcfm_validation_failed');
							product_form_is_valid = false;
							$('#wcfm_products_manage_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + 'Reserve Price should not be negative or empty.').addClass('wcfm-error').slideDown();
							wcfm_notification_sound.play();
							return;
						}

						// By it now price field 
						var buy_it_now_price = $.trim($('#wcfm_products_manage_form').find('#auction_buy_it_now_price').val());
						$('#wcfm_products_manage_form').find('#auction_buy_it_now_price').removeClass('wcfm_validation_failed').addClass('wcfm_validation_success');

						if (buy_it_now_price < 0 || buy_it_now_price === '' || buy_it_now_price === null) {
							$('#wcfm_products_manage_form').find('#auction_buy_it_now_price').removeClass('wcfm_validation_success').addClass('wcfm_validation_failed');
							product_form_is_valid = false;
							$('#wcfm_products_manage_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + 'Buy It Now Price should not be negative or empty.').addClass('wcfm-error').slideDown();
							wcfm_notification_sound.play();
							return;
						}
					}

					if ($('#product_type').val() === 'auction_reverse') {
						// Reserve reverse price field
						var reserve_price = $.trim($('#wcfm_products_manage_form').find('#auction_reserve_price_reverse').val());
						$('#wcfm_products_manage_form').find('#auction_reserve_price_reverse').removeClass('wcfm_validation_failed').addClass('wcfm_validation_success');

						if (reserve_price < 0 || reserve_price === '' || reserve_price === null) {
							$('#wcfm_products_manage_form').find('#auction_reserve_price_reverse').removeClass('wcfm_validation_success').addClass('wcfm_validation_failed');
							product_form_is_valid = false;
							$('#wcfm_products_manage_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + 'Reserve Price should not be negative or empty.').addClass('wcfm-error').slideDown();
							wcfm_notification_sound.play();
							return;
						}

						// By it now reverse price field 
						var buy_it_now_price = $.trim($('#wcfm_products_manage_form').find('#auction_buy_it_now_price_reverse').val());
						$('#wcfm_products_manage_form').find('#auction_buy_it_now_price_reverse').removeClass('wcfm_validation_failed').addClass('wcfm_validation_success');

						if (buy_it_now_price < 0 || buy_it_now_price === '' || buy_it_now_price === null) {
							$('#wcfm_products_manage_form').find('#auction_buy_it_now_price_reverse').removeClass('wcfm_validation_success').addClass('wcfm_validation_failed');
							product_form_is_valid = false;
							$('#wcfm_products_manage_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + 'Buy It Now Price should not be negative or empty.').addClass('wcfm-error').slideDown();
							wcfm_notification_sound.play();
							return;
						}
					}


					if ($('#auction_extend_if_fail').prop('checked') === true) {
						// Wait time before extend duration
						var auction_wait_time_before_extend_if_fail = $.trim($('#wcfm_products_manage_form').find('#auction_wait_time_before_extend_if_fail').val());
						$('#wcfm_products_manage_form').find('#auction_wait_time_before_extend_if_fail').removeClass('wcfm_validation_failed').addClass('wcfm_validation_success');

						if ( auction_wait_time_before_extend_if_fail < 0) {
							$('#wcfm_products_manage_form').find('#auction_wait_time_before_extend_if_fail').removeClass('wcfm_validation_success').addClass('wcfm_validation_failed');
							product_form_is_valid = false;
							$('#wcfm_products_manage_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + 'Wait time before extend should not be negative.').addClass('wcfm-error').slideDown();
							wcfm_notification_sound.play();
							return;
						}

						// auction_extend_duration_if_fail duration
						var auction_extend_duration_if_fail = $.trim($('#wcfm_products_manage_form').find('#auction_extend_duration_if_fail').val());
						$('#wcfm_products_manage_form').find('#auction_extend_duration_if_fail').removeClass('wcfm_validation_failed').addClass('wcfm_validation_success');

						if (auction_extend_duration_if_fail < 0) {
							$('#wcfm_products_manage_form').find('#auction_extend_duration_if_fail').removeClass('wcfm_validation_success').addClass('wcfm_validation_failed');
							product_form_is_valid = false;
							$('#wcfm_products_manage_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + 'Extend duration should not be negative.').addClass('wcfm-error').slideDown();
							wcfm_notification_sound.play();
							return;
						}
					}

					if ($('#auction_extend_if_not_paid').prop('checked') === true) {
						// auction_wait_time_before_extend_if_not_paid duration
						var auction_wait_time_before_extend_if_not_paid = $.trim($('#wcfm_products_manage_form').find('#auction_wait_time_before_extend_if_not_paid').val());
						$('#wcfm_products_manage_form').find('#auction_wait_time_before_extend_if_not_paid').removeClass('wcfm_validation_failed').addClass('wcfm_validation_success');

						if (auction_wait_time_before_extend_if_not_paid < 0) {
							$('#wcfm_products_manage_form').find('#auction_wait_time_before_extend_if_not_paid').removeClass('wcfm_validation_success').addClass('wcfm_validation_failed');
							product_form_is_valid = false;
							$('#wcfm_products_manage_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + 'Wait time before extend should not be negative.').addClass('wcfm-error').slideDown();
							wcfm_notification_sound.play();
							return;
						}

						// auction_extend_duration_if_not_paid duration
						var auction_extend_duration_if_not_paid = $.trim($('#wcfm_products_manage_form').find('#auction_extend_duration_if_not_paid').val());
						$('#wcfm_products_manage_form').find('#auction_extend_duration_if_not_paid').removeClass('wcfm_validation_failed').addClass('wcfm_validation_success');

						if (auction_extend_duration_if_not_paid < 0) {
							$('#wcfm_products_manage_form').find('#auction_extend_duration_if_not_paid').removeClass('wcfm_validation_success').addClass('wcfm_validation_failed');
							product_form_is_valid = false;
							$('#wcfm_products_manage_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + 'Extend duration should not be negative.').addClass('wcfm-error').slideDown();
							wcfm_notification_sound.play();
							return;
						}
					}

					if ($('#auction_extend_always').prop('checked') === true) {
						// auction_wait_time_before_extend_always duration
						var auction_wait_time_before_extend_always = $.trim($('#wcfm_products_manage_form').find('#auction_wait_time_before_extend_always').val());
						$('#wcfm_products_manage_form').find('#auction_wait_time_before_extend_always').removeClass('wcfm_validation_failed').addClass('wcfm_validation_success');

						if (auction_wait_time_before_extend_always < 0) {
							$('#wcfm_products_manage_form').find('#auction_wait_time_before_extend_always').removeClass('wcfm_validation_success').addClass('wcfm_validation_failed');
							product_form_is_valid = false;
							$('#wcfm_products_manage_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + 'Wait time before extend should not be negative.').addClass('wcfm-error').slideDown();
							wcfm_notification_sound.play();
							return;
						}

						// auction_extend_duration_always duration
						var auction_extend_duration_always = $.trim($('#wcfm_products_manage_form').find('#auction_extend_duration_always').val());
						$('#wcfm_products_manage_form').find('#auction_extend_duration_always').removeClass('wcfm_validation_failed').addClass('wcfm_validation_success');

						if (auction_extend_duration_always < 0) {
							$('#wcfm_products_manage_form').find('#auction_extend_duration_always').removeClass('wcfm_validation_success').addClass('wcfm_validation_failed');
							product_form_is_valid = false;
							$('#wcfm_products_manage_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + 'Extend duration should not be negative.').addClass('wcfm-error').slideDown();
							wcfm_notification_sound.play();
							return;
						}
					}


					//For relist 
					if ($('#auction_relist_if_fail').prop('checked') === true) {
						// Wait time before duration
						var auction_wait_time_before_relist_if_fail = $.trim($('#wcfm_products_manage_form').find('#auction_wait_time_before_relist_if_fail').val());
						$('#wcfm_products_manage_form').find('#auction_wait_time_before_relist_if_fail').removeClass('wcfm_validation_failed').addClass('wcfm_validation_success');

						if (auction_wait_time_before_relist_if_fail < 0) {
							$('#wcfm_products_manage_form').find('#auction_wait_time_before_relist_if_fail').removeClass('wcfm_validation_success').addClass('wcfm_validation_failed');
							product_form_is_valid = false;
							$('#wcfm_products_manage_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + 'Wait time before relist should not be negative.').addClass('wcfm-error').slideDown();
							wcfm_notification_sound.play();
							return;
						}

						// auction_relist_duration_if_fail duration
						var auction_relist_duration_if_fail = $.trim($('#wcfm_products_manage_form').find('#auction_relist_duration_if_fail').val());
						$('#wcfm_products_manage_form').find('#auction_relist_duration_if_fail').removeClass('wcfm_validation_failed').addClass('wcfm_validation_success');

						if (auction_relist_duration_if_fail < 0) {
							$('#wcfm_products_manage_form').find('#auction_relist_duration_if_fail').removeClass('wcfm_validation_success').addClass('wcfm_validation_failed');
							product_form_is_valid = false;
							$('#wcfm_products_manage_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + 'relist duration should not be negative.').addClass('wcfm-error').slideDown();
							wcfm_notification_sound.play();
							return;
						}
					}

					if ($('#auction_relist_if_not_paid').prop('checked') === true) {

						// auction_wait_time_before_relist_if_not_paid duration
						var auction_wait_time_before_relist_if_not_paid = $.trim($('#wcfm_products_manage_form').find('#auction_wait_time_before_relist_if_not_paid').val());
						$('#wcfm_products_manage_form').find('#auction_wait_time_before_relist_if_not_paid').removeClass('wcfm_validation_failed').addClass('wcfm_validation_success');

						if (auction_wait_time_before_relist_if_not_paid < 0) {
							$('#wcfm_products_manage_form').find('#auction_wait_time_before_relist_if_not_paid').removeClass('wcfm_validation_success').addClass('wcfm_validation_failed');
							product_form_is_valid = false;
							$('#wcfm_products_manage_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + 'Wait time before relist should not be negative.').addClass('wcfm-error').slideDown();
							wcfm_notification_sound.play(); return;

						}

						// auction_relist_duration_if_not_paid duration
						var auction_relist_duration_if_not_paid = $.trim($('#wcfm_products_manage_form').find('#auction_relist_duration_if_not_paid').val());
						$('#wcfm_products_manage_form').find('#auction_relist_duration_if_not_paid').removeClass('wcfm_validation_failed').addClass('wcfm_validation_success');

						if (auction_relist_duration_if_not_paid < 0) {
							$('#wcfm_products_manage_form').find('#auction_relist_duration_if_not_paid').removeClass('wcfm_validation_success').addClass('wcfm_validation_failed');
							product_form_is_valid = false;
							$('#wcfm_products_manage_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + 'relist duration should not be negative.').addClass('wcfm-error').slideDown();
							wcfm_notification_sound.play();
							return;
						}
					}
					if ($('#auction_relist_always').prop('checked') === true) {

						// auction_wait_time_before_relist_always duration
						var auction_wait_time_before_relist_always = $.trim($('#wcfm_products_manage_form').find('#auction_wait_time_before_relist_always').val());
						$('#wcfm_products_manage_form').find('#auction_wait_time_before_relist_always').removeClass('wcfm_validation_failed').addClass('wcfm_validation_success');

						if (auction_wait_time_before_relist_always < 0) {
							$('#wcfm_products_manage_form').find('#auction_wait_time_before_relist_always').removeClass('wcfm_validation_success').addClass('wcfm_validation_failed');
							product_form_is_valid = false;
							$('#wcfm_products_manage_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + 'Wait time before relist should not be negative.').addClass('wcfm-error').slideDown();
							wcfm_notification_sound.play();
							return;
						}

						// auction_relist_duration_always duration
						var auction_relist_duration_always = $.trim($('#wcfm_products_manage_form').find('#auction_relist_duration_always').val());
						$('#wcfm_products_manage_form').find('#auction_relist_duration_always').removeClass('wcfm_validation_failed').addClass('wcfm_validation_success');

						if (auction_relist_duration_always < 0) {
							$('#wcfm_products_manage_form').find('#auction_relist_duration_always').removeClass('wcfm_validation_success').addClass('wcfm_validation_failed');
							product_form_is_valid = false;
							$('#wcfm_products_manage_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + 'relist duration should not be negative.').addClass('wcfm-error').slideDown();
							wcfm_notification_sound.play();
							return;
						}
					}
				}


				if ($is_valid) {
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
					}
				});

			}
			else {
				$('.wcfm_submit_button').show();
			}
		});


}
);

}) (jQuery);
