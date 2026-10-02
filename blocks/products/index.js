/**
 * AVDEB Products block — editor UI. Rendered on the server (render.php), previewed with ServerSideRender.
 * Plain ES5 against the wp.* globals so the plugin ships without a build step.
 */
( function ( blocks, element, blockEditor, components, i18n, ServerSideRender ) {
	var el = element.createElement;
	var __ = i18n.__;
	var InspectorControls = blockEditor.InspectorControls;
	var useBlockProps = blockEditor.useBlockProps;
	var PanelBody = components.PanelBody;
	var SelectControl = components.SelectControl;
	var TextControl = components.TextControl;
	var RangeControl = components.RangeControl;

	blocks.registerBlockType( 'avdeb/products', {
		edit: function ( props ) {
			var a = props.attributes;
			var set = props.setAttributes;

			var sourceField = {
				creator: el( TextControl, {
					label: __( 'Creator slug', 'avdeb-product-embeds' ),
					help: __( 'The last part of your creator page URL: avdeb.com/creators/your-slug/', 'avdeb-product-embeds' ),
					value: a.creator,
					onChange: function ( v ) { set( { creator: v } ); },
				} ),
				tag: el( TextControl, {
					label: __( 'Category slug', 'avdeb-product-embeds' ),
					help: __( 'For example "stickers" from avdeb.com/category/stickers/', 'avdeb-product-embeds' ),
					value: a.tag,
					onChange: function ( v ) { set( { tag: v } ); },
				} ),
				handle: el( TextControl, {
					label: __( 'Product handle', 'avdeb-product-embeds' ),
					help: __( 'The last part of the product URL: avdeb.com/store/product-handle/', 'avdeb-product-embeds' ),
					value: a.handle,
					onChange: function ( v ) { set( { handle: v } ); },
				} ),
				q: el( TextControl, {
					label: __( 'Search for', 'avdeb-product-embeds' ),
					help: __( 'For example "cat stickers" — the best matches are shown.', 'avdeb-product-embeds' ),
					value: a.q,
					onChange: function ( v ) { set( { q: v } ); },
				} ),
			}[ a.source ];

			return el(
				'div',
				useBlockProps(),
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Products', 'avdeb-product-embeds' ) },
						el( SelectControl, {
							label: __( 'Show', 'avdeb-product-embeds' ),
							value: a.source,
							options: [
								{ label: __( 'A creator\'s products', 'avdeb-product-embeds' ), value: 'creator' },
								{ label: __( 'A category', 'avdeb-product-embeds' ), value: 'tag' },
								{ label: __( 'One product', 'avdeb-product-embeds' ), value: 'handle' },
								{ label: __( 'Search results', 'avdeb-product-embeds' ), value: 'q' },
							],
							onChange: function ( v ) { set( { source: v } ); },
						} ),
						sourceField,
						a.source !== 'handle' && el( RangeControl, {
							label: __( 'Number of products', 'avdeb-product-embeds' ),
							value: a.limit,
							min: 1,
							max: 48,
							onChange: function ( v ) { set( { limit: v || 6 } ); },
						} ),
						a.source !== 'handle' && el( RangeControl, {
							label: __( 'Columns (0 = plugin setting)', 'avdeb-product-embeds' ),
							value: a.columns,
							min: 0,
							max: 6,
							onChange: function ( v ) { set( { columns: v || 0 } ); },
						} )
					)
				),
				el( ServerSideRender, { block: 'avdeb/products', attributes: a } )
			);
		},
		save: function () {
			return null;
		},
	} );
} )( window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.components, window.wp.i18n, window.wp.serverSideRender );
