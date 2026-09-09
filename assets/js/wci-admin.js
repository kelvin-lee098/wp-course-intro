/* global jQuery, wp, wciAdmin */
( function ( $ ) {
	'use strict';

	/**
	 * Open the WP media frame for a person row and store the chosen image.
	 *
	 * @param {jQuery} $row Person row.
	 */
	function pickImage( $row ) {
		var frame = wp.media( {
			title: wciAdmin.chooseImage,
			button: { text: wciAdmin.useImage },
			library: { type: 'image' },
			multiple: false
		} );

		frame.on( 'select', function () {
			var attachment = frame.state().get( 'selection' ).first().toJSON();
			var url = attachment.sizes && attachment.sizes.thumbnail
				? attachment.sizes.thumbnail.url
				: attachment.url;

			$row.find( '.wci-person__image-id' ).val( attachment.id );
			$row.find( '.wci-person__preview' )
				.removeClass( 'is-empty' )
				.html( '<img src="' + url + '" alt="" />' );
			$row.find( '.wci-person__clear' ).prop( 'hidden', false );
		} );

		frame.open();
	}

	/**
	 * Re-index name/image_id field keys after add/remove so PHP receives a clean array.
	 *
	 * @param {jQuery} $list The .wci-people__list wrapper.
	 * @param {string} baseKey e.g. _wci_instructors
	 */
	function reindex( $list, baseKey ) {
		$list.children( '.wci-person' ).each( function ( i ) {
			$( this ).find( '.wci-person__image-id' )
				.attr( 'name', baseKey + '[' + i + '][image_id]' );
			$( this ).find( '.wci-person__name' )
				.attr( 'name', baseKey + '[' + i + '][name]' );
		} );
	}

	$( document ).ready( function () {

		$( '.wci-people' ).on( 'click', '.wci-person__pick', function ( e ) {
			e.preventDefault();
			pickImage( $( this ).closest( '.wci-person' ) );
		} );

		$( '.wci-people' ).on( 'click', '.wci-person__clear', function ( e ) {
			e.preventDefault();
			var $row = $( this ).closest( '.wci-person' );
			$row.find( '.wci-person__image-id' ).val( '' );
			$row.find( '.wci-person__preview' ).addClass( 'is-empty' ).empty();
			$( this ).prop( 'hidden', true );
		} );

		$( '.wci-people' ).on( 'click', '.wci-person__remove', function ( e ) {
			e.preventDefault();
			var $list = $( this ).closest( '.wci-people__list' );
			var baseKey = $list.closest( '.wci-people' ).find( '.wci-people__add' ).data( 'key' );

			if ( $list.children( '.wci-person' ).length > 1 ) {
				$( this ).closest( '.wci-person' ).remove();
			} else {
				// Keep one empty row.
				$( this ).closest( '.wci-person' ).find( 'input' ).val( '' );
				$( this ).closest( '.wci-person' ).find( '.wci-person__preview' ).addClass( 'is-empty' ).empty();
				$( this ).closest( '.wci-person' ).find( '.wci-person__clear' ).prop( 'hidden', true );
			}

			reindex( $list, baseKey );
		} );

		$( '.wci-people__add' ).on( 'click', function ( e ) {
			e.preventDefault();
			var baseKey = $( this ).data( 'key' );
			var $list = $( this ).closest( '.wci-people' ).find( '.wci-people__list' );
			var tmpl = $( '#tmpl-wci-person-row' ).html();
			var index = $list.children( '.wci-person' ).length;

			var html = tmpl
				.replace( /__KEY__/g, baseKey )
				.replace( /__INDEX__/g, index );

			$list.append( html );
			reindex( $list, baseKey );
		} );
	} );

}( jQuery ) );
