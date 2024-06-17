<?php
/**
 * The template for displaying the footer
 */
?>
</div>
<?php

    /**
     * Before Footer area
     */
    aqualine_the_subscribe_block();

    aqualine_the_before_footer();
    
?>
</div>


 <!-- Footer Code start -->
    
    
    <div class="ltx-footer-wrapper">
    <div class="footer_bg">
    <div class="footer_section">
        <div class="footer_child">
           
            <img src="https://canberraautodetailing.com.au/wp-content/uploads/2019/06/site-logo-big-copy-2.png" alt="image">
            <p>We deliver ceramic coating services, paint correction, and high-level car detailing services that elevate the look and feel of your vehicle.</p>
            
        </div>
        <div class="footer_child">
            <?php dynamic_sidebar( 'footer-2' ); ?> 
        </div>
        <div class="footer_child">
            <?php dynamic_sidebar( 'footer-3' ); ?> 
        </div>
        <div class="footer_child">
            <?php dynamic_sidebar( 'footer-4' ); ?> 
        </div>
    </div>
    
    
   <?php
     aqualine_the_footer_overlay();
    /**
     * Copyright
     */
    aqualine_the_copyrights_section();
    ?>

<!-------- close Landing Menu --------->	
		
    </div>
    </div>
<?php 

    aqualine_the_go_top();

    /**
     * WordPress Core Functions
     */   
    wp_footer();
?>
</body>
</html>
