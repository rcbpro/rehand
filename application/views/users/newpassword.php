<div id="allWrapper">
    <div class="PageContent" style="width:260px;"><a id="back-to-rehand" href="<?php echo self::$globalConfig['base_url']?>users/profile/"></a>
	<h3 class="titlelogin">Change Password</h3>
    <div class="clearH"></div>
    <form id="new_PasswordForm" name="new_PasswordForm" action="" method="post">
        <input id="curpassword" type="password" class="InputBg" name="cur_Password[password]" />
		<input id="curpassword_text" type="text" class="InputBg" readonly="readonly" value="Current Password" />
        <div class="clearH10"></div>
        <input id="newpassword" type="password" class="InputBg" name="new_Password[password]" />
        <input id="newpassword_text" type="text" class="InputBg" readonly="readonly" value="New Password" />
        <div class="clearH10"></div>
        <input id="newpasswordConfirm" type="password" class="InputBg" name="new_Password[password]" />
        <input id="newpasswordConfirm_text" type="text" class="InputBg" readonly="readonly" value="Confirm Password" />
        <div id="forgotPassError"></div>
        <div id="forgotPassSuccess"></div>
        <div class="clearH10"></div>    
        <input type="button" id="recover_Password" name="recover_Password" value="Change Password" class="Button1" /></td>
        <div class="clear"></div>                               
    </form>
	</div>
</div>