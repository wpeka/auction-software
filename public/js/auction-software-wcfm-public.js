/**
 * Admin JavaScript.
 *
 * @package    Auction_Software
 * @subpackage Auction_Software/admin
 * @author     WPeka Club <support@wpeka.com>
 */

(function( $ ) {
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

	$( window ).load(
		function () {
			if ($( "#auction_date_from" ).length || $( "#auction_date_to" ).length) {
				$( '#auction_date_from' ).datetimepicker(
					{
						defaultDate: "",
						dateFormat: "yy-mm-dd",
						timeFormat: 'HH:mm:ss',
						numberOfMonths: 1,
					}
				);
				$( '#auction_date_to' ).datetimepicker(
					{
						defaultDate: "",
						dateFormat: "yy-mm-dd",
						timeFormat: 'HH:mm:ss',
						numberOfMonths: 1,
					}
				);
			}

			$( '.form-field.auction_relist' ).hide();
			$( '.form-field.auction_extend' ).hide();
			$( '.form-field.auction_' + $( '#auction_extend_or_relist_auction' ).val() ).show();
			$( document ).on(
				'change',
				'#auction_extend_or_relist_auction',
				function(){
					$( '.form-field.auction_relist' ).hide();
					$( '.form-field.auction_extend' ).hide();
					$( '.form-field.auction_' + $( this ).val() ).show();
					if ($( this ).val() == 'extend') {
						$( '.form-field.auction_extend' ).find( ':checkbox' ).each(
							function() {
								if (this.checked) {
									$( '.form-field.' + this.id ).show();
								} else {
									$( '.form-field.' + this.id ).hide();
								}
							}
						);
					} else if ($( this ).val() == 'relist') {
						$( '.form-field.auction_relist' ).find( ':checkbox' ).each(
							function() {
								if (this.checked) {
									$( '.form-field.' + this.id ).show();
								} else {
									$( '.form-field.' + this.id ).hide();
								}
							}
						);
					}
				}
			);
			var extend_relist = $( '#auction_extend_or_relist_auction' ).val();
			console.log( extend_relist );

			if ('extend' == extend_relist) {
				$( '.form-field.auction_extend' ).find( ':checkbox' ).each(
					function() {
						if (this.checked) {
							$( '.form-field.' + this.id ).show();
						} else {
							$( '.form-field.' + this.id ).hide();
						}
					}
				);
			} else if ('relist' == extend_relist) {
				$( '.form-field.auction_relist' ).find( ':checkbox' ).each(
					function() {
						if (this.checked) {
							$( '.form-field.' + this.id ).show();
						} else {
							$( '.form-field.' + this.id ).hide();
						}
					}
				);
			}

			$( '.form-field.auction_extend :checkbox' ).change(
				function() {
					if (this.checked) {
						$( '.form-field.' + this.id ).show();
					} else {
						$( '.form-field.' + this.id ).hide();
					}
				}
			);
			$( '.form-field.auction_relist :checkbox' ).change(
				function() {
					if (this.checked) {
						$( '.form-field.' + this.id ).show();
					} else {
						$( '.form-field.' + this.id ).hide();
					}
				}
			);
		}
	);

})( jQuery );
