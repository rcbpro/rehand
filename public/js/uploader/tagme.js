(function($){
	var tagBox = null,
			tagpanel = null,
			mouseOver = false;
			
			
	var methods = {
		closeTagBox : function( ) { 
		if(tagBox != null)
		{
			$(tagBox).remove();
			tagBox = null;
		}
		if(tagpanel != null)
		{
			$(tagpanel).remove();
			tagpanel = null;
			}
		}
	};
	
	
	
	$.fn.TagMe = function(options){
		
		if ( methods[options] ) {
			return methods[ options ].apply( this, Array.prototype.slice.call( arguments, 1 )); 
		}
		
		
		var self = this,tagList = Array(),
			currentTagId = 0,
			defaults = {
				loadTags:false,
				id:null,
				loadTagsAction:{
							url:"test.php",
							onProgress:function(data){},
							onSuccess:function(result){},
							onFail:function(result){},
							json:true		
				},
                tagbox : {
							element: '<div class="tm-tagbox"></div>',
							width:50,
							height:50,
							placeHolderElement:'<div class="tm-tagbox"></div>'
				},
				
				tagpanelElement: '<div class="tm-tagpanel">'+
										 '<form id="tm-tagpanel-form">'+
											 '<input name="itemName" value="Item name">'+
											 '<textarea name="itemDescription">Item Description</textarea>'+									 
											 '<input name="itemPrice" value="Price">'+
											 '<div class="clearH5"></div><input type="checkbox" class="freeCheckBox" />Free'+
											 '<button type="button" class="cancel" >Cancel</button>'+
											 '<button type="submit" >Done</button>'+
										 '</form>'+
									 '</div>',

				tagEditpanelElement: '<div class="tm-tagpanel">'+
										 '<form id="tm-tagpanel-form">'+
											 '<input name="itemName" value="Item name">'+
											 '<textarea name="itemDescription">Item Description</textarea>'+									 
											 '<input name="itemPrice" value="Price">'+
											 '<div class="clearH5"></div><select name="itemStatus" class="DDBg">'+
											 '	<option value="available">Available</option>'+
											 '	<option value="free">Free</option>'+											 
											 '	<option value="sold">Sold</option>'+
											 '</select>'+
											 '<button type="button" class="cancel" >Cancel</button>'+
											 '<button type="submit" >Done</button>'+
										 '</form>'+
									 '</div>',

				tagpanelAction:{
										url:"test.php",
										submitFormId:'tm-tagpanel-form',
										onProgress:function(data){},
										onSuccess:function(result){},
										onFail:function(result){},
										json:true		
							},
				tagpanelEditAction:{
										url:"test.php",
										submitFormId:'tm-tagpanel-form',
										onProgress:function(ele){},
										onSuccess:function(res,ele){},
										onFail:function(ele){},
										json:true		
							},
				tagpanelDeleteAction:{
										url:"test.php",
										submitFormId:'tm-tagpanel-form',
										onProgress:function(ele){},
										onSuccess:function(res,ele){},
										onFail:function(ele){},
										json:true		
							},
				tagpanelOnAfterInitPanel:	function(element){},
				validateForm:				function(element){},				
				onTagBoxOver : 				function(event,element,data){},
				onTagBoxClick : 			function(event,element,data){},
				onTagBoxOut : 				function(event,element,data){},
				onTagAdded : 				function(tagElement){},
				
				
            },
		_options =  $.extend(defaults, options),$this = this;
		tagBox = null;
		tagpanel = null;
		mouseOver = false;		
		if(_options.loadTags)
		{
			$.post(
				_options.loadTagsAction.url,
				{action:"loadTag",imageId: _options.id },
				function(res){
					doneLoad(res);
				},
				'json'
			);
		}
				
		//functions
		function doneLoad(respond){
			if(respond.error == null)
			{
				$.each(respond.tags,function(ind,tagData){
					addTag(tagData);
					_options.loadTagsAction.onSuccess();
				});
			}
		}
		function done(respond){
			if(respond.error == null)
			{
				addTag(respond.obj);
				if(_options.tagpanelAction.json)
					_options.tagpanelAction.onSuccess(respond.error);
				else
					_options.tagpanelAction.onSuccess(respond.error+"");				
			}
			else
			{
				if(_options.tagpanelAction.json)
					_options.tagpanelAction.onFail(respond.error);
				else
					_options.tagpanelAction.onFail(respond.error+"");
			}
		}
		function doneEdit(respond,element){
			
			if(respond.error == null)
			{
				updateTag(respond.obj);
				if(_options.tagpanelEditAction.json)
					_options.tagpanelEditAction.onSuccess(respond,element);
				else
					_options.tagpanelEditAction.onSuccess(respond+"");
			}
			else
			{	
				if(_options.tagpanelEditAction.json)
					_options.tagpanelEditAction.onFail(respond,element);
				else
					_options.tagpanelEditAction.onFail(respond+"");
			}			
		}
		function doneDelete(respond,element){
			if(respond.error == null)
			{
				deleteTag(respond.obj);
				element.remove();
				if(_options.tagpanelDeleteAction.json)
					_options.tagpanelDeleteAction.onSuccess(respond,element);
				else
					_options.tagpanelDeleteAction.onSuccess(respond+"");
			}
			else
			{	
				if(_options.tagpanelDeleteAction.json)
					_options.tagpanelDeleteAction.onFail(respond,element);
				else
					_options.tagpanelDeleteAction.onFail(respond+"");
			}
			
			cancelTag();
		}
		function deleteTag(obj){
			delete tagList[obj.tagId];
			$("#"+obj.tagId).remove();
		}
		function updateTag(obj){
			tagList[obj.tagId] = {"tagData":obj,"tagPosition":{"x":parseInt(obj.tagX),"y":parseInt(obj.tagY),"width":parseInt(_options.tagbox.width),"height":parseInt(_options.tagbox.height)}};
			//console.log(obj);
		}
		function addTag(obj){
			
			var tmpTb = $(_options.tagbox.placeHolderElement);
			tmpTb.width(_options.tagbox.width);
			tmpTb.height(_options.tagbox.height);
			tmpTb.css('top',parseInt(obj.tagY));
			tmpTb.css('left',parseInt(obj.tagX));
			tmpTb.attr('id',obj.tagId),
			temEle=null;
			
			//####Bind Events#####
			tmpTb.click(function(e){
				e.stopPropagation();
				
				initEditTagPanel($(this),tagList[$(this).attr("id")]);
			});
			tmpTb.mouseover(function(e){
				_options.onTagBoxOver(e,$(this),tagList[$(this).attr("id")]);
				//initEditTagPanel($(this),tagList[$(this).attr("id")]);
			});
			tmpTb.mouseout(function(e){
				_options.onTagBoxOut(e,$(this),tagList[$(this).attr("id")]);
				//console.log(this);
				//setTimeout(disposeTagPanel,50);
				
			});
			//-------------------
			tagList[obj.tagId] = {"tagData":obj,"tagPosition":{"x":parseInt(obj.tagX),"y":parseInt(obj.tagY),"width":parseInt(_options.tagbox.width),"height":parseInt(_options.tagbox.height)}};
			tmpTb.appendTo($this);
			_options.onTagAdded(tmpTb,obj);
		}
		
		function cancelTag()
		{
			if(tagBox != null)
			{
			   $(tagBox).remove();
			   tagBox = null;
			}
			if(tagpanel != null)
			{
			   $(tagpanel).remove();
			   tagpanel = null;
			}
		}
		
		//########tagging process
		this.click(function(e) {
			//console.log(tagBox);
			//return;
			if(tagBox == null && tagpanel == null)
			{
				var parentOffset = $(this).offset(),
					box = _options.tagbox,
					relX = e.pageX - parentOffset.left - (box.width/2),
					relY = e.pageY - parentOffset.top - (box.height/2),
					diff;
				if(relX > ($(this).find("img").width()-box.width))
				{ 
					diff = box.width - ($(this).find("img").width()-relX);
					//console.log("relx "+relX+"  xlimit "+$(this).find("img").width() +"  diff= "+diff);
					relX -= diff;
				}else if(relX < 0){
					diff = Math.abs(0 - relX);
					relX += diff;
				}
				
				if(relY > ($(this).find("img").height()-box.height))
				{ 
					diff = box.height - ($(this).find("img").height()-relY);
					//console.log("relx "+relX+"  xlimit "+$(this).find("img").width() +"  diff= "+diff);
					relY -= diff;
				}else if(relY < 0){
					diff = Math.abs(0 - relY);
					relY += diff;
				}
				//--------//
				
				tagBox = $(box.element);
				tagBox.css('top',relY);
				tagBox.css('left',relX);
				tagBox.css('width',box.width);
				tagBox.css('height',box.height);
				tagpanel = $(_options.tagpanelElement);
				//append hidden elements containning coordinates to the form			
				temEle = $('<input type="hidden" name="tagX" value="'+parseInt(relX)+'" />');
				temEle.appendTo(tagpanel.find("#"+_options.tagpanelAction.submitFormId));
				temEle = $('<input type="hidden" name="tagY" value="'+parseInt(relY)+'" />');
				temEle.appendTo(tagpanel.find("#"+_options.tagpanelAction.submitFormId));
				temEle = $('<input type="hidden" name="tagW" value="'+parseInt(box.width)+'" />');
				temEle.appendTo(tagpanel.find("#"+_options.tagpanelAction.submitFormId));
				temEle = $('<input type="hidden" name="tagH" value="'+parseInt(box.height)+'" />');
				temEle.appendTo(tagpanel.find("#"+_options.tagpanelAction.submitFormId));
				temEle = $('<input type="hidden" name="imageId" value="'+_options.id+'" />');
				temEle.appendTo(tagpanel.find("#"+_options.tagpanelAction.submitFormId));
				temEle = $('<input type="hidden" name="tagId" value="'+currentTagId+'" />');
				temEle.appendTo(tagpanel.find("#"+_options.tagpanelAction.submitFormId));
				temEle = $('<input type="hidden" name="action" value="newTag" />');
				temEle.appendTo(tagpanel.find("#"+_options.tagpanelAction.submitFormId));
				temEle = null;
				//--------------------------------------------------------//
				
				
				tagpanel.css('top',relY);
				tagpanel.css('left',relX+box.width+5);
				tagpanel.appendTo($(this));		
				tagBox.appendTo($(this));
				
				//####Events#####
				
				//stop tagpanel click propegation
				tagpanel.each(function(){
					$(this).click(function(e) {
						e.stopPropagation(); 
					});
				});
				//-----------------------------//
				//tagpanel event bind//
				tagpanel.find("#"+_options.tagpanelAction.submitFormId).submit(function(e) {
					e.preventDefault();
					if(_options.validateForm($(this))){
						
						$.post(
							_options.tagpanelAction.url,
							$(this).serialize(),
							function(res){
								res.panel = $(this).parent();
								done(res);
							},
							'json'
						);
						currentTagId++;
						tagpanel.remove();
						tagBox.remove();
						tagpanel = null;
						tagBox = null;				
					}else
						false;
				});
				tagpanel.mouseover(function(){
					mouseOver = true;							
				});
				tagpanel.mouseout(function(){
					mouseOver = false;							
				});
				tagpanel.find(".Tagpanelclose").click(function(e)
				{
					e.stopPropagation();
					cancelTag();
				});
				//------------------//
				_options.tagpanelOnAfterInitPanel(tagpanel);
			}
		});
		//########end of tagging process
		
		function initEditTagPanel(element,data){
			
			if(tagBox == null && tagpanel == null)
			{
			   var relX = data.tagPosition.x,
				relY = data.tagPosition.y,
				box = _options.tagbox,deleteButton;
				
				tagpanel = $(_options.tagEditpanelElement);
				//occupy data
				tagpanel.find("input[name=itemName]").val(data.tagData.itemName);
				tagpanel.find("input[name=itemPrice]").val(data.tagData.itemPrice);
				tagpanel.find("textarea[name=itemDescription]").val(data.tagData.itemDescription);
				tagpanel.find("select[name=itemStatus]").val(data.tagData.itemStatus);
				//stop tagpanel click propegation
				tagpanel.each(function(){
					$(this).click(function(e) {
						e.stopPropagation(); 
					});
				});
				//-----------------------------//
				
				//append delete button
				deleteButton = $('<input type="button"  id="deleteTag" value="Delete" />');
				deleteButton.appendTo(tagpanel.find("#"+_options.tagpanelAction.submitFormId));
				
				//append hidden elements containning coordinates to the form		
				temEle = $('<input type="hidden" name="tagX" value="'+parseInt(relX)+'" />');
				temEle.appendTo(tagpanel.find("#"+_options.tagpanelAction.submitFormId));
				temEle = $('<input type="hidden" name="tagY" value="'+parseInt(relY)+'" />');
				temEle.appendTo(tagpanel.find("#"+_options.tagpanelAction.submitFormId));
				temEle = $('<input type="hidden" name="tagW" value="'+parseInt(box.width)+'" />');
				temEle.appendTo(tagpanel.find("#"+_options.tagpanelAction.submitFormId));
				temEle = $('<input type="hidden" name="tagH" value="'+parseInt(box.height)+'" />');
				temEle.appendTo(tagpanel.find("#"+_options.tagpanelAction.submitFormId));
				temEle = $('<input type="hidden" name="imageId" value="'+_options.id+'" />');
				temEle.appendTo(tagpanel.find("#"+_options.tagpanelAction.submitFormId));
				temEle = $('<input type="hidden" name="tagId" value="'+element.attr("id")+'" />');
				temEle.appendTo(tagpanel.find("#"+_options.tagpanelAction.submitFormId));
				temEle = $('<input type="hidden" name="action" value="editTag" />');
				temEle.appendTo(tagpanel.find("#"+_options.tagpanelAction.submitFormId));
				temEle = null;
				//--------------------------------------------------------//
				
				//tagpanel event bind//
				tagpanel.find("#"+_options.tagpanelEditAction.submitFormId).submit(function(e) {
					e.preventDefault();
					tagpanel.find("input[name=action]").val("editTag");
					if(_options.validateForm($(this))){
						$.post(
							_options.tagpanelEditAction.url,
							$(this).serialize(),
							function(res){
								doneEdit(res,tagpanel);
							},
							'json'
						);									
					}else
						false;
				});
				deleteButton.click(function(e){
					e.preventDefault();
					tagpanel.find("input[name=action]").val("deleteTag");
					$.post(
						_options.tagpanelDeleteAction.url,
						tagpanel.find("#"+_options.tagpanelDeleteAction.submitFormId).serialize(),
						function(res){
							doneDelete(res,tagpanel);
						},
						'json'
					);
				});
				
				tagpanel.mouseover(function(){
					mouseOver = true;							
				});
				tagpanel.mouseout(function(){
					mouseOver = false;							
				});
				tagpanel.css('top',relY);
				tagpanel.css('left',relX+box.width+5);
				tagpanel.appendTo(element.parent());
				
				//------------------//
				tagpanel.find(".Tagpanelclose").click(function(e)
				{
					e.stopPropagation();
					cancelTag();
				});
				
				_options.tagpanelOnAfterInitPanel(tagpanel);
			}
		}
			
		return self;
		
	}
	function disposeTagPanel()
		{
			
			
			if(mouseOver)
			{
				setTimeout(disposeTagPanel,10);
			}
			else
			{
				if(tagpanel != null)
			   		$(tagpanel).remove();
			}
		}
})( jQuery );