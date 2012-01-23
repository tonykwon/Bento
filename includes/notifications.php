<?php if( $scms->logged_in() ){ ?>
    
    <li class="menu <?php if( $scms->notifications() > 0){?>some<?php } else { ?>none<?php } ?>" id="scms_notification">
        <a href="#" class="menu none"><?php echo $scms->notifications();?></a>
        <ul class="menu-dropdown" id="scms_notifications">
            <!--scms:feed:notifications-->
        </ul>
    </li>
    
<?php } ?>