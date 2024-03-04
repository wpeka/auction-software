/**
 * Frontend JavaScript.
 *
 * @package    Auction_Software
 * @subpackage Auction_Software/public
 * @author     WPeka Club <support@wpeka.com>
 */

(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
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
	$( document ).ready(
		function ($) {

			var timeLeftIds = [];
			$( '.timeLeftId' ).each(
				function() {
					if ( ! timeLeftIds.includes(this.value) ) {
						timeLeftIds.push( this.value );
					}						
				}
			);

			$.each(
				timeLeftIds,
				function( index, value ) {
					var newIndex     = value;
					var newValue     = 'timeLeftValue' + newIndex;
					var endDateValue = $( '.' + newValue ).val();
					var x            = setInterval(
						function () {

							var countDownDate = new Date( endDateValue.replace( /-/g, "/" ) ).getTime();
							var now = 0;
							if (php_vars.default) {
								var d           = new Date();
								var localTime   = d.getTime();
								var localOffset = d.getTimezoneOffset() * 60000;
								var utc         = localTime + localOffset;
								now             = utc + (3600000 * php_vars.timezone);
							} else {
								var timezoneTime = new Date().toLocaleString( "en-US", {timeZone: php_vars.timezone } );
								timezoneTime     = new Date( timezoneTime );
								now              = timezoneTime.getTime();
							}
							var distance = countDownDate - now;
							if (distance <= 0) {
								clearInterval( x );
								if ($( ".timeLeft" + newIndex ).length) {
									var startEndText = document.getElementsByClassName( 'startEndText' + newIndex );
									var endText      = 'Auction has ended';
									for( var i = 0 ; i < startEndText.length; i++ ) {
										if ($( startEndText[i] ).hasClass( 'auction_starts_in' )) {
											endText = 'Auction has started. Please refresh the page.';
											$( startEndText[i] ).remove();
										}
									}
									$( '.timeLeft' + newIndex ).text( endText );
									$( '.timeLeft' + newIndex ).css( 'display','inline-block' );	
								}
							} else {
								var days    = Math.floor( distance / (1000 * 60 * 60 * 24) );
								var hours   = Math.floor( (distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60) );
								var minutes = Math.floor( (distance % (1000 * 60 * 60)) / (1000 * 60) );
								var seconds = Math.floor( (distance % (1000 * 60)) / 1000 );
								if (document.getElementsByClassName( "timeLeft" + newIndex ) != null) {
									var timeLefts       = document.getElementsByClassName( "timeLeft" + newIndex );
									var timeLeftsLength = timeLefts.length;
									for (var index = 0;index < timeLeftsLength;index++) {
										timeLefts[index].innerHTML = '<div class="time_left-box"><p class="time-span">' + days + '</p><span class="label-span">'+php_vars.Days+'</span></div>' +
											'<div class="time_left-box"><p class="time-span">' + hours + '</p><span class="label-span">'+ php_vars.Hours +'</span></div>' +
											'<div class="time_left-box"><p class="time-span">' + minutes + '</p><span class="label-span">'+ php_vars.Minutes +'</span></div>' +
											'<div class="time_left-box"><p class="time-span">' + seconds + '</p><span class="label-span">'+ php_vars.Seconds +'</span></div>';
									}
								}
							}

						},
						1000
					);
				}
			);

			var isStarted = document.getElementById( 'is_started' ) != null ? document.getElementById( 'is_started' ).value : '';
			if (isStarted == true) {
				var productAuctionType = document.getElementById( 'product_auction_type' ) != null ? document.getElementById( 'product_auction_type' ).value : '';
				var window_focus       = true;
				$( window ).on(
					'focusin' ,
					function() {
						window_focus = true;
					}
				).on(
					'focusout',
					function() {
						window_focus = false;
					}
				);
				var getEndDate = document.getElementById( 'end_date' );
				var endDate    = getEndDate.value;
				if ($( "#product_type" ).length) {

					var x = setInterval(
						function () {

							var countDownDate = new Date( endDate.replace( /-/g, "/" ) ).getTime();
							var now           = 0;
							if (php_vars.default) {
								var d           = new Date();
								var localTime   = d.getTime();
								var localOffset = d.getTimezoneOffset() * 60000;
								var utc         = localTime + localOffset;
								now             = utc + (3600000 * php_vars.timezone);
							} else {
								var timezoneTime = new Date().toLocaleString( "en-US", {timeZone: php_vars.timezone } );
								timezoneTime     = new Date( timezoneTime );
								now              = timezoneTime.getTime();
							}

							var distance = countDownDate - now;

							var days    = Math.floor( distance / (1000 * 60 * 60 * 24) );
							var hours   = Math.floor( (distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60) );
							var minutes = Math.floor( (distance % (1000 * 60 * 60)) / (1000 * 60) );
							var seconds = Math.floor( (distance % (1000 * 60)) / 1000 );
							document.getElementById( "time_left" ) != null ? document.getElementById( "time_left" ).innerHTML = '<div class="time_left-box"><p class="time-span">' + days + '</p><span class="label-span">'+php_vars.Days+'</span></div>' +
																																'<div class="time_left-box"><p class="time-span">' + hours + '</p><span class="label-span">'+php_vars.Hours+'</span></div>' +
																																'<div class="time_left-box"><p class="time-span">' + minutes + '</p><span class="label-span">'+php_vars.Minutes+'</span></div>' +
																																'<div class="time_left-box"><p class="time-span">' + seconds + '</p><span class="label-span">'+php_vars.Seconds+'</span></div>' : "";
							if (distance < 0) {
								clearInterval( x );
								document.getElementById( "time_left" ).innerHTML = "Auction has ended";
								var buyitnow                                     = document.getElementsByClassName( 'single_add_to_cart_button' );
								var bidnow                                       = document.getElementsByClassName('auction-bid-simple');
								var penny                                        = document.getElementsByClassName('auction-bid-penny');
								$( buyitnow ).remove();
								$( bidnow ).remove();
								$( penny ).remove();
								window.location.reload();

							}

						},
						1000
					);

					$( document ).on(
						'click',
						'.single_add_to_cart_button',
						function (e) {
							e.preventDefault();

							var $thisbutton  = $( this ),
								$form        = $thisbutton.closest( 'form.cart' ),
								id           = $thisbutton.val(),
								product_qty  = $form.find( 'input[name=quantity]' ).val() || 1,
								product_id   = $form.find( 'input[name=product_id]' ).val() || id,
								variation_id = $form.find( 'input[name=variation_id]' ).val() || 0;

							var data = {
								action: 'woocommerce_ajax_add_to_cart',
								product_id: product_id,
								product_sku: '',
								quantity: product_qty,
								variation_id: variation_id,
							};

							$( document.body ).trigger( 'adding_to_cart', [$thisbutton, data] );

							$.ajax(
								{
									type: 'post',
									url: wc_cart_fragments_params.ajax_url,
									data: data,
									beforeSend: function (response) {
										$thisbutton.removeClass( 'added' ).addClass( 'loading' );
									},
									complete: function (response) {
										$thisbutton.addClass( 'added' ).removeClass( 'loading' );
									},
									success: function (response) {

										if (response.error & response.product_url) {
											window.location = response.product_url;

										} else {
											if (response.status === 'login_error') {
												$( ".woocommerce-message" ).remove();
												var node = document.getElementsByClassName( 'auction-software-form' );
												$( response.notice_message ).insertBefore( node );
											} else {
												$( document.body ).trigger( 'added_to_cart', [response.fragments, response.cart_hash, $thisbutton] );
											}
										}
									},
								}
							);

							return false;
						}
					);

					setInterval(
						function () {
							if (window_focus == true) {
								$( ".auction_reserve_price" ).load( location.href + " .auction_reserve_price", "" );
								if (productAuctionType == 'auction_simple') {
									$( ".auction_current_bid_simple" ).load( location.href + " .auction_current_bid_simple>*", "" );
									$( ".auction_max_bid_simple" ).load( location.href + " .auction_max_bid_simple>*", "" );
								}
								if (productAuctionType == 'auction_reverse') {
									$( ".auction_current_bid_reverse" ).load( location.href + " .auction_current_bid_reverse>*", "" );
									$( ".auction_max_bid_reverse" ).load( location.href + " .auction_max_bid_reverse>*", "" );
								}
								if (productAuctionType == 'auction_penny') {
									$( ".auction_current_bid_penny" ).load( location.href + " .auction_current_bid_penny>*", "" );
									$( ".auction_remaining_bids" ).load( location.href + " .auction_remaining_bids", "" );
								}
								$( "#auction_history_table" ).load( location.href + " #auction_history_table>*", "" );
							}
						},
						php_vars.timeinterval * 1000
					);

					$( document ).on(
						'click',
						'.auction-bid-simple',
						function (e) {
							e.preventDefault();

							var $thisbutton  = $( this ),
								$form        = $thisbutton.closest( 'form.cart' ),
								id           = $thisbutton.val(),
								product_qty  = $form.find( 'input[name=quantity]' ).val() || 1,
								product_id   = $form.find( 'input[name=product_id]' ).val() || id,
								variation_id = $form.find( 'input[name=variation_id]' ).val() || 0,
								price        = document.getElementById( 'auction-price-incr-decr' ).value || 0;

							var data = {
								action: 'woocommerce_ajax_add_to_cart_simple',
								product_id: product_id,
								product_sku: '',
								quantity: product_qty,
								variation_id: variation_id,
								auction_bid: 1,
								price: price,
							};

							$.ajax(
								{
									type: 'post',
									url: wc_cart_fragments_params.ajax_url,
									data: data,
									beforeSend: function (response) {
										$thisbutton.removeClass( 'added' ).addClass( 'loading' );
									},
									complete: function (response) {
										$thisbutton.addClass( 'added' ).removeClass( 'loading' );
									},
									success: function (response) {

										if (response.error & response.product_url) {
											window.location = response.product_url;

										} else {
											if (response.status === 'notice') {
												$( ".woocommerce-message" ).remove();
												var node = document.getElementsByClassName( 'auction-software-form' );
												$( response.notice_message ).insertBefore( node );
												if (response.change_bid == 1) {

													var curBid = document.getElementsByClassName( 'auction_current_bid_simple' );
													$( curBid ).empty();
													$( curBid ).append( response.change_current_bid );

													var pricebox   = document.getElementById( 'auction-price-incr-decr' );
													pricebox.value = response.change_pricebox_bid;
													setStartPrice  = response.change_pricebox_bid;

													var buyitnow = document.getElementsByClassName( 'single_add_to_cart_button' );
													if (response.remove_buy_it_now_cart_text == 1) {
														$( buyitnow ).remove();
													}

												}
												var max_bid = document.getElementById( 'auction_max_bid' );
												if (response.change_max_bid == 1) {
													$( max_bid ).css( {'display':'table-row'} );
													var max_bid_value = document.getElementsByClassName( 'auction_max_bid_simple' );
													$( max_bid_value ).empty();
													$( max_bid_value ).append( response.change_max_bid_value );
												} else {
													$( max_bid ).hide();
												}

												$( ".auction_reserve_price" ).load( location.href + " .auction_reserve_price", "" );
												$( ".auction_current_bid_simple" ).load( location.href + " .auction_current_bid_simple>*", "" );
												$( ".auction_max_bid_simple" ).load( location.href + " .auction_max_bid_simple>*", "" );
												$( "#auction_history_table" ).load( location.href + " #auction_history_table>*", "" );
												endDate = response.seconds;

											}
										}
									},
								}
							);

							return false;
						}
					);

					$( document ).on(
						'click',
						'.auction-bid-penny',
						function (e) {
							e.preventDefault();

							var $thisbutton  = $( this ),
								$form        = $thisbutton.closest( 'form.cart' ),
								id           = $thisbutton.val(),
								product_qty  = $form.find( 'input[name=quantity]' ).val() || 1,
								product_id   = $form.find( 'input[name=product_id]' ).val() || id,
								variation_id = $form.find( 'input[name=variation_id]' ).val() || 0;

							var data = {
								action: 'woocommerce_ajax_add_to_cart_penny',
								product_id: product_id,
								product_sku: '',
								quantity: product_qty,
								variation_id: variation_id,
								auction_bid: 1,
							};

							$.ajax(
								{
									type: 'post',
									url: wc_cart_fragments_params.ajax_url,
									data: data,
									beforeSend: function (response) {
										$thisbutton.removeClass( 'added' ).addClass( 'loading' );
									},
									complete: function (response) {
										$thisbutton.addClass( 'added' ).removeClass( 'loading' );
									},
									success: function (response) {

										if (response.error & response.product_url) {
											window.location = response.product_url;

										} else {
											if (response.status === 'notice') {
												$( ".woocommerce-message" ).remove();
												var node = document.getElementsByClassName( 'auction-software-form' );
												$( response.notice_message ).insertBefore( node );

												if (response.change_bid == 1) {
													var curBid = document.getElementsByClassName( 'auction_current_bid_penny' );
													$( curBid ).empty();
													$( curBid ).append( response.change_current_bid );

													var buyitnow = document.getElementsByClassName( 'single_add_to_cart_button' );
													if (response.remove_buy_it_now_cart_text == 1) {
														$( buyitnow ).remove();
													} else {
														$( buyitnow ).empty();
														$( buyitnow ).append( response.change_buy_it_now_cart_text );
													}

												}
												$( ".auction_reserve_price" ).load( location.href + " .auction_reserve_price", "" );
												$( ".auction_current_bid_penny" ).load( location.href + " .auction_current_bid_penny>*", "" );
												$( "#auction_history_table" ).load( location.href + " #auction_history_table>*", "" );
												$( ".auction_remaining_bids" ).load( location.href + " .auction_remaining_bids", "" );
												endDate = response.seconds;

											}
										}
									},
								}
							);

							return false;
						}
					);

					$( document ).on(
						'click',
						'.auction-bid-reverse',
						function (e) {
							e.preventDefault();

							var $thisbutton  = $( this ),
								$form        = $thisbutton.closest( 'form.cart' ),
								id           = $thisbutton.val(),
								product_qty  = $form.find( 'input[name=quantity]' ).val() || 1,
								product_id   = $form.find( 'input[name=product_id]' ).val() || id,
								variation_id = $form.find( 'input[name=variation_id]' ).val() || 0,
								price        = document.getElementById( 'auction-price-incr-decr' ).value || 0;

							var data = {
								action: 'woocommerce_ajax_add_to_cart_reverse',
								product_id: product_id,
								product_sku: '',
								quantity: product_qty,
								variation_id: variation_id,
								auction_bid: 1,
								price: price,
							};

							$.ajax(
								{
									type: 'post',
									url: wc_cart_fragments_params.ajax_url,
									data: data,
									beforeSend: function (response) {
										$thisbutton.removeClass( 'added' ).addClass( 'loading' );
									},
									complete: function (response) {
										$thisbutton.addClass( 'added' ).removeClass( 'loading' );
									},
									success: function (response) {

										if (response.error & response.product_url) {
											window.location = response.product_url;

										} else {
											if (response.status === 'notice') {
												$( ".woocommerce-message" ).remove();
												var node = document.getElementsByClassName( 'auction-software-form' );
												$( response.notice_message ).insertBefore( node );

												if (response.change_bid == 1) {
													var curBid = document.getElementsByClassName( 'auction_current_bid_reverse' );
													$( curBid ).empty();
													$( curBid ).append( response.change_current_bid );

													var pricebox   = document.getElementById( 'auction-price-incr-decr' );
													pricebox.value = response.change_pricebox_bid;
													setStartPrice  = response.change_pricebox_bid;

													var buyitnow = document.getElementsByClassName( 'single_add_to_cart_button' );
													if (response.remove_buy_it_now_cart_text == 1) {
														$( buyitnow ).remove();
													}

												}

												var max_bid = document.getElementById( 'auction_max_bid' );
												if (response.change_max_bid == 1) {
													$( max_bid ).css( {'display':'table-row'} );
													var max_bid_value = document.getElementsByClassName( 'auction_max_bid_reverse' );
													$( max_bid_value ).empty();
													$( max_bid_value ).append( response.change_max_bid_value );
												} else {
													$( max_bid ).hide();
												}

												$( ".auction_reserve_price" ).load( location.href + " .auction_reserve_price", "" );
												$( ".auction_current_bid_reverse" ).load( location.href + " .auction_current_bid_reverse>*", "" );
												$( ".auction_max_bid_reverse" ).load( location.href + " .auction_max_bid_reverse>*", "" );
												$( "#auction_history_table" ).load( location.href + " #auction_history_table>*", "" );
												endDate = response.seconds;

											}
										}
									},
								}
							);

							return false;
						}
					);

					if ($( "#auction-price-incr-decr" ).length) {
						$( '#auction-price-incr-decr' ).on(
							'input',
							function () {
								this.value = this.value
									.replace( /[^\d.]/g, '' )
									.replace( /(\..*)\./g, '$1' )
									.replace( /(\.[\d]{2})./g, '$1' );
							}
						);
						var currentBid                 = document.getElementById( 'auction_current_bid' );
						var auctionStartPrice          = document.getElementById( 'auction_start_price' );
						var bidIncrement               = document.getElementById( 'auction_bid_increment' );
						var auction_initial_bid_placed = document.getElementById( 'auction_initial_bid_placed' );
						var setStartPrice              = '';
						if (currentBid.value == 0 && auction_initial_bid_placed.value != 1) {
							setStartPrice = auctionStartPrice.value;
						} else if ($( ".auction-bid-simple" ).length) {
							setStartPrice = (parseFloat( currentBid.value ) + parseFloat( bidIncrement.value )).toFixed( 2 );
						} else if ($( ".auction-bid-reverse" ).length) {
							setStartPrice = (parseFloat( currentBid.value ) - parseFloat( bidIncrement.value )).toFixed( 2 );
						}

						var startPrice   = document.getElementById( 'auction-price-incr-decr' );
						startPrice.value = setStartPrice;
						var incrementPlus;
						var incrementMinus;
						var buttonPlus  = $( ".cart-price-plus" );
						var buttonMinus = $( ".cart-price-minus" );
						incrementPlus   = buttonPlus.click(
							function () {
								var $n = $( this )
									.parent( ".button-container" )
									.parent( ".container" )
									.find( ".price" );
								$n.val( Number( $n.val() ) + 1 );
							}
						);

						incrementMinus = buttonMinus.click(
							function () {
								var $n     = $( this )
									.parent( ".button-container" )
									.parent( ".container" )
									.find( ".price" );
								var amount = Number( $n.val() );
								$n.val( Number( $n.val() ) - 1 );
							}
						);
					}
				}
			} else {
				var startDate = '';
				if ($( "#start_date" ).length) {
					var getStartDate = document.getElementById( 'start_date' );
					startDate        = getStartDate.value;
				}
				if ($( "#product_type" ).length) {

					var x = setInterval(
						function () {

							var countDownDate = new Date( startDate.replace( /-/g, "/" ) ).getTime();

							var now = 0;
							if (php_vars.default) {
								var d           = new Date();
								var localTime   = d.getTime();
								var localOffset = d.getTimezoneOffset() * 60000;
								var utc         = localTime + localOffset;
								now             = utc + (3600000 * php_vars.timezone);
							} else {
								var timezoneTime = new Date().toLocaleString( "en-US", {timeZone: php_vars.timezone } );
								timezoneTime     = new Date( timezoneTime );
								now              = timezoneTime.getTime();
							}

							var distance = countDownDate - now;

							var days    = Math.floor( distance / (1000 * 60 * 60 * 24) );
							var hours   = Math.floor( (distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60) );
							var minutes = Math.floor( (distance % (1000 * 60 * 60)) / (1000 * 60) );
							var seconds = Math.floor( (distance % (1000 * 60)) / 1000 );

							document.getElementById( "time_start" ) != null ? document.getElementById( "time_start" ).innerHTML = '<div class="time_left-box"><p class="time-span">' + days + '</p><span class="label-span">'+php_vars.Days+'</span></div>' +
																																'<div class="time_left-box"><p class="time-span">' + hours + '</p><span class="label-span">'+php_vars.Hours+'</span></div>' +
																																'<div class="time_left-box"><p class="time-span">' + minutes + '</p><span class="label-span">'+php_vars.Minutes+'</span></div>' +
																																'<div class="time_left-box"><p class="time-span">' + seconds + '</p><span class="label-span">'+php_vars.Seconds+'</span></div>' : "";

							if (distance < 0) {
								clearInterval( x );
								if ($( "#time_start" ).length) {
									document.getElementById( "time_start" ).innerHTML = "Auction has started. Please refresh the page.";
								}
							}

						},
						1000
					);
				}
			}

			$( document ).on(
				'click',
				'.auctionadd-watchlist',
				function (e) {
					e.preventDefault();

					var id                  = $( this ).data( "value" );
					var addWatchlist        = document.getElementById( 'auctionadd-watchlist' );
					var removeWatchlist     = document.getElementById( 'auctionremove-watchlist' );
					var auctionLoginMessage = document.getElementById( 'auctionlogin_message' );

					var data = {
						action: 'woocommerce_ajax_add_to_auctionwatchlist',
						product_id: id,
					};

					$.ajax(
						{
							type: 'post',
							url: wc_cart_fragments_params.ajax_url,
							data: data,
							beforeSend: function (response) {

							},
							complete: function (response) {

							},
							success: function (response) {

								if (response.error & response.product_url) {
									window.location = response.product_url;

								} else {
									if (response == 'success') {
										addWatchlist.style.display    = 'none';
										removeWatchlist.style.display = 'block';
									} else if (response == 'notlogin') {
										addWatchlist.style.display        = 'none';
										removeWatchlist.style.display     = 'none';
										auctionLoginMessage.style.display = 'block';
									}
								}
							},
						}
					);

					return false;

				}
			);

			$( document ).on(
				'click',
				'.auctionremove-watchlist',
				function (e) {
					e.preventDefault();

					var id                  = $( this ).data( "value" );
					var addWatchlist        = document.getElementById( 'auctionadd-watchlist' );
					var removeWatchlist     = document.getElementById( 'auctionremove-watchlist' );
					var auctionLoginMessage = document.getElementById( 'auctionlogin_message' );

					var data = {
						action: 'woocommerce_ajax_remove_from_auctionwatchlist',
						product_id: id,
					};

					$.ajax(
						{
							type: 'post',
							url: wc_cart_fragments_params.ajax_url,
							data: data,
							beforeSend: function (response) {

							},
							complete: function (response) {

							},
							success: function (response) {

								if (response.error & response.product_url) {
									window.location = response.product_url;

								} else {
									if (response == 'success') {
										addWatchlist.style.display    = 'block';
										removeWatchlist.style.display = 'none';
									} else if (response == 'notlogin') {
										addWatchlist.style.display        = 'none';
										removeWatchlist.style.display     = 'none';
										auctionLoginMessage.style.display = 'block';
									}
								}
							},
						}
					);

					return false;

				}
			);

		}
	);
})( jQuery );
