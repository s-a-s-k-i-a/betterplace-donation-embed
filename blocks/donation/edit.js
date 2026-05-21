/**
 * Editor view for the betterplace-embed/donation block.
 *
 * Build-step-free: uses the global `wp` namespace exposed by WordPress core.
 * No JSX, no @wordpress/scripts — keeps install simple.
 */
( function ( wp ) {
	'use strict';

	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var __ = wp.i18n.__;
	var sprintf = wp.i18n.sprintf;
	var registerBlockType = wp.blocks.registerBlockType;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var PanelBody = wp.components.PanelBody;
	var TextControl = wp.components.TextControl;
	var SelectControl = wp.components.SelectControl;
	var ToggleControl = wp.components.ToggleControl;
	var Placeholder = wp.components.Placeholder;

	registerBlockType( 'betterplace-embed/donation', {
		edit: function ( props ) {
			var attrs = props.attributes;
			var setAttr = function ( key ) {
				return function ( value ) {
					var update = {};
					update[ key ] = value;
					props.setAttributes( update );
				};
			};
			var setAttrInt = function ( key ) {
				return function ( value ) {
					var n = parseInt( value, 10 );
					var update = {};
					update[ key ] = isNaN( n ) ? 0 : n;
					props.setAttributes( update );
				};
			};

			var blockProps = useBlockProps( {
				className: 'bpde-block-edit',
			} );

			var preview;
			if ( ! attrs.project_id ) {
				preview = el(
					Placeholder,
					{
						icon: 'heart',
						label: __( 'Betterplace-Spendenformular', 'betterplace-donation-embed' ),
						instructions: __(
							'Bitte rechts in der Seitenleiste die betterplace-Projekt-ID eintragen.',
							'betterplace-donation-embed'
						),
					}
				);
			} else {
				var src =
					'https://www.betterplace.org/' +
					encodeURIComponent( attrs.lang || 'de' ) +
					'/donate/iframe/' +
					encodeURIComponent( attrs.receiver_type || 'project' ) + 's/' +
					encodeURIComponent( String( attrs.project_id ) ) +
					'?background_color=' + encodeURIComponent( attrs.background_color || 'ffffff' ) +
					'&color=' + encodeURIComponent( attrs.color || '6c9c2e' ) +
					'&donation_amount=' + encodeURIComponent( String( attrs.default_amount || 10 ) ) +
					'&bottom_logo=' + ( attrs.bottom_logo === false ? 'false' : 'true' ) +
					'&default_payment_method=' + encodeURIComponent( attrs.default_payment_method || '' ) +
					'&default_interval=' + encodeURIComponent( attrs.default_interval || 'single' );

				preview = el( 'div', { className: 'bpde-block-preview', style: { maxWidth: ( attrs.width || 600 ) + 'px', margin: '0 auto' } },
					el( 'div', {
						className: 'bpde-block-preview__overlay',
						'aria-hidden': 'true',
						style: {
							position: 'relative',
							border: '1px dashed #c3c4c7',
							borderRadius: '3px',
							padding: '.5em',
							background: '#f6f7f7',
						},
					},
						el( 'p', { style: { margin: '0 0 .5em', fontSize: '12px', color: '#50575e' } },
							sprintf(
								/* translators: %d: betterplace project ID. */
								__( 'Vorschau: betterplace-Projekt %d', 'betterplace-donation-embed' ),
								attrs.project_id
							)
						),
						el( 'iframe', {
							src: src,
							title: __( 'Spendenformular Vorschau', 'betterplace-donation-embed' ),
							loading: 'lazy',
							referrerPolicy: 'strict-origin-when-cross-origin',
							style: {
								display: 'block',
								border: 0,
								width: '100%',
								height: ( attrs.height || 800 ) + 'px',
								background: 'transparent',
								pointerEvents: 'none',
							},
						} )
					)
				);
			}

			return el( Fragment, null,
				el( InspectorControls, null,
					el( PanelBody, { title: __( 'Projekt', 'betterplace-donation-embed' ), initialOpen: true },
						el( TextControl, {
							label: __( 'Betterplace-Projekt-ID', 'betterplace-donation-embed' ),
							type: 'number',
							value: attrs.project_id || '',
							onChange: setAttrInt( 'project_id' ),
							help: __( 'Z. B. 4667 (Zahl ohne Bindestrich, aus der betterplace-URL).', 'betterplace-donation-embed' ),
						} ),
						el( SelectControl, {
							label: __( 'Empfängertyp', 'betterplace-donation-embed' ),
							value: attrs.receiver_type,
							options: [
								{ value: 'project', label: __( 'Projekt', 'betterplace-donation-embed' ) },
								{ value: 'fundraising_event', label: __( 'Spendenaktion', 'betterplace-donation-embed' ) },
								{ value: 'organisation', label: __( 'Organisation', 'betterplace-donation-embed' ) },
							],
							onChange: setAttr( 'receiver_type' ),
						} ),
						el( SelectControl, {
							label: __( 'Sprache', 'betterplace-donation-embed' ),
							value: attrs.lang,
							options: [
								{ value: 'de', label: 'Deutsch' },
								{ value: 'en', label: 'English' },
							],
							onChange: setAttr( 'lang' ),
						} )
					),
					el( PanelBody, { title: __( 'Spende', 'betterplace-donation-embed' ), initialOpen: false },
						el( TextControl, {
							label: __( 'Standard-Betrag (€)', 'betterplace-donation-embed' ),
							type: 'number',
							value: attrs.default_amount,
							onChange: setAttrInt( 'default_amount' ),
						} ),
						el( SelectControl, {
							label: __( 'Intervall', 'betterplace-donation-embed' ),
							value: attrs.default_interval,
							options: [
								{ value: 'single', label: __( 'Einmalig', 'betterplace-donation-embed' ) },
								{ value: 'monthly', label: __( 'Monatlich', 'betterplace-donation-embed' ) },
								{ value: 'yearly', label: __( 'Jährlich', 'betterplace-donation-embed' ) },
							],
							onChange: setAttr( 'default_interval' ),
						} ),
						el( SelectControl, {
							label: __( 'Bevorzugte Zahlungsart', 'betterplace-donation-embed' ),
							value: attrs.default_payment_method,
							options: [
								{ value: '', label: __( 'Keine Vorauswahl', 'betterplace-donation-embed' ) },
								{ value: 'paypal', label: 'PayPal' },
								{ value: 'stripe', label: 'Kreditkarte (Stripe)' },
								{ value: 'stripe_sepa_debit', label: 'SEPA-Lastschrift' },
								{ value: 'apple_pay', label: 'Apple Pay' },
								{ value: 'google_pay', label: 'Google Pay' },
							],
							onChange: setAttr( 'default_payment_method' ),
						} )
					),
					el( PanelBody, { title: __( 'Darstellung', 'betterplace-donation-embed' ), initialOpen: false },
						el( TextControl, {
							label: __( 'Akzentfarbe (Hex, ohne #)', 'betterplace-donation-embed' ),
							value: attrs.color,
							onChange: setAttr( 'color' ),
						} ),
						el( TextControl, {
							label: __( 'Hintergrund (Hex, ohne #)', 'betterplace-donation-embed' ),
							value: attrs.background_color,
							onChange: setAttr( 'background_color' ),
						} ),
						el( TextControl, {
							label: __( 'Breite (px)', 'betterplace-donation-embed' ),
							type: 'number',
							value: attrs.width,
							onChange: setAttrInt( 'width' ),
						} ),
						el( TextControl, {
							label: __( 'Höhe (px)', 'betterplace-donation-embed' ),
							type: 'number',
							value: attrs.height,
							onChange: setAttrInt( 'height' ),
						} ),
						el( ToggleControl, {
							label: __( 'betterplace-Logo am Fuß zeigen', 'betterplace-donation-embed' ),
							checked: !! attrs.bottom_logo,
							onChange: setAttr( 'bottom_logo' ),
						} )
					)
				),
				el( 'div', blockProps, preview )
			);
		},

		save: function () {
			// Dynamic block — output is rendered server-side via PHP render_callback.
			return null;
		},
	} );
} )( window.wp );
