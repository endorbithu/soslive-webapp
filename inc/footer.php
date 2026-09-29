
	<!--<a href="https://seal.beyondsecurity.com/vulnerability-scanner-verification/soslive.info"><img src="https://seal.beyondsecurity.com/verification-images/soslive.info/vulnerability-scanner-2.gif" alt="Website Security Test" /></a>-->

<script>
    $(function(){
        
        $('[data-toggle="tooltip"]').tooltip();   
        
        $(".footer-menu").on("click", function(){
            var target = "#" + $(this).data("target");
            
            if ($(target).is(':visible')) {
                $(".toggle-hash").hide("fast");
            } else {
                $(".toggle-hash").hide("fast");
                $(target).show("fast");
                
                $("html, body").animate({scrollTop: $("footer").offset().top});
            }
            
        });
        
        $(".footer-menu-inside").on("click", function(){
            var target = "#" + $(this).data("target");
            
            if ($(target).is(':visible')) {
                $(".toggle-hash-inside").hide("fast");
            } else {
                $(".toggle-hash-inside").hide("fast");
                $(target).show("fast");
                
                $("html, body").animate({scrollTop: $("footer").offset().top});
            }
          
            
        });
        
      //valamiért nem megy: 
    
		
  
        
        
    });
    

        
</script>

</div>
</body></html>
<?php exit; ?>