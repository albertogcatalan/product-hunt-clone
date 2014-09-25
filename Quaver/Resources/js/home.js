$(function () {	
	
	

    var track_load = 3; //total loaded record group(s)
    var loading  = false; //to prevents multipal ajax loads
    var last_date;
    
    $(window).scroll(function() { //detect page scroll
        
        if($(window).scrollTop() + $(window).height() == $(document).height())  //user scrolled to bottom of the page?
        {
            
            last_date = $(".date:last").attr('datetime');
            console.log(last_date);

            if(loading == false) //there's more data to load
            {
                loading = true; //prevent further ajax loading
                $('.animation_image').show(); //show loading image
                
                //load data from the server using a HTTP POST request
                $.post('/Ajax/autoloadMore.php',{'last_date': last_date}, function(data){
                                    
                    $("#day-results").append(data); //append received data into the element

                    //hide loading image
                    $('.animation_image').hide(); //hide loading image once data is received
                    
                    track_load++; //loaded group increment
                    loading = false; 
                
                }).fail(function(xhr, ajaxOptions, thrownError) { //any errors?
                    
                    alert(thrownError); //alert with HTTP error
                    $('.animation_image').hide(); //hide loading image
                    loading = false;
                
                });
                
            }
        }
    });


});