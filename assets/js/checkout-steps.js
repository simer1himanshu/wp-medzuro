/**
 * Multi-step checkout (Delivery -> Details -> Payment).
 *
 * Shows one panel of woocommerce/checkout/form-checkout.php at a time and
 * validates the visible fields before moving on. WooCommerce's own
 * checkout.js still owns the form: order review refreshes, payment method
 * selection and submission are untouched.
 *
 * @package Medzuro
 */
( function ( $ ) {
	'use strict';

	var $form = $( 'form.checkout.mz-co' );
	if ( ! $form.length ) {
		return;
	}

	var i18n = window.medzuroCheckout || {};
	var STAGES = [ 'delivery', 'details', 'payment' ];
	var current = 0;

	// Pickup adds "How would you like to pay?" before the payment step.
	function panels() {
		return isPickup()
			? [ 'delivery', 'details', 'address', 'review', 'payopt', 'payment' ]
			: [ 'delivery', 'details', 'address', 'review', 'payment' ];
	}

	/* ------------------------------------------------------------------ */
	/* Delivery / pickup mode                                              */
	/* ------------------------------------------------------------------ */

	function chosenRate() {
		var $real = $form.find( 'input[name="shipping_method[0]"]:checked, input[name="shipping_method[0]"][type="hidden"]' ).first();
		if ( $real.length ) {
			return $real.val();
		}
		return $form.find( 'input[name="mz_delivery_choice"]:checked' ).val() || '';
	}

	function isPickup( rate ) {
		return /:pickup$/.test( rate || chosenRate() );
	}

	function applyMode() {
		var pickup = isPickup();
		$form.toggleClass( 'is-pickup', pickup ).toggleClass( 'is-delivery', ! pickup );
		applyOption();
	}

	// "Reserve without payment" hides the payment method list (design 7C).
	function applyOption() {
		var option = $form.find( '.mz-pay-summary' ).data( 'option' ) || ( isPickup() ? val( 'mz_pickup_payment' ) : 'delivery' );
		$form.toggleClass( 'is-reserve', isPickup() && 'reserve' === option );
	}

	// "Go back & pay 10%" on the reserve screen.
	$form.on( 'click', '.mz-switch-option', function ( e ) {
		e.preventDefault();
		var want = $( this ).data( 'option' );
		$form.find( 'input[name="mz_pickup_payment"]' ).filter( function () {
			return this.value === want;
		} ).prop( 'checked', true ).trigger( 'change' );
	} );

	// Proxy cards on step 1 drive the real radios inside the order review.
	$form.on( 'change', 'input[name="mz_delivery_choice"]', function () {
		var value = this.value;
		var $real = $form.find( 'input[name="shipping_method[0]"]' ).filter( function () {
			return this.value === value;
		} );

		if ( $real.length && ! $real.is( ':checked' ) ) {
			$real.prop( 'checked', true ).trigger( 'change' );
		}

		$form.toggleClass( 'is-pickup', isPickup( value ) ).toggleClass( 'is-delivery', ! isPickup( value ) );
	} );

	function syncProxy() {
		var rate = chosenRate();
		if ( rate ) {
			$form.find( 'input[name="mz_delivery_choice"]' ).each( function () {
				this.checked = this.value === rate;
			} );
		}
		applyMode();
	}

	/* ------------------------------------------------------------------ */
	/* Summaries shown on review/payment                                   */
	/* ------------------------------------------------------------------ */

	function val( name ) {
		var $f = $form.find( '[name="' + name + '"]' );
		if ( $f.is( ':radio' ) ) {
			$f = $f.filter( ':checked' );
		}
		return $.trim( $f.val() || '' );
	}

	function text( $el ) {
		return $.trim( $el.first().text().replace( /\s+/g, ' ' ) );
	}

	function updateSummaries() {
		var state = $form.find( '#billing_state option:selected' ).text();
		var address = [ ( val( 'billing_house_no' ) + ' ' + val( 'billing_address_1' ) ).trim(), val( 'billing_address_2' ), val( 'billing_city' ), state, 'Fiji' ].filter( Boolean ).join( ', ' );
		var contact = [ val( 'billing_first_name' ), val( 'billing_phone' ) ? '+679 ' + val( 'billing_phone' ) : '', val( 'billing_email' ) ].filter( Boolean ).join( ' · ' );

		$form.find( '[data-mz-summary="address"]' ).text( address );
		$form.find( '[data-mz-summary="contact"]' ).text( contact );

		var $store = $form.find( 'input[name="mz_pickup_store"]:checked' );
		if ( $store.length ) {
			$form.find( '[data-mz-summary="store"]' ).text( $store.data( 'name' ) );
		}
	}

	/* ------------------------------------------------------------------ */
	/* Validation                                                          */
	/* ------------------------------------------------------------------ */

	function setError( $row, message ) {
		$row.addClass( 'woocommerce-invalid woocommerce-invalid-required-field' ).removeClass( 'woocommerce-validated' );
		$row.find( '.mz-err' ).remove();
		$row.append( $( '<span class="mz-err" role="alert"></span>' ).text( message ) );
	}

	function clearError( $row ) {
		$row.removeClass( 'woocommerce-invalid woocommerce-invalid-required-field' );
		$row.find( '.mz-err' ).remove();
	}

	function validatePanel( name ) {
		var $panel = $form.find( '[data-panel="' + name + '"]' );
		var firstBad = null;

		$panel.find( '.form-row, fieldset.mz-contact-method' ).filter( ':visible' ).each( function () {
			var $row = $( this );
			var $inputs = $row.find( 'input, select, textarea' ).not( '[type="hidden"]' );
			if ( ! $inputs.length ) {
				return;
			}

			var required = $row.hasClass( 'validate-required' );
			var value = $inputs.is( ':radio' ) ? $inputs.filter( ':checked' ).val() || '' : $.trim( $inputs.val() || '' );
			var message = '';

			if ( required && ! value ) {
				message = i18n.required || 'Please fill in this field.';
			} else if ( value && $row.is( '#billing_phone_field' ) ) {
				var digits = value.replace( /\D+/g, '' ).replace( /^679(?=\d{7}$)/, '' );
				if ( digits.length !== 7 ) {
					message = i18n.phone || 'Enter a 7-digit Fiji mobile number.';
				}
			} else if ( value && $row.hasClass( 'validate-email' ) && ! /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test( value ) ) {
				message = i18n.email || 'Enter a valid email address.';
			}

			if ( message ) {
				setError( $row, message );
				firstBad = firstBad || $inputs.first();
			} else {
				clearError( $row );
			}
		} );

		if ( 'delivery' === name && ! chosenRate() ) {
			firstBad = $panel.find( 'input' ).first();
		}

		if ( firstBad ) {
			firstBad.trigger( 'focus' );
			return false;
		}
		return true;
	}

	$form.on( 'input change', '.form-row input, .form-row select, fieldset.mz-contact-method input', function () {
		var $row = $( this ).closest( '.form-row, fieldset.mz-contact-method' );
		if ( $row.hasClass( 'woocommerce-invalid' ) ) {
			clearError( $row );
		}
	} );

	/* ------------------------------------------------------------------ */
	/* Navigation                                                          */
	/* ------------------------------------------------------------------ */

	function show( index, opts ) {
		opts = opts || {};
		current = Math.max( 0, Math.min( panels().length - 1, index ) );

		var PANELS = panels();
		var name = PANELS[ current ];
		var $panel = $form.find( '[data-panel="' + name + '"]' );
		var stage = STAGES.indexOf( $panel.data( 'stage' ) );

		$form.find( '.mz-panel' ).removeClass( 'is-active' );
		$panel.addClass( 'is-active' );

		$form.find( '.mz-stepper__step' ).each( function ( i ) {
			$( this )
				.toggleClass( 'is-done', i < stage )
				.toggleClass( 'is-active', i === stage )
				.attr( 'aria-current', i === stage ? 'step' : null );
		} );

		if ( name === 'review' ) {
			updateSummaries();
		}

		if ( window.history && window.history.replaceState ) {
			window.history.replaceState( null, '', '#' + name );
		}

		if ( ! opts.noScroll ) {
			var top = $form.offset().top - 90;
			if ( $( window ).scrollTop() > top ) {
				$( 'html, body' ).animate( { scrollTop: top }, 200 );
			}
		}
	}

	function next() {
		if ( validatePanel( panels()[ current ] ) ) {
			show( current + 1 );
		}
	}

	function goTo( name ) {
		var PANELS = panels();
		var target = PANELS.indexOf( name );
		if ( target < 0 ) {
			return;
		}
		// Moving forward must pass every step in between.
		for ( var i = current; i < target; i++ ) {
			if ( ! validatePanel( PANELS[ i ] ) ) {
				show( i );
				return;
			}
		}
		show( target );
	}

	$form.on( 'click', '.mz-next', function ( e ) {
		e.preventDefault();
		next();
	} );

	$form.on( 'click', '.mz-back', function ( e ) {
		e.preventDefault();
		show( current - 1 );
	} );

	$form.on( 'click', '.mz-goto', function ( e ) {
		e.preventDefault();
		goTo( $( this ).data( 'goto' ) );
	} );

	$form.on( 'click', '.mz-stepper__step.is-done', function () {
		var stage = $( this ).data( 'stage' );
		var PANELS = panels();
		var first = PANELS.filter( function ( p ) {
			return $form.find( '[data-panel="' + p + '"]' ).data( 'stage' ) === stage;
		} )[ 0 ];
		show( PANELS.indexOf( first ) );
	} );

	// Enter in a field moves on instead of submitting early.
	$form.on( 'keydown', 'input:not([type="submit"])', function ( e ) {
		if ( 13 === e.which && 'payment' !== panels()[ current ] ) {
			e.preventDefault();
			next();
		}
	} );

	// A server-side error jumps to the panel holding the field.
	$( document.body ).on( 'checkout_error', function () {
		var id = $form.find( '.woocommerce-error li[data-id]' ).first().data( 'id' );
		var $target = id ? $form.find( '#' + id ) : $form.find( '.woocommerce-invalid' ).first();
		var panel = $target.closest( '.mz-panel' ).data( 'panel' );
		var PANELS = panels();

		if ( panel && panel !== PANELS[ current ] ) {
			show( PANELS.indexOf( panel ), { noScroll: true } );
		}
	} );

	$( document.body ).on( 'updated_checkout', function () {
		syncProxy();
		updateSummaries();
	} );

	$form.on( 'change', 'input[name="mz_pickup_payment"]', applyOption );
	$form.on( 'change', 'input[name="mz_pickup_store"]', updateSummaries );

	/* ------------------------------------------------------------------ */
	/* Start                                                               */
	/* ------------------------------------------------------------------ */

	$form.addClass( 'is-stepped' );
	syncProxy();

	var start = panels().indexOf( ( window.location.hash || '' ).replace( '#', '' ) );
	// Arriving from the cart (#details) the delivery choice is already made.
	// Never start past Details: earlier fields must be filled in first.
	show( start > 0 ? Math.min( start, 1 ) : 0, { noScroll: true } );
} )( jQuery );
