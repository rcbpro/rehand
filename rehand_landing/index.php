<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Rehand&trade; - Social Online Classifieds for Second Hand Items</title>
<meta name="keywords" content="Rehand, Social Online Classifieds, 2nd hand items, Used items for sale"/>
<meta name="description" content="Social Online Classifieds for 2nd hand items"/>
<meta name="twitter:site" value="@rehandapp">
<link href='http://fonts.googleapis.com/css?family=Source+Sans+Pro:400,400italic,600,700' rel='stylesheet' type='text/css'>
<link rel="stylesheet" type="text/css" href="../public/css/styles.css" />
<link rel="stylesheet" href="../public/css/styles-720.css" type="text/css" media="screen and (min-width: 720px)">
<link rel="stylesheet" href="../public/css/styles-986.css" type="text/css" media="screen and (min-width: 986px)">
<link rel="stylesheet" href="../public/css/styles-1236.css" media="screen and (min-width: 1236px)" >
<link rel="shortcut icon" href="../public/images/rehand-fav.png" />
<link rel="apple-touch-icon-precomposed" media="screen and (resolution: 163dpi)" href="../public/images/rehand-57.png" />
<link rel="apple-touch-icon-precomposed" media="screen and (resolution: 132dpi)" href="../public/images/rehand-72.png" />
<link rel="apple-touch-icon-precomposed" media="screen and (resolution: 326dpi)" href="../public/images/rehand-114.png" />
<script src="js/jquery-1.7.2.min.js" type="text/javascript"></script>
<script src="js/jquery-ui.min.js" type="text/javascript"></script>
<script src="js/jquery.path.js" type="text/javascript"></script>
<script src="js/jquery.backgroundpos.min.js" type="text/javascript"></script>
<script src="js/scroll-startstop.events.jquery.js" type="text/javascript"></script>
<script language="javascript" type="text/javascript">
var wordPoint = -1;

var currentPopIndex = { "pAni": 1 , "aAni" : 1};

var cycleCount = 0;

var scrollUpdateInterval = null;
var scrolledAmount = 0;

var spaceBuffer = 200;

$(document).ready(function(e) {
    
    resetAllPops( $("#photographyAni") );
	
	resetAllPops( $("#antiquesAni") );
	
  	
	
	//setTimeout(removeWord,3000);
	changeWords( );
	setTimeout( function()
			{
				
				popUpBaloons( $("#photographyAni") , 400, getTop( $("#photographyAniWrapMain"), 455) , getBottom( $("#photographyAniWrapMain"), 350 ) ,"pAni" );
				
				popUpBaloons( $("#antiquesAni") , 300, getTop( $("#antiquesAni"), 425) , getBottom( $("#antiquesAni"), 300 ), "aAni" );
				
				animateCircle(2500,800,getTop( $("#cycleAni"), 675) , getBottom( $("#cycleAni"), spaceBuffer ));
				
			}, 1000);
	
	
	jQuery(window).bind('scrollstart', function(){
		scrollUpdateInterval = true;
		updateScrollAmount();
		
	});
	
	jQuery(window).bind('scrollstop', function(e){
		scrollUpdateInterval = false;
	});
	
});

function updateScrollAmount()
{	
	scrolledAmount = $(window).scrollTop();
	
	$("#scrollAmount").html(scrolledAmount);
	
	if(scrollUpdateInterval)
	{
		setTimeout( function()
					{
						updateScrollAmount( );
						
					}, 50);
	}
}



var wordList = ["Discovering","Buying","Selling","Sharing"];

var iphoneSteps = {
		"Discovering":{
				text : "Discover",
				marginLeft : 194,
				marginTop : 244			
			},
			
		"Buying":{
				text : "Buy",
				marginLeft : 248,
				marginTop : 280			
			},
		"Selling":{
				text : "Sell",
				marginLeft : 474,
				marginTop : 234			
			},
		
		"Sharing":{
				text : "Share",
				marginLeft : 554,
				marginTop : 364			
			},
		
		
	}



function changeWords()
{
	if(scrolledAmount < 200)
	{
		$('#text').animate({
			opacity: 0
		 }, 200, function()
				 {
					 
					 
					 if(wordPoint == (wordList.length-1))
						wordPoint = 0;
					else
						wordPoint++;
						
					
					addWord( $('#text') ,wordList[wordPoint]);
					
					setupIphone(wordList[wordPoint],800);
					
					$('#text').animate({
						opacity: 1
					 }, 300, function()
							 {
								 
								 setTimeout(changeWords,1300);
							 }
					 );
				 }
		 
		 );
	}
	else
	{
		setTimeout(changeWords,1300);
	}
	
}

