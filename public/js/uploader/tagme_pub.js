(function($){
	var tagBox = null,
		tagpanel = null,
		mouseOver = false;
	$.fn.TagMe = function(options){
		
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
							element: '<div class="tm-tagbox"><span class="TagItemName"></span><span class="TagItemPrice"></span></div>',
							width:50,
							height:50,
							placeHolderElement:'<div class="tm-tagbox"></div>'
				},
				
				tagpanelElement: '<div class="tm-tagpanel">'+
										 '<form id="tm-tagpanel-form">'+
											 '<span>item Name</span>&nbsp;&nbsp;<span id="itemName"></span><br />'+
											 '<span>item Description</span>&nbsp;&nbsp;<span id="itemDescription"></span><br />'+									 
											 '<span>item Price</span>&nbsp;&nbsp;<span id="itemPrice"></span><br />'+
											 '<span>item Qty</span>&nbsp;&nbsp;<span id="itemQty" ></span><br />'+
											 '<span>item Status</span>&nbsp;&nbsp;<span id="itemStatus"></span><br />'+
											 '<input type="button" value="Buy" />'+
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
				
				tagpanelOnAfterInitPanel:	function(element){},
				validateForm:				function(element){},				
				onTagBoxOver : 				function(event,element,data){},
				onTagBoxClick : 			function(event,element,data){},
				onTagBoxOut : 				function(event,element,data){},
				onTagAdded : 				function(tagElement){},
				
				
            },
		_options =  $.extend(defaults, options),$this = this;
		
		if(_options.loadTags)
		{
			$.post(
				_options.loadTagsAction.url,
				{action:"publoadTag",imageId: _options.id },
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
				});
				_options.loadTagsAction.onSuccess(self);
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
		
		function addTag(obj){
			var tmpTb = $(_options.tagbox.element);
			//tmpTb.width(_options.tagbox.width);
			//tmpTb.height(_options.tagbox.height);
			tmpTb.css('top',parseInt(obj.tagY));
			tmpTb.css('left',parseInt(obj.tagX));
			tmpTb.attr('id',obj.tagId),
			temEle=null;
			
			//####Bind Events#####
			tmpTb.click(function(e){
				e.stopPropagation();
				//console.log($(this).attr("id"));
				initShowTagPanel($(this),tagList[$(this).attr("id")]);
				//_options.onTagBoxClick(e,$(this),tagList[$(this).attr("id")]);
				
			});
			// For the mouseover
			tmpTb.mouseover(function(e){
				_options.onTagBoxOver(e,$(this),tagList[$(this).attr("id")]);
				initShowTagPanel($(this),tagList[$(this).attr("id")]);
			});
			tmpTb.children().each(function(i,e){
				$(e).mouseover(function(){
					  mouseOver = true;
				});
				$(e).mouseout(function(){
					 mouseOver = false;
				});
			 });
			
			// For the mouseout
			tmpTb.mouseout(function(e){
				_options.onTagBoxOut(e,$(this),tagList[$(this).attr("id")]);
				setTimeout(disposeTagPanel,50);
			});
			$('.TagItemName').mouseout(function(){
				tmpTb.trigger('mouseout');
			});
			$('.TagItemName').mouseout(function(){
				tmpTb.trigger('mouseout');
			});
			//-------------------
			tagList[obj.tagId] = {"tagData":obj,"tagPosition":{"x":parseInt(obj.tagX),"y":parseInt(obj.tagY),"width":parseInt(_options.tagbox.width),"height":parseInt(_options.tagbox.height)}};
			tmpTb.appendTo($this);
			_options.onTagAdded(tmpTb,obj);
			
		}
				
		//########tagging process
		this.click(function(e) {
			//console.log(_options);
			//return;
			if(tagBox != null)
			{
			   $(tagBox).remove();
			}
			if(tagpanel != null)
			{
			   $(tagpanel).remove();
			}
			
		});
		/*########end of tagging process*/
		
		function initShowTagPanel(element,data){
			var relX = data.tagPosition.x,
				relY = data.tagPosition.y,
				box = _options.tagbox,deleteButton,
                                noOfInterests = "";
			if(tagBox != null)
			{
			   $(tagBox).remove();
			}
			if(tagpanel != null)
			{
			   $(tagpanel).remove();
			}
			tagpanel = $(_options.tagpanelElement);
			//occupy data
			tagpanel.find("#itemName").text(data.tagData.itemName);
			tagpanel.find("#itemPrice").text(data.tagData.itemPrice);
			tagpanel.find("#itemQty").text(data.tagData.itemQty);
			tagpanel.find("#itemDescription").text(data.tagData.itemDescription);
			tagpanel.find("#itemStatus").text(data.tagData.itemStatus);
			if (data.tagData.noOfInterests != null){
				noOfInterests = data.tagData.noOfInterests;
			}else{
				noOfInterests = 0;
			}
			tagpanel.find("#noOfInterests").text(noOfInterests);			
			//stop tagpanel click propegation
			tagpanel.each(function(){
				$(this).click(function(e) {
					e.stopPropagation(); 
                });
			});
			//-----------------------------//
			
			/*//append delete button
			deleteButton = $('<input type="button"  id="deleteTag" value="Delete" />');
			deleteButton.appendTo(tagpanel.find("#"+_options.tagpanelAction.submitFormId));
			*/
			//append hidden elements containning coordinates to the form		
			temEle = $('<input type="hidden" name="imageId" value="'+_options.id+'" />');
			temEle.appendTo(tagpanel.find("#"+_options.tagpanelAction.submitFormId));
			temEle = $('<input type="hidden" name="tagId" value="'+element.attr("id")+'" />');
			temEle.appendTo(tagpanel.find("#"+_options.tagpanelAction.submitFormId));
			temEle = $('<input type="hidden" name="action" value="buyTag" />');
			temEle.appendTo(tagpanel.find("#"+_options.tagpanelAction.submitFormId));
			temEle = null;
			//--------------------------------------------------------//
			
			//tagpanel event bind//
			/*tagpanel.find("#"+_options.tagpanelEditAction.submitFormId).submit(function(e) {
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
			
			*/
			tagpanel.css('top',relY);
			tagpanel.css('left',relX+box.width+5);
			tagpanel.appendTo(element.parent());
			
			tagpanel.mouseover(function(){
				mouseOver = true;							
			});
			tagpanel.mouseout(function(){
				mouseOver = false;							
			});
			//------------------//
			_options.tagpanelOnAfterInitPanel(tagpanel,data);
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