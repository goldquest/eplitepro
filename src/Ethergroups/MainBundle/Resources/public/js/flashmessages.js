var flashmessages = {
		timeout: function() {
			if(typeof flashTimeout != 'undefined') {
				clearTimeout(flashTimeout);
			}
			flashTimeout = setTimeout(function() {
				$('#flash-messages').slideUp();
			}, 5000);
		},
		show: function(obj) {
		    $('#flash-messages').slideUp();
		    $('#flash-messages').empty().append(obj);
		    $('#flash-messages').slideDown();
		    this.timeout();
		},
		clickHandler: function() {
			// possibility to hide flash-messages with a click
		    $('#flash-messages').click(function(e) {
		        $(this).slideUp();
		        });
		}
};