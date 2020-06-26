/**
 * Admin Settings JavaScript.
 *
 * @package    Auction_Software
 * @subpackage Auction_Software/admin
 * @author     WPeka Club <support@wpeka.com>
 */

(function ($, data, wp, ajaxurl) {
	$(
		function () {

			function byKey(key) {
				return function (o) {
					var v = parseInt( o[key], 10 );
					return isNaN( v ) ? o[key] : v;
				};
			}

			var $tbody      = $( '.wc-auction-class-rows' ),
			$save_button    = $( '.wc-auction-class-save' ),
			$row_template   = wp.template( 'wc-auction-class-row' ),
			$blank_template = wp.template( 'wc-auction-class-row-blank' ),

			// Backbone model.
			AuctionClass = Backbone.Model.extend(
				{
					changes: {},
					logChanges: function (changedRows) {
						var changes = this.changes || {};

						_.each(
							changedRows,
							function (row, id) {
								changes[id] = _.extend( changes[id] || {term_id: id}, row );
							}
						);

						this.changes = changes;
						this.trigger( 'change:classes' );
					},
					save: function () {
						if (_.size( this.changes )) {
							$.post(
								ajaxurl + (ajaxurl.indexOf( '?' ) > 0 ? '&' : '?') + 'action=auction_software_save_wc_classes',
								{
									wc_auction_classes_nonce: data.wc_auction_classes_nonce,
									changes: this.changes
								},
								this.onSaveResponse,
								'json'
							);
						} else {
							auctionClass.trigger( 'saved:classes' );
						}
					},
					discardChanges: function (id) {
						var changes = this.changes || {};

						// Delete all changes.
						delete changes[id];

						// No changes? Disable save button.
						if (0 === _.size( this.changes )) {
							auctionClassView.clearUnloadConfirmation();
						}
					},
					onSaveResponse: function (response, textStatus) {
						if ('success' === textStatus) {
							if (response.success) {
								auctionClass.set( 'classes', response.data.auction_classes );
								auctionClass.trigger( 'change:classes' );
								auctionClass.changes = {};
								auctionClass.trigger( 'saved:classes' );
							} else if (response.data) {
								window.alert( response.data );
							} else {
								window.alert( data.strings.save_failed );
							}
						}
						auctionClassView.unblock();
					}
				}
			),

			// Backbone view.
			AuctionClassView = Backbone.View.extend(
				{
					rowTemplate: $row_template,
					initialize: function () {
						this.listenTo( this.model, 'change:classes', this.setUnloadConfirmation );
						this.listenTo( this.model, 'saved:classes', this.clearUnloadConfirmation );
						this.listenTo( this.model, 'saved:classes', this.render );
						$tbody.on( 'change', {view: this}, this.updateModelOnChange );
						$( window ).on( 'beforeunload', {view: this}, this.unloadConfirmation );
						$save_button.on( 'click', {view: this}, this.onSubmit );
						$( document.body ).on( 'click', '.wc-auction-class-add', {view: this}, this.onAddNewRow );
						$( document.body ).on( 'click', '.wc-auction-class-save-changes', {view: this}, this.onSubmit );
					},
					block: function () {
						$( this.el ).block(
							{
								message: null,
								overlayCSS: {
									background: '#fff',
									opacity: 0.6
								}
							}
						);
					},
					unblock: function () {
						$( this.el ).unblock();
					},
					render: function () {
						var classes = _.indexBy( this.model.get( 'classes' ), 'term_id' ),
						view        = this;

						this.$el.empty();
						this.unblock();

						if (_.size( classes )) {
							// Sort classes.
							classes = _.sortBy( classes, byKey( 'name' ) );

							// Populate $tbody with the current classes.
							$.each(
								classes,
								function (id, rowData) {
									view.renderRow( rowData );
								}
							);
						} else {
							view.$el.append( $blank_template );
						}
					},
					renderRow: function (rowData) {
						var view = this;
						view.$el.append( view.rowTemplate( rowData ) );
						view.initRow( rowData );
					},
					initRow: function (rowData) {
						var view = this;
						var $tr  = view.$el.find( 'tr[data-id="' + rowData.term_id + '"]' );

						// Support select boxes.
						$tr.find( 'select' ).each(
							function () {
								var attribute = $( this ).data( 'attribute' );
								$( this ).find( 'option[value="' + rowData[attribute] + '"]' ).prop( 'selected', true );
							}
						);

						// Make the rows function.
						$tr.find( '.view' ).show();
						$tr.find( '.edit' ).hide();
						$tr.find( '.wc-auction-class-edit' ).on( 'click', {view: this}, this.onEditRow );
						$tr.find( '.wc-auction-class-delete' ).on( 'click', {view: this}, this.onDeleteRow );
						$tr.find( '.editing .wc-auction-class-edit' ).trigger( 'click' );
						$tr.find( '.wc-auction-class-cancel-edit' ).on( 'click', {view: this}, this.onCancelEditRow );

						// Editing?
						if (true === rowData.editing) {
							$tr.addClass( 'editing' );
							$tr.find( '.wc-auction-class-edit' ).trigger( 'click' );
						}
					},
					onSubmit: function (event) {
						event.data.view.block();
						event.data.view.model.save();
						event.preventDefault();
					},
					onAddNewRow: function (event) {
						event.preventDefault();

						var view = event.data.view,
						model    = view.model,
						classes  = _.indexBy( model.get( 'classes' ), 'term_id' ),
						changes  = {},
						size     = _.size( classes ),
						newRow   = _.extend(
							{},
							data.default_auction_class,
							{
								term_id: 'new-' + size + '-' + Date.now(),
								editing: true,
								newRow: true
							}
						);

						changes[newRow.term_id] = newRow;

						model.logChanges( changes );
						view.renderRow( newRow );
						$( '.wc-auction-classes-blank-state' ).remove();
					},
					onEditRow: function (event) {
						event.preventDefault();
						$( this ).closest( 'tr' ).addClass( 'editing' );
						$( this ).closest( 'tr' ).find( '.view' ).hide();
						$( this ).closest( 'tr' ).find( '.edit' ).show();
						event.data.view.model.trigger( 'change:classes' );
					},
					onDeleteRow: function (event) {
						var view = event.data.view,
						model    = view.model,
						classes  = _.indexBy( model.get( 'classes' ), 'term_id' ),
						changes  = {},
						term_id  = $( this ).closest( 'tr' ).data( 'id' );

						event.preventDefault();

						if (classes[term_id]) {
							delete classes[term_id];
							changes[term_id] = _.extend( changes[term_id] || {}, {deleted: 'deleted'} );
							model.set( 'classes', classes );
							model.logChanges( changes );
						}

						view.render();
					},
					onCancelEditRow: function (event) {
						var view = event.data.view,
						model    = view.model,
						row      = $( this ).closest( 'tr' ),
						term_id  = $( this ).closest( 'tr' ).data( 'id' ),
						classes  = _.indexBy( model.get( 'classes' ), 'term_id' );

						event.preventDefault();
						model.discardChanges( term_id );

						if (classes[term_id]) {
							classes[term_id].editing = false;
							row.after( view.rowTemplate( classes[term_id] ) );
							view.initRow( classes[term_id] );
						}

						row.remove();
					},
					setUnloadConfirmation: function () {
						this.needsUnloadConfirm = true;
						$save_button.removeAttr( 'disabled' );
					},
					clearUnloadConfirmation: function () {
						this.needsUnloadConfirm = false;
						$save_button.attr( 'disabled', 'disabled' );
					},
					unloadConfirmation: function (event) {
						if (event.data.view.needsUnloadConfirm) {
							event.returnValue        = data.strings.unload_confirmation_msg;
							window.event.returnValue = data.strings.unload_confirmation_msg;
							return data.strings.unload_confirmation_msg;
						}
					},
					updateModelOnChange: function (event) {
						var model = event.data.view.model,
						$target   = $( event.target ),
						term_id   = $target.closest( 'tr' ).data( 'id' ),
						attribute = $target.data( 'attribute' ),
						value     = $target.val(),
						classes   = _.indexBy( model.get( 'classes' ), 'term_id' ),
						changes   = {};

						if ( ! classes[term_id] || classes[term_id][attribute] !== value) {
							changes[term_id]            = {};
							changes[term_id][attribute] = value;
						}

						model.logChanges( changes );
					}
				}
			),
			auctionClass     = new AuctionClass(
				{
					classes: data.classes
				}
			),
			auctionClassView = new AuctionClassView(
				{
					model: auctionClass,
					el: $tbody
				}
			);

			auctionClassView.render();
		}
	);
})( jQuery, auctionClassesLocalizeScript, wp, ajaxurl );
