( function( $ ) {
	$( document ).ready( function() {
		showModal();

		/**
		 * Show modal
		 */
		function showModal() {
			var data = {
				'action': OTNData.ajaxAction,
				'nonce':  OTNData.ajaxNonce,
			};

			$.ajax( {
				type: 'GET',
				url: OTNData.ajaxURL,
				data: data,
				success: function( response ) {
					if ( response.data ) {
						$( '#orbisTimesheetsNoticeModal' ).modal( 'show' );
					}
				}
			} );
		}
	} );
} )( jQuery );
