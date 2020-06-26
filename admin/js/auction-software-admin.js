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
		}
	);

})( jQuery );
