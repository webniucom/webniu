layui.define(["jquery","layer"], function (exports) {
	var MOD_NAME = 'newTheme',
	    $ = layui.jquery;

	var newTheme = {};
	newTheme.autoHead = false;

	newTheme.changeTheme = function (target, autoHead) {
		this.autoHead = autoHead;
		var color 	= localStorage.getItem("theme-color-color");
		//判断window.GICAI 是否是一个方法
		if (typeof window.dashboard == "function") {
			window.dashboard();
		}
		 
		this.colorSet(color);
		if (target.frames.length == 0) return;
		for (var i = 0; i < target.frames.length; i++) {
			try {
				if(target.frames[i].layui == undefined) continue;
				target.frames[i].layui.newTheme.changeTheme(target.frames[i], autoHead);
			}
			catch (error) {
			}
		}
	}
	newTheme.colorSet = function(color) {
		 
		var uniqueId = "pear-admin-color-script";
		var existingScript = $("script[data-unique-id='" + uniqueId + "']").first();
	
		if (existingScript.length > 0) {
			existingScript.remove(); // 移除旧脚本
		} else {
		}
	
		// 创建并插入新脚本
		var newScript = document.createElement('script');
		newScript.type = 'text/javascript';
		newScript.dataset.uniqueId = uniqueId;
		newScript.innerHTML = this.generateScriptContent(color);
		$("head")[0].appendChild(newScript);
	};
	
	newTheme.generateScriptContent = function(color) {
		if(color!=null){
			return "document.documentElement.style.setProperty('--global-primary-color', '" + color + "');";
		}
		return false;
	};
	

	exports(MOD_NAME, newTheme);
});