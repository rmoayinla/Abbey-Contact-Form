// jquery-javascript //
(function($) {
	$( document ).ready( function() {
		$( document ).on( "click", "#abbey-contact-form-button", function( event ){
			event.preventDefault(); //prevent form from submitting normally //
			var ajaxUrl, resultDiv, error, errorMessage, spinner, button;
			ajaxUrl = abbeyContact.ajax_url;
			if ( $( ".mini-popup" ).length < 1 ){
				$( "body" ).append( "<div class='mini-popup'> </div>" );
			}

			resultDiv = $( ".mini-popup" );
			
			spinner = abbeyContact.spinner_url;
			button = $( this );
			var formValues = new FormData(document.getElementById("abbey-contact-form"));
			formValues.append( "action", "abbey_process_form" );

			$.ajax({
				url: ajaxUrl,
				data: formValues,
				processData: false, 
				contentType: false, 
				type: "POST",
				success: function( data ){
					alert( data );
					resultDiv.append(data).fadeIn( "slow" );
				},
				error: function ( xhr, status, message){
					alert( status + ": "+message );
				}, 
				beforeSend: function( xhr ){
					button.addClass( "btn-block" ).text( "Processing . . ." ).append( "<span><img src='"+spinner+"' /></span>" );
				}, 
				complete: function (  xhr ){
					button.removeClass( "btn-block" ).text( "Submit" ).addClass( "disabled" );
					resultDiv.fadeOut( 5000 ).delay( 3000 );
				}

			});

		}); //on click abbey-contact-form-button //



	} ); //ready//
	
})( jQuery );