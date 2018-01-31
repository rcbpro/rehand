<?php 
	header('Content-type: text/javascript');
	defined('WEB_PATH') ? NULL : define('WEB_PATH', 'http://'.$_SERVER['HTTP_HOST'].'/www.rehand.com');
?>
$(document).ready(function(){

	var loginClicked = false,
        regClicked = false,
        emailReg = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/,    
    	userIdAvailability = false,
        userIdAvailability2 = false;

	// This is for the Jquery login box
    $('#directLogin').click(function() {

		var id = "",
        	maskHeight = "",
            maskWidth = "",
            winH = "",
            winW  = "";
        	
        $("#login_recover_dialog").css({"display" : "none"});
    	$("#login_boxes").css({"display" : "block"});
    
        //Get the A tag
        id = $("#login_dialog");
     
        //Get the screen height and width
        maskHeight = $(document).height();
        maskWidth = $(window).width();
     
        //Set height and width to mask to fill up the whole screen
        $('#login_mask').css({'width':maskWidth,'height':maskHeight});
         
        //transition effect    
        $('#login_mask').fadeIn(1000);   
        $('#login_mask').fadeTo("slow",0.8); 
     
        //Get the window height and width
        winH = $(window).height();
        winW = $(window).width();
               
        //Set the popup window to center
        $(id).css('top',  winH/2-$(id).height()/2);
        $(id).css('left', winW/2-$(id).width()/2);
     
        //transition effect
        $(id).fadeIn(2000);
     
    });
    //if close button is clicked
    $('.login_window .close').click(function (e) {
        //Cancel the link behavior
        e.preventDefault();
        $('#login_mask, .login_window').hide();
    });    
    //if mask is clicked
    $('#login_mask').click(function () {
        $(this).hide();
        $('.login_window').hide();
    });        
   // This is for the secondary login and registration buttons 
    $("#secondaryLoginLink").click(function(){
    	$('.window .close').trigger("click");
    	$('#directLogin').trigger("click");
   	});
    $("#secondaraySignUpLink").click(function(){
    	$('.login_window .close').trigger("click");
    	$('#RegisterBut').trigger("click");
    });
    // This is for the top left login and register text links
    $("#textLogin").click(function(){
    	$('#directLogin').trigger("click");
    });
    $("#textRegister").click(function(){
    	$('#RegisterBut').trigger("click");
    });
    
    $('#RegisterButResponsive').click(function(){
    	$('#RegisterBut').trigger('click');
    });
    
	// This is for the Jquery registration box
    // select all the a tag with name equal to modal
    $('#RegisterBut').click(function() {
		var id = "",
        	maskHeight = "",
            maskWidth = "",
            winH = "",
            winW  = "";
        	
        //Get the A tag
        id = $("#dialog");
     
        //Get the screen height and width
        maskHeight = $(document).height();
        maskWidth = $(window).width();
     
        //Set height and width to mask to fill up the whole screen
        $('#mask').css({'width':maskWidth,'height':maskHeight});
         
        //transition effect    
        $('#mask').fadeIn(1000);   
        $('#mask').fadeTo("slow",0.8); 
     
        //Get the window height and width
        winH = $(window).height();
        winW = $(window).width();
               
        //Set the popup window to center
        $(id).css('top',  winH/2-$(id).height()/2);
        $(id).css('left', winW/2-$(id).width()/2);
     
        //transition effect
        $(id).fadeIn(2000);
     
    });
    //if close button is clicked
    $('.window .close').click(function (e) {
        //Cancel the link behavior
        e.preventDefault();
        $('#mask, .window').hide();
    });    
    //if mask is clicked
    $('#mask').click(function () {
        $(this).hide();
        $('.window').hide();
    }); 
	
	$("#directLogin").click(function() {
        if ($("#directLoginError").css("display") == "block"){
           $("#directLoginError").css({"display" : "none"});            
        }
		if ((!loginClicked) && (!regClicked)){
			loginClicked = true;            
			$("#directLoginDiv").show(1000);
            $("#fbLoginUrl").css({"position" : "absolute", "top" : "121px", "left" : "11px"});                   	                                    
            $("#fbRegisterButton").css({"position" : "absolute", "top" : "23px", "left" : "112px", "margin-top" : "125px"});                   	                                                
            $("#forgotPassUrl").css({"position" : "absolute", "top" : "52px", "left" : "37px", "margin-top" : "125px"});                   	                                                                        
            $("#directLoginDiv").css({"right": "15px", "top" : "53px"});                   	            
            $("#authenticateDiv").css({"height": "175px"});
		}else{
			loginClicked = false;		
			$("#directLoginDiv").hide(1000);
            $("#fbLoginUrl").css({"position" : "absolute", "top" : "26px", "left" : "9px"});                   	                                    
            $("#fbRegisterButton").css({"position" : "absolute", "top" : "-70px", "left" : "111px", "margin-top" : "125px"});                   	                                                
            $("#forgotPassUrl").css({"position" : "absolute", "top" : "-40px", "left" : "35px", "margin-top" : "125px"});                   	                                                                                    
            $("#directLoginDiv").css({"right": "15px", "top" : "53px"});                   	            
            $("#authenticateDiv").css({"height": "92px"});
		}	
	}); 
    $("#directLoginPassword").keypress(function(event) {
  		if (event.which == 13) {
   			$("#directLoginSubmit").trigger("click");		
        }
    });
    
    $("#directLoginSubmit").click(function() {

		var login_errors = "We were unable to sign you in. If you forgot your password you can <a href='javascript:void(0)' id='ResetPass'>reset it</a>";                        
        if (
        	($("#directLoginEmail").val() == '') || 
            ($("#directLoginPassword").val() == 'Email Address') ||
            ($("#directLoginPassword").val() == '') ||
            ($("#directLoginPassword").val() == 'Password')
           ){
                $("#directLoginError").html(login_errors);                        
                $("#directLoginError").show(500);            
                setTimeout(function(){
                    $("#directLoginError").hide(500);    	                                                            
                    $("#directLoginDiv").show(500);    	                                
                }, 8000);
                // This is for the reset link
                $("#ResetPass").click(function(){
                    $('#login_mask').trigger('click');
                    $('#ForgotPassword').trigger('click');	
                })

                return false;
        }else{
            $.ajax({  
                type: "POST",
                url: "<?php echo WEB_PATH?>/library/ajaxFunctions.php?action=checkLogin", 
                data: "username=" + $("#directLoginEmail").val() + "&password=" + $("#directLoginPassword").val(), 
                async: true,
                success: function(server_response){				
                    if (server_response == '0'){
                        $("#directLoginError").html(login_errors);                        
                        $("#directLoginError").show(500);            
                        setTimeout(function(){
                            $("#directLoginError").hide(500);    	                                                            
                            $("#directLoginDiv").show(500);    	                                
                        }, 8000);
                        return false;
                    }else{
                		$("#directLoginForm").submit();
                    }
                }
            });
        } 
    });
     
	$("#user_id").change(function(){ 
		var username = $("#user_id").val();
		$("#availability_status").html('<img src="<?php echo WEB_PATH?>/public/images/loader.gif" class="Floatmargin"> <span>Checking availability...</span>');		
        $("#registrationFormWrapper").css({"width": "600px", "padding-left" : "100px"});        
		$.ajax({  
			type: "POST",
			url: "<?php echo WEB_PATH?>/library/ajaxFunctions.php?action=checkUname", 
			data: "username=" + username, 
			success: function(server_response){	
				if (server_response == '0'){
					if (!emailReg.test(username)){                
                        $("#registrationFormWrapper").css({"width": "600px", "padding-left" : "100px"});                    
						$("#availability_status").html('<img src="<?php echo WEB_PATH?>/public/images/not_available.png" class="Floatmargin"><div class="clear"></div> <span>Oops. Your email address is invalid.</span>');	  
                        $("#requiredForEmailImg").addClass("hide");                  
                    }else{
						userIdAvailability = true;                    
                        $("#registrationFormWrapper").css({"width": "500px", "padding-left" : "50px"});                                            
						$("#availability_status").html('<img src="<?php echo WEB_PATH?>/public/images/available.png" class="Floatmargin"><div class="clear"></div> <span class="Green">Yay! This email address is Available.</span>');	
                    	$("#requiredForEmailImg").addClass("hide");
                    }    
				}else{
					$("#registrationFormWrapper").css({"width": "525px", "padding-left" : "75px"});                                                            
					$("#availability_status").html('<img class="notAvailable Floatmargin" src="<?php echo WEB_PATH?>/public/images/not_available.png"><div class="clear"></div> <span>Oops. This email address is Not Available.</span>');                                        
					$("#requiredForEmailImg").addClass("hide");
                }
                $("#registerErrorMsg").hide();
		}});
	});

	$("#password").change(function(){ 
        if ($("#password").val().length < 6){
            $("#registrationFormWrapper").css({"width": "600px", "padding-left" : "100px"});                   	        
        	$("#password_status2").html('<img src="<?php echo WEB_PATH?>/public/images/not_available.png" class="Floatmargin"><div class="clear"></div> <span>Oops. your password need to have at least 6 characters.</span>'); 
            $("#requiredForPasswordImg").addClass("hide");
        }else{
			$("#password_status2").html('<img src="<?php echo WEB_PATH?>/public/images/available.png" class="Floatmargin"><div class="clear"></div> <span class="Green"></span>');
            $("#requiredForPasswordImg").addClass("hide");	        	                       
        }
        $("#registerErrorMsg").hide();        
    });       

    $("#password").keypress(function(event) {
        if (event.which == 13) {
            $("#newUserRegister").trigger("click");		
        }
    });

	$("#name").change(function(){ 
        if ($("#name").val() == ''){
            $("#registrationFormWrapper").css({"width": "600px", "padding-left" : "100px"});                   	        
        	$("#name_status").html('<img src="<?php echo WEB_PATH?>/public/images/not_available.png" class="Floatmargin">'); 
            $("#requiredForNameImg").addClass("hide");
        }else{
			$("#name_status").html('<img src="<?php echo WEB_PATH?>/public/images/available.png" class="Floatmargin">');
            $("#requiredForNameImg").addClass("hide");	        	                       
        }
      
        $("#registerErrorMsg").hide();        
    });       

	$("#newUserRegister").click(function () {
        var errors = "",
        	errMsg = "",
            errorCount = 0;
        $("#registerErrorMsg").html("");	
        
        if ( 
        	(($("#name").val() == "Name") || ($("#name").val() == "")) &&
        	(($("#password").val() == "Password") || ($("#password").val() == "")) && 
            (($("#user_id").val() == "Email Address") || ($("#user_id").val() == ""))  
           ){
                errorCount += 1;                
        		errors = "Please complete the following fields: <br /><div>Name<br />Email<br />Password</div>";
        }else{
        	if (($("#user_id").val() == "Email Address") || ($("#user_id").val() == "")){
                errorCount += 1;                
	        	errors += "<span>Oops. Email field is empty<br />";
            }else{
                if (!emailReg.test($("#user_id").val())){                        	
                    errors += "<span>Oops. Email address is invalid</span><br />";        
                    errorCount += 1;                                            	
                }else{
                    if (!userIdAvailability){
                        errors += "<span>Oops. This email address is already registered.</span><br />";        
                        errorCount += 1;                                            	                	
                    }            	
                }
           	}
        	if (($("#password").val() == "Password") || ($("#password").val() == "")){
                errorCount += 1;                
	        	errors += "<span>Oops. Please enter a password.<br />";
           	}else{
                  if ($("#password").val().length < 6){
                      errors += "<span>Oops. Your password has to be at least 6 characters long.</span><br />";        
                      errorCount += 1;                                
                  }            	
            }
            if (($("#name").val() == "Name") || ($("#name").val() == "")){
                errorCount += 1;                
	        	errors += "<span>Oops. Name field is empty</span>";
            }  
        }        
		if (errors != ""){
        	errMsg = errors;
			$("#registerErrorMsg").html(errMsg);	
            switch (errorCount) {
                case '1': $("#registerErrorMsg").css({"height" : "10px"}); break;
                case '2': $("#registerErrorMsg").css({"height" : "20px"}); break;
                case '3': $("#registerErrorMsg").css({"height" : "30px"}); break;
                case '4': $("#registerErrorMsg").css({"height" : "40px"}); break;
                case '5': $("#registerErrorMsg").css({"height" : "50px"}); break;
                case '6': $("#registerErrorMsg").css({"height" : "60px"}); break;                    
            }            
			$("#requiredForEmailImg").addClass("hide");    
            $("#requiredForNameImg").addClass("hide");
            $("#requiredForPasswordImg").addClass("hide");
            
            $("#name_status").html('<img class="notAvailable Floatmargin" src="<?php echo WEB_PATH?>/public/images/not_available.png" title="Name Required" >');                                        
			$("#availability_status").html('<img class="notAvailable Floatmargin" src="<?php echo WEB_PATH?>/public/images/not_available.png" title="Email Address Required" >');                                        
			$("#password_status2").html('<img class="notAvailable Floatmargin" src="<?php echo WEB_PATH?>/public/images/not_available.png" title="Password Required" >');                                        
            
            $("#registerErrorMsg").slideDown("slower");
            setTimeout(function(){
	            $("#registerErrorMsg").slideUp("slower");    	                                
            }, 8000);                                      
            return false;                                
        }else{
        	$("#newUser").submit();
        }
    });	
	$("#curemail").keypress(function(event){
  		if (event.which == 13) {
   			$("#request_Password").trigger("click");
            return false;		
        }
    });
    $("#request_Password").click(function(){
    	var errors = "",
        	errorCount = 0;
        if (($("#curemail").val() == '') || ($("#curemail").val() == 'Your email address')){
        	errors += "Please enter an email address.";
            $("#curemail").val('Your email address');
            $("#forgotPassError").html(errors);	                
            $("#forgotPassError").slideDown("slower");
			$("#recover_password_status").html('');		
            setTimeout(function(){
                $("#forgotPassError").slideUp("slower");    	                                
            }, 3000);                                      
            return false;
        }else{
            if (emailReg.test($("#curemail").val())){       
                $.ajax({  
                    type: "POST",
                    url: "<?php echo WEB_PATH?>/library/ajaxFunctions.php?action=chkCurEmail", 
                    data: "curemail=" + $("#curemail").val(),
                    success: function(data){
                        if (data == '1'){
				            $("#curemail").val('Your email address');
                            $("#recover_password_status").html('');
                            $("#forgotPassSuccess").html("Please check your email: Password reset link has been emailed to you!");	                
                            $("#forgotPassSuccess").slideDown("slower");
                            $("#recover_password_status").html('');		
                            setTimeout(function(){
                                $("#forgotPassSuccess").slideUp("slower");    	                                
                                $("#login_recover_mask").trigger("click");
                            }, 3000);                                      
                        }else{
				            $("#curemail").val('Your email address ');
                            $("#recover_password_status").html('');
                            errors += "Oops. We couldn't find your email address in our database.";
                            $("#forgotPassError").html(errors);	                
                            $("#forgotPassError").slideDown("slower");
                            setTimeout(function(){
                                $("#forgotPassError").slideUp("slower");    	                                
                            }, 3000);                                      
                            return false;
                        }
                    }
                });
             }else{
                  $("#curemail").val('Your email address');
                  $("#recover_password_status").html('');
                  errors += "Oops. This is not a valid email address!";
                  $("#forgotPassError").html(errors);	                
                  $("#forgotPassError").slideDown("slower");
                  setTimeout(function(){
                      $("#forgotPassError").slideUp("slower");    	                                
                  }, 3000);                                      
                  return false;
             }           
        }
    });
    
    $("#recover_Password").click(function(){
    	var errors = "";
        if (
        	(($("#curpassword").val() == '') || ($("#curpassword").val() == 'Current Password')) &&
            (($("#newpassword").val() == '') || ($("#newpassword").val() == 'New Password')) &&
            (($("#newpasswordConfirm").val() == '') || ($("#newpasswordConfirm").val() == 'Confirm Password'))            
           ){
           		errors += "Please enter your current password, new password and confirm your new password.";
           }else{
           		if (($("#curpassword").val() == '') || ($("#curpassword").val() == 'Current Password')){
                	errors += "Oops. Current password field is empty.";	
                }else if (($("#newpassword").val() == '') || ($("#newpassword").val() == 'New Password')){
                	errors += "Oops. New password field is empty.";	
                }else if (($("#newpasswordConfirm").val() == '') || ($("#newpasswordConfirm").val() == 'Confirm Password')){
                	errors += "Oops. Password confirmation field is empty.";	
                }else if ($("#newpassword").val() != $("#newpasswordConfirm").val()){
                	errors += "Oops. Your passwords don't match. Please check again.";	                    
                }else if (($("#newpassword").val() .length < 6) && ($("#newpasswordConfirm").val() .length < 6)){
                	errors += "Your password must have at least 6 characters.";	                	
                }else{
                    $.ajax({  
                        type: "POST",
                        url: "<?php echo WEB_PATH?>/library/ajaxFunctions.php?action=chkCurPass", 
                        data: "curpass=" + $("#curpassword").val(),
                        success: function(data){
                            if (data == 'not ok'){
                                errors += "Oops. Your current password is incorrect. Please enter again.";
                			}
                        }
                    });        	
                }
           }
               
          if (errors != ""){
              $("#forgotPassError").html(errors);	                
              $("#forgotPassError").slideDown("slower");
              setTimeout(function(){
              	  $("#forgotPassError").html("");	                	
                  $("#forgotPassError").slideUp("slower");    	                                
              }, 3000);                                      
              return false;
          }else{
               $.ajax({  
                  type: "POST",
                  url: "<?php echo WEB_PATH?>/library/ajaxFunctions.php?action=newpass", 
                  data: "newpassval=" + $("#newpassword").val(),
                  success: function(data){
                      if (data == 'ok'){
                         $("#forgotPassSuccess").html("<div class='clearH10'></div>Your password is updated!");	                
                          setTimeout(function(){
                              $("#curpassword").addClass("hide");
                              $("#curpassword_text").removeClass("hide");
                              $("#newpassword").addClass("hide");                          
                              $("#newpassword_text").removeClass("hide");
                              $("#newpasswordConfirm").addClass("hide");
                              $("#newpasswordConfirm_text").removeClass("hide");
                      	  	  $("#forgotPassSuccess").html("");	     
                          }, 3000);                                                  
                      }
                  }
               });
           }
      });
      
    // This is for the show hidden password text in the registration box
    $("#password").addClass("hide");
    $("#password_text").focus(function(){
    	$("#password_text").addClass("hide");
    	$("#password").removeClass("hide");
        $("#password").focus();
    });
    $("#password").focusout(function(){
    	if ($("#password").val() ==""){
            $("#password_text").removeClass("hide");
            $("#password").addClass("hide");
        }
    });
    // This is for the show hidden password text in the login box
    $("#directLoginPassword").addClass("hide");
    $("#directLoginPassword_text").focus(function(){
    	$("#directLoginPassword_text").addClass("hide");
    	$("#directLoginPassword").removeClass("hide");
        $("#directLoginPassword").focus();
    });
    $("#directLoginPassword").focusout(function(){
    	if ($("#directLoginPassword").val() ==""){
            $("#directLoginPassword_text").removeClass("hide");
            $("#directLoginPassword").addClass("hide");
        }
    });
    // This is for the change password fields
    $("#curpassword").addClass("hide");
    $("#curpassword_text").focus(function(){
        $("#curpassword_text").addClass("hide");
        $("#curpassword").removeClass("hide");
        $("#curpassword").focus();
    });
    $("#curpassword").focusout(function(){
        if ($("#curpassword").val() ==""){
            $("#curpassword_text").removeClass("hide");
            $("#curpassword").addClass("hide");
        }

    });
    // New password field
    $("#newpassword").addClass("hide");
    $("#newpassword_text").focus(function(){
        $("#newpassword_text").addClass("hide");
        $("#newpassword").removeClass("hide");
        $("#newpassword").focus();
    });
    $("#newpassword").focusout(function(){
        if ($("#newpassword").val() ==""){
            $("#newpassword_text").removeClass("hide");
            $("#newpassword").addClass("hide");
        }
    });
    $("#newpasswordConfirm").addClass("hide");
    $("#newpasswordConfirm_text").focus(function(){
        $("#newpasswordConfirm_text").addClass("hide");
        $("#newpasswordConfirm").removeClass("hide");
        $("#newpasswordConfirm").focus();
    });
    $("#newpasswordConfirm").focusout(function(){
        if ($("#newpasswordConfirm").val() ==""){
            $("#newpasswordConfirm_text").removeClass("hide");
            $("#newpasswordConfirm").addClass("hide");
        }
    });
    // This is for the forget password change
    $("#new_Password").click(function(){
        var errors = "";
        if ( 
        	(($("#newpassword").val() == "") || ($("#newpassword").val() == "New Password")) &&
            (($("#newpasswordConfirm").val() == "") || ($("#newpasswordConfirm").val() == "Confirm Password"))  
           ){
           		$("#forgotPassErrorInNewPassEnter").css({'display' : 'block !important'});
        		errors = "Password and confirm password fields are empty";
                $("#forgotPassErrorInNewPassEnter").html(errors + '<div class="clearH10"></div>');	                
                $("#forgotPassErrorInNewPassEnter").slideDown("slower");
                setTimeout(function(){
                    $("#forgotPassErrorInNewPassEnter").slideUp("slower");    	                                
                    $("#newpassword").addClass("hide");
                    $("#newpassword_text").removeClass("hide");
                    $("#newpasswordConfirm").addClass("hide");
                    $("#newpasswordConfirm_text").removeClass("hide");
           			$("#forgotPassErrorInNewPassEnter").css({'display' : 'none !important'});                    
                }, 3000);                                      
                return false;
        }else{
        	if ($("#newpassword").val().length < 6){
           		$("#forgotPassErrorInNewPassEnter").css({'display' : 'block !important'});
	        	errors += "<span>Oops. Your password has to be at least 6 characters long.</span><br />";
                $("#forgotPassErrorInNewPassEnter").html(errors + '<div class="clearH10"></div>');	                
                $("#forgotPassErrorInNewPassEnter").slideDown("slower");
                setTimeout(function(){
                    $("#forgotPassErrorInNewPassEnter").slideUp("slower");    	                                
                    $("#newpassword").addClass("hide");
                    $("#newpassword_text").removeClass("hide");
                    $("#forgotPassErrorInNewPassEnter").html('');	
           			$("#forgotPassErrorInNewPassEnter").css({'display' : 'none !important'});                    
                }, 3000);                                      
                return false;
            }else if ($("#newpasswordConfirm").val().length < 6){
	        	errors += "<span>Please enter a password with more than 6 characters.</span><br />";
                $("#forgotPassErrorInNewPassEnter").html(errors + '<div class="clearH10"></div>');	                
                $("#forgotPassErrorInNewPassEnter").slideDown("slower");
                setTimeout(function(){
                    $("#forgotPassErrorInNewPassEnter").slideUp("slower");    	                                
                    $("#newpasswordConfirm").addClass("hide");
                    $("#newpasswordConfirm_text").removeClass("hide");
                    $("#forgotPassErrorInNewPassEnter").html('');	
           			$("#forgotPassErrorInNewPassEnter").css({'display' : 'none !important'});                    
                }, 3000);                                      
                return false;
            }else if ($("#newpasswordConfirm").val() != $("#newpassword").val()){
	        	errors += "<span>Oops. Your passwords don't match.</span><br />";
                $("#forgotPassErrorInNewPassEnter").html(errors + '<div class="clearH10"></div>');	                
                $("#forgotPassErrorInNewPassEnter").slideDown("slower");
                setTimeout(function(){
                    $("#forgotPassErrorInNewPassEnter").slideUp("slower");    	                                
                    $("#newpasswordConfirm").addClass("hide");
                    $("#newpasswordConfirm_text").removeClass("hide");
                    $("#forgotPassErrorInNewPassEnter").html('');	
           			$("#forgotPassErrorInNewPassEnter").css({'display' : 'none !important'});                    
                }, 3000);                                      
                return false;
            }else{
                $.ajax({  
                    type: "POST",
                    url: "<?php echo WEB_PATH?>/library/ajaxFunctions.php?action=newpassforpassreset", 
                    data: "forgotpassword=" + $("#newpassword").val(), 
                    success: function(server_response){				
                        if (server_response == '1'){
		                    $("#forgotPassErrorInNewPassEnter").html("You're password is updated successfully!");	                        
                            setTimeout(function(){
                                $("#newpassword").addClass("hide");
                                $("#newpassword_text").removeClass("hide");
                                $("#newpasswordConfirm").addClass("hide");
                                $("#newpasswordConfirm_text").removeClass("hide");
		                    	$("#forgotPassErrorInNewPassEnter").html("");	                        
                                location.href = "<?php echo WEB_PATH?>";
                            }, 3000);
                        }
                    }
                });
            }    
        }        
    });
    // This is for the Jquery login recovery box
    $('#ForgotPassword').click(function(e) {
		var id = "",
        	maskHeight = "",
            maskWidth = "",
            winH = "",
            winW  = "";
    
    	$("#login_boxes").css({"display" : "none"});
        $("#login_recover_dialog").css({"display" : "block"});
        //Cancel the link behavior
        e.preventDefault();
     
        //Get the screen height and width
        maskHeight = $(document).height();
        maskWidth = $(window).width();
     
        //Set height and width to mask to fill up the whole screen
        $('#login_recover_mask').css({'width':maskWidth,'height':maskHeight});
         
        //transition effect    
        $('#login_recover_mask').fadeIn(1000);   
        $('#login_recover_mask').fadeTo("slow",0.8); 
     
        //Get the window height and width
        winH = $(window).height();
        winW = $(window).width();
        
        //Set the popup window to center
        $('#login_recover_dialog').css('top',  winH/2-$('#login_recover_dialog').height()/2);
        $('#login_recover_dialog').css('left', winW/2-$('#login_recover_dialog').width()/2);
     
        //transition effect
        $('#login_recover_dialog').fadeIn(2000);
     
    });
    //if close button is clicked
    $('.login_recover_window .close').click(function (e) {
        //Cancel the link behavior
        e.preventDefault();
        $('#login_recover_mask, .login_recover_window').hide();
    });    
    //if mask is clicked
    $('#login_recover_mask').click(function () {
        $(this).hide();
        $('.login_recover_window').hide();
    });
      
});