function removeWord(element)
{
	var text = $(element).html();
	
	if( text.length > 0)
	{
		var cutOff = text.length - 1 ;
		var newText = text.substring(0, cutOff)
		
		
		$(element).html(newText);
		
		setTimeout( function()
					{
						removeWord(element)
						
					}, 50);
		return false;
	}
	else
	{	
		return true;
	}
	
}

function addWord(element,word)
{
	var text = $(element).html();
	text = text.replace(/&nbsp;/g, " ");
	
	var newTextLength = word.length;
	
	if(word != text)
	{
		if( text.length < newTextLength)
		{
			var cutOff = text.length + 1 ;
			var newText = word.substring(0, cutOff)
			
			
			$(element).html(newText);
			
			setTimeout( function()
						{
							addWord(element,word)
							
						}, 20);
			return false;
		}
		else if ( text.length > newTextLength)
		{
			
			var cutOff = text.length - 1 ;
			var padLen = cutOff - newTextLength;
			var pad = "";
			
			for(var i=0; i < padLen; i++)
			{
				pad += '&nbsp;';
			}
			
			var newText = word+pad;
			
			$(element).html(newText);
			
			setTimeout( function()
						{
							addWord(element,word)
							
						}, 50);
			return false;
			
		}
		else
		{
			var newText = word;
			
			$(element).html(newText);
			
			return true;
		}
	}
	else
	{
		return true;
	}
}

function setupIphone(step,duration)
{
	
	var left_fix = 100;
	var top_fix = 95;
	
	var newMarginLeft = iphoneSteps[step]['marginLeft']- left_fix+"px";
	var newMarginTop = iphoneSteps[step]['marginTop'] - top_fix +"px";
	var newText = iphoneSteps[step]['text'];
	
	var offsetX = parseInt( $("#iPhoneBgMask").css('left') ) ;
	var offsetY = parseInt( $("#iPhoneBgMask").css('top') ) ;
	
	
	var newNegMarginLeft = "-"+(iphoneSteps[step]['marginLeft']+offsetX+1- left_fix)+"px";
	var newNegMarginTop = "-"+(iphoneSteps[step]['marginTop']+offsetY+1- top_fix)+"px";
	
	$("#iPhoneObj").animate({left: newMarginLeft,top:newMarginTop, },duration,'linear');
	$("#iPhoneBgMask").animate({backgroundPosition: newNegMarginLeft+" "+newNegMarginTop},duration,'linear');
	$("#iphoneText").html(newText);
		
}


function popUpBaloons( parentEle, duration, startPoint, endPoint, var_index )
{
	
	
	
	if(scrolledAmount > startPoint && scrolledAmount < endPoint )
	{
		
		var currentIndex = currentPopIndex[var_index];
		
		
		
		var popList = $(parentEle).children('.pop');
		var popListLength = popList.length;
		
		
		popUpImage( popList[currentIndex-1] , 100);
		
		if(currentIndex < popListLength)
		{
			currentIndex++;
			setTimeout( function()
			{
				popUpBaloons( parentEle, duration, startPoint, endPoint, var_index );
				
			}, duration );
			
		}
		currentPopIndex[var_index] = currentIndex;
		
	}
	else
	{
		setTimeout( function()
			{
				popUpBaloons( parentEle, duration, startPoint, endPoint, var_index );
				
			}, duration );
	}
	
}

function resetAllPops( parentEle )
{
	$(parentEle).find('.pop').hide();
}

function popUpImage(ele,duration)
{
	$(ele).show("scale", {}, duration);
}

function resetPopUpImage(ele,duration)
{
	$(ele).hide();
}


var cycleList = [

	{
		center: [250,295],  
		radius: 183,    
		start: 180,
		end: 63.5,
		dir: -1
	},
	
	{
		center: [250,295],  
		radius: 183,    
		start: 63.5,
		end: 298,
		dir: -1
	},
	
	
	{
		center: [250,295],  
		radius: 183,    
		start: 298,
		end: 180,
		dir: -1
	}

];



var circle_pos_1 = {
	center: [250,295],  
	radius: 183,    
	start: 180,
	end: 63.5,
	dir: -1
}

var circle_pos_2 = {
	center: [250,295],  
	radius: 183,    
	start: 63.5,
	end: 298,
	dir: -1
}

var circle_pos_3 = {
	center: [250,295],  
	radius: 183,    
	start: 298,
	end: 180,
	dir: -1
}




