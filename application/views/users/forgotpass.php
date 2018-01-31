<div id="forgotPassWrapper">
    <table id="newUserTable" border="0" cellpadding="0" cellspacing="0">
    <form id="forgotUser" name="forgotUser" action="" method="post">
        <tr>
            <td><span>Please enter Username / Email&nbsp;&nbsp;</span></td>
            <td>
            	<input id="forgotPass" type="text" name="forgotUser[email]" value="<?php echo (!empty($_POST['forgotUser']['email'])) ? $_POST['forgotUser']['email'] : ''?>" />
				<span id="availability_status"></span>
            </td>
        </tr>
        <tr>
            <td colspan="2">
            	<div class="smallHeightDiv"><!-- --></div>
            </td>
        </tr>                        
        <tr>
            <td colspan="2" align="center"><input type="submit" name="recover_Password" value="Recover Password" id="recoverPassword" /></td>
        </tr>        	                            
        <tr>
            <td colspan="2">
            	<div id="forgotPassError"><!-- --></div>        
            </td>
        </tr>                              
    </form>
    </table>
</div>