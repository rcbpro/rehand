<div id="allWrapper">
    <div class="PageContent">
		<a id="back-to-rehand" href="<?php echo self::$globalConfig['base_url']?>users/profile/"></a>
        <h3 class="titlelogin">Deactivate Account</h3>
        <div class="clearH"></div>
        <form id="passwordForDeactivateForm" name="passwordForDeactivateForm" action="" method="post">
            <input id="yourpassword" type="password" class="InputBg" name="your_Password[password]" />
            <input id="yourpassword_text" type="text" class="InputBg" readonly="readonly" value="Your Password" />
            <div class="clearH10"></div>
            <div id="forgotPassErrorInNewPassEnter">
            <?php if ($ViewableData['error']):?>
            <span class="Error">Please provide the correct password!</span>
            <?php endif;?>
            </div>        
            <input type="submit" id="passwordForDeactivate" name="passwordForDeactivate" value="Deactivate" class="RedBut" />
            <div class="clearH20"></div>
            <span style="font-size:12px;">You can reactivate your account once you log with rehand!</span>
        </form>
    </div>
</div>