function animateCircle(duration,delay,startPoint, endPoint)
{
	if(scrolledAmount > startPoint && scrolledAmount < endPoint )
	{
		if( cycleCount >= 3 )
			cycleCount = 0;
		
		var c1 = cycleCount;
		var c2 = (cycleCount+1)%3 ;
		var c3 = (cycleCount+2)%3 ;
		
		
		$("#book_set_1").animate({path : new $.path.arc(cycleList[c1])},duration);	
		$("#book_set_2").animate({path : new $.path.arc(cycleList[c2])},duration);	
		$("#book_set_3").animate({path : new $.path.arc(cycleList[c3])},duration);
		
		
		cycleCount++;
		
		setTimeout( function()
		{
			animateCircle(duration,delay,startPoint, endPoint);
			
		},  duration+delay );
	}
	else
	{
		setTimeout( function()
			{
				animateCircle(duration,delay,startPoint, endPoint);
				
			},  duration+delay );
	}
}

function getTop(element,padding)
{
	if( isNaN( parseInt(padding) ) )
		padding = 0;
	
	var ret = $(element).offset().top - padding;
	
	return ret;
	
}

function getBottom( element, padding)
{
	if( isNaN( parseInt(padding) ) )
		padding = 0;
	
	var ret = $(element).offset().top + $(element).height() + padding ;
	
	return ret;
}
</script>
<script src="js/authenticate.js.php" type="text/javascript"></script>
</head>
<body>

<div id="TopNavMain">
  <a href="index.php" class="floatl" title="Rehand" id="Logo"></a>
  <a href="javascript:void(0);" name="registration_modal" id="RegisterBut"></a><a href="javascript:void(0);" name="login_modal" id="directLogin"></a>
</div>
<div class="clearshadow"></div>

<div id="scrollAmount"></div>

<div class="LandingWrap">
	
    <div id="Story1" class="StoryLine"></div>
    <div id="textHolder" class="LandingText">Rehand is a social online classifieds for <span id="text" style="font-weight:600;">Buying</span> the things you love...</div>

	<div id="iphoneAni" class="aniContainer">
        <div id="iPhoneObj">
            <div id="iPhoneBgMask"></div>
            <div id="iphoneText">Buy</div>
        </div>
    </div>
    
    <div id="Story2" class="StoryLine"></div>
    <div class="LandingText StoryText2">Discover the things you love from groups and people just like you.</div>
    
    <div class="clear"></div>
	<div id="photographyAniWrapMain">
        <div id="photographyAniWrap">
            <div id="photographyAni" class="aniContainer" >
                <img src="../public/images/landing/rehand_items/1.png" id="pop_1" class="pop" />
                <img src="../public/images/landing/rehand_items/1-1.png" id="pop_2"  class="pop" />
                
                <img src="../public/images/landing/rehand_items/2.png" id="pop_3"  class="pop"/>
                <img src="../public/images/landing/rehand_items/2-2.png" id="pop_4"   class="pop"/>
                
                
                <img src="../public/images/landing/rehand_items/3-3.png" id="pop_5"  class="pop"/>
                <img src="../public/images/landing/rehand_items/3.png" id="pop_6"  class="pop"/>
                
                <img src="../public/images/landing/rehand_items/4-4.png" id="pop_7"  class="pop"/>
                <img src="../public/images/landing/rehand_items/4.png" id="pop_8"  class="pop"/>
                
                <img src="../public/images/landing/rehand_items/5-5.png" id="pop_9"  class="pop"/>
                <img src="../public/images/landing/rehand_items/5.png" id="pop_10"  class="pop"/>
            </div>
        </div>
    </div>
    <div class="clear"></div>
    <div id="Story3" class="StoryLine"></div>
    <div class="LandingText StoryText3">Create your own groups and communities to connect with<br />even more like minded people.</div>

	<div id="antiquesAni" class="aniContainer"  >
    	<img src="../public/images/landing/rehand_items/heart.png" id="aa_pop_1" class="pop" />
    </div>
    
    <div id="Story4" class="StoryLine"></div>
    <div class="LandingText StoryText4">Rehand is taking better care for our environemnt.</div>

	<div id="cycleAni" class="aniContainer" >
    	<img src="../public/images/landing/rehand_items/books1.png" id="book_set_1" class="cycle" />
        <img src="../public/images/landing/rehand_items/books2.png" id="book_set_2" class="cycle" />
        <img src="../public/images/landing/rehand_items/books3.png" id="book_set_3" class="cycle" />
    </div>

</div>
<?php include 'htmlContent.html';?>
</body>
</html>