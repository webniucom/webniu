window.rootPath = (function (src) {
	src = document.currentScript
		? document.currentScript.src
		: document.scripts[document.scripts.length - 1].src;
	return src.substring(0, src.lastIndexOf("/") + 1);
})();

layui.config({
	base: rootPath + "module/",
	version: "4.0.3"
}).extend({
	admin: "admin",
	page: "page",
	tabPage: "tabPage",
	menu: "menu",
	fullscreen: "fullscreen",
	messageCenter: "messageCenter",
	menuSearch: "menuSearch",
	button: "button",
	popup: "extends/popup",
	count: "extends/count",
	toast: "extends/toast",
	nprogress: "extends/nprogress",
	echarts: "extends/echarts",
	echartsTheme: "extends/echartsTheme",
	yaml: "extends/yaml",
	tools: "tools",
	uploads: "uploads",
	xmSelect: "xm-select",
	apexcharts: "apexcharts/apexcharts",
	popover:"popover/popover",
	watermark:"watermark/watermark",
	iconPicker: "iconPicker",
	http: "http",
	context: "context",
	convert:"convert",
	cropper:"cropper",
	loading: "loading",
	card: "card",
	design: "design",
	topBar: "topBar",
	area:"area",
	tinymce:"tinymce/tinymce",
	dtree:"dtree",
	step:"step",
	notice: "notice",
	drawer: "drawer",
	newTheme: "newTheme",
	select: "select",
	encrypt: "encrypt",
}).use(['layer','newTheme'], function () {
	layui.newTheme.changeTheme(window, false);
});