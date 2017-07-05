// jquery-javascript //
(function($) {
	$( document ).ready( function() {
		$( document ).on( "click", "#abbey-contact-form-button", function( event ){
			event.preventDefault(); //prevent form from submitting normally //
			var ajaxUrl, resultDiv, error, errorMessage, spinner, button, jqueryData, alertClass, form;
			ajaxUrl = abbeyContact.ajax_url;
			
			alertClass = "";
			resultDiv = $( ".mini-popup" );
			form = $( "#abbey-contact-form" );
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
					jqueryData = $($.parseHTML( data ));
					alertClass = jqueryData.hasClass( "error" ) ? "warning" : "success";
					if( $.magnificPopup ){
						$.magnificPopup.open({
						  items: {
						    src: '<div class="mini-popup '+alertClass+'">'+data+'</div>',
						    type: 'inline', 

						  }, 
						  mainClass: 'bg-white'
						});
					}
					else{
						resultDiv.append(data).fadeIn( "slow" );
					}
					if( jqueryData.hasClass( "success" ) ){
						form.each( function(){ this.reset(); } );
					}
					else{
						form.find( "input" ).filter( ":first" ).focus();
					}
				},
				error: function ( xhr, status, message){
					alert( status + ": "+message );
				}, 
				beforeSend: function( xhr ){
					button.addClass( "btn-block" ).text( "Processing . . ." ).append( "<span><img src='"+spinner+"' /></span>" );
				}, 
				complete: function (  xhr ){
					button.removeClass( "btn-block" ).text( "Submit" );
					resultDiv.fadeOut( 5000 ).delay( 3000 );
				}

			});

		}); //on click abbey-contact-form-button //



	} ); //ready//
	
})( jQuery );