<?php if ((!empty($ViewableData)) && (is_array($ViewableData)) && (!isset($_SESSION['currentUser']))):?>
	<?php 
        $errorMsg = 'Please check whether you have filled in all the fields.';
        foreach($ViewableData as $each){
            $errorMsg .= '<span class="errorSpan">'.$each.'</span>';
        }
        echo $errorMsg;	
    ?>
<?php else:?>
    <?php if (!isset($_SESSION['currentUser'])):?>
    	<span class="errorSpan">Oops. Invalid username or password</span>
    <?php elseif ((!is_array($ViewableData)) && (!isset($_POST['directLoginSubmit']))):?>
    	<span class="errorSpan">Please check whether you have filled in all the fields.</span>
    <?php endif;?>	    
<?php endif;?>	