$(function () {
	
	requestSent = false;

	$("[rel='tooltip']").tooltip();
	
	$(".point-content").on("click", ".vote", function(e){
    	e.preventDefault();
    	var pid = $(this).data('id');
    	var status = $(this).data('status');
    	var counter = $('.vote-count[data-id='+pid+']');
		var content = $('.point-content[data-id='+pid+']');


        // Check and send state 
		if (status){
			var new_a = '<a href="#" class="vote" data-id='+ pid + ' data-status="0"><i class="vote-icon fa fa-caret-square-o-up fa-2x"></i></a>';
			counter.html(parseInt(counter.html())-1);
		} else {
			var new_a = '<a href="#" class="vote" data-id='+ pid + ' data-status="1"><i class="vote-icon-active fa fa-caret-square-o-up fa-2x"></i></a>';
			counter.html(parseInt(counter.html())+1);
		}

		content.html(new_a);

		if(!requestSent) {
	    	requestSent = true;

	    	$.ajax({
	            type: 'POST',
	            url:  '/Ajax/postPoint.php',
	            async: false,
	            dataType: "html",
	            data: 
	                { 
	                    pid: pid,
	                    status: status,
	                }
	        }).done(function (data){

				requestSent = false;
				var json = JSON.parse(data);
				
				$("[rel='tooltip']").tooltip();
				
				content.html(json);
	            
	        }).fail(function (data){
	        	requestSent = false;

	        	if (status){
	        		counter.html(parseInt(counter.html())-1);
	        	} else {
	        		counter.html(parseInt(counter.html())+1);
	        	}

	        	content.html('<a href="#" class="vote" data-id='+ pid + ' data-status="0"><i class="vote-icon fa fa-caret-square-o-up fa-2x"></i></a>');
	        });

			
		};
	});

});