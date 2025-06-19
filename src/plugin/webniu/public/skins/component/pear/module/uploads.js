layui.define(['jquery', 'element','upload','layer'], function(exports) {
	"use strict";

	var MOD_NAME = 'uploads',
		element = layui.element;
	let upload = layui.upload;
	function renderList() {
		layui.$('.wn_list_dom').each(function() { 
			setUploadListValue(layui.$(this),false);
		});
		layer.photos({photos: '.wn_upload_dom', anim: 5});
	}
	// 初始化渲染
	renderList();
	 
	
	function setUploadListValue(object,data) { 
		let wn_upload_dom   = object.parent().parent();
		let wn_u_d_input    = wn_upload_dom.find(".wn_upload_dom input:first");
		let wn_u_d_name     = wn_u_d_input.attr("name");
		let wn_u_d_value    = wn_u_d_input.attr("value"); 
		let wn_u_d_multiple = wn_u_d_input.attr("multiple")==undefined?false:true; //boolean
		let wn_u_d_accept   = wn_u_d_input.attr("accept")??'images';
		let wn_list_dom     = wn_upload_dom.find(".wn_list_dom");
		if(wn_u_d_value!='' && wn_u_d_value!=undefined && data==false){
			wn_u_d_input.val('');
			layui.each((wn_u_d_value).split(","), function (k , imgurl) {
				setUploadListValue(object,{url:imgurl});
			});
		}
		 
		if(wn_u_d_accept=='images' && data!=false){
			let imgurl = data.url;
			if(!wn_u_d_multiple){wn_list_dom.html("");}
			wn_u_d_input.val(imgurl);
			wn_list_dom.append(`
			<li class="wx_images_li">
				<img src="${imgurl}" class="img_class" lay-on="photos" />
				<i class="layui-icon layui-icon-close" onclick="layui.$(this).parent().remove()"></i>
				<input type="hidden" name="${wn_u_d_name}${wn_u_d_multiple?'[]':''}" value="${imgurl}" />
			</li>`);
		}
		if(wn_u_d_accept=='file' && data!=false){
			let imgurl = data.url; 
			wn_u_d_input.val(imgurl);
			wn_list_dom.html(`
			<li class="wx_images_li"> 
				<div class="wn_upload_fileicon layui-icon layui-icon-file-b"></div>
				<i class="layui-icon layui-icon-close" onclick="layui.$(this).parent().remove()"></i>
				<input type="hidden" name="${wn_u_d_name}${wn_u_d_multiple?'[]':''}" value="${imgurl}" />
			</li>`);
		}
		element.render();
	}
	var uploads = {
		render: function (options) {
			return upload.render(options);
		},
		setUploadListValue: function(object,data) {
			setUploadListValue(object,data);
		},
		renderList: function() {
			renderList();
		}
	}

	exports(MOD_NAME,uploads);
});