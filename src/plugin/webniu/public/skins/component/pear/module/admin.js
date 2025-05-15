layui.define(['jquery', 'tools', 'element', 'yaml', 'form', 'tabPage','newTheme', 'menu', 'page', 'fullscreen', 'messageCenter', 'menuSearch'],
	function (exports) {
		"use strict";

		var $ = layui.jquery,
			form = layui.form,
			yaml = layui.yaml,
			page = layui.page,
			menu = layui.menu,
			tabPage = layui.tabPage,
			newTheme = layui.newTheme,
			messageCenter = layui.messageCenter,
			menuSearch = layui.menuSearch,
			fullscreen = layui.fullscreen,
			tools = layui.tools;

		var configurationCache;

		var logout = function () { };
		var cacheclear 	= function() {};
		window.layui.diyfuner = function (obj) { // 自定义函数
		}
		var body = $('body');

		var pearAdmin = new function () {

			this.configuration = {};

			this.configurationPath = "pear.config.json";

			this.instances = {};

			/**
			 * @since Pear Admin 4.0
			 * 
			 * 获取 pear.config 实现 [ default ] 
			 */
			this.configurationProvider = () => {
				return new Promise((resolve) => {
					if (this.configurationPath.indexOf(".yml") === -1) {
						$.ajax({
							type: 'GET',
							url: this.configurationPath,
							dataType: 'json',
							async: false,
							success: (result) => {
								// 定义默认对象
								let defaultObject = {
									"logo": {
										"title": "WebNiu 网牛",
										"image": "/app/webniu/skins/admin/images/logo.png"
									},
									"menu": {
										"data": "/app/webniu/web/rule/get",
										"method": "GET",// GET POST
										"accordion": true,// 手风琴
										"collapse": false,// 折叠
										"control": "double",// 控制
										"controlWidth": "auto",// 控制宽度
										"async": true// 异步
									},
									"tab": {
										"enable": true,// 开启
										"keepState": true,// 保持状态
										"session": true,// 会话
										"preload": false,// 预加载
										"max": "30",// 最大
										"index": {
											"id": "3",
											"href": "/app/webniu/web/index/dashboard",
											"title": "控制台"
										}
									},
									"theme": {
										"defaultColor": "1", // 默认颜色
										"defaultMenu": "dark-theme", // 默认菜单
										"defaultHeader": "light-theme", // 默认头部
										"allowCustom": true, // 允许自定义
										"banner": false, // 通栏
										"footer": false, // 底部
										"keepLoad": "800", // 保持加载
										"autoHead": false, // 自动头部
									},
									"colors": [
										{
											"id": "1",
											"color": "#2d8cf0",
											"second": "#ecf5ff"
										},
										{
											"id": "2",
											"color": "#16baaa",
											"second": "#16baaa"
										},
										{
											"id": "3",
											"color": "#f6ad55",
											"second": "#fdf6ec"
										},
										{
											"id": "4",
											"color": "#f56c6c",
											"second": "#fef0f0"
										},
										{
											"id": "5",
											"color": "#3963bc",
											"second": "#ecf5ff"
										}
										,
										{
											"id": "6",
											"color": "#000000",
											"second": "#000000"
										}
									],
									
									"header": {
										"message": "/app/webniu/web/index/message"
									}
								};
			
								// 合并默认对象和返回的结果，确保所有字段都有值
								let finalConfig 		= {  ...defaultObject,...result.views }; 
									finalConfig.logo 	= result.base; 
								resolve(finalConfig);
							},
							error: (xhr, status, error) => {
								console.error("配置加载失败:", error);
								// 在请求失败时，仍然返回默认配置
								resolve(defaultObject);
							}
						});
					} else {
						// 处理 YAML 文件的情况
						try {
							const yamlData = yaml.load(this.configurationPath);
							resolve(yamlData);
						} catch (e) {
							console.error("YAML 解析失败:", e);
							// 如果 YAML 解析失败，返回默认配置或处理错误
							resolve(defaultObject); // 或者根据需求处理错误
						}
					}
				});
			};
			

			/**
			 * @since Pear Admin 4.0 
			 * 
			 * 配置 pear.config 路径
			 */
			this.setConfigurationPath = (path) => {
				this.configurationPath = path;
			}

			/**
			 * @since Pear Admin 4.0
			 * 
			 * 获取 pear.config 实现 [ implement ] 
			 */
			this.setConfigurationProvider = (provider) => {
				this.configurationProvider = provider;
			}

			/**
			 * @since Pear Admin 4.0
			 * 
			 * 获取 pear.config 配置
			 */
			this.getConfiguration = () => {
				return this.configuration;
			}

			/**
			 * @since Pear Admin 4.0
			 * 
			 * Core Function.
			 * 
			 * @param {*} options 
			 */
			this.render = (options) => {
				if (options !== undefined) {
					pearAdmin.apply(options);
				} else {
					this.configurationProvider().then((result) => {
						pearAdmin.apply(result);
					})
				}
			}

			/**
			 * @since Pear Admin 4.0 
			 * 
			 * 启动构建
			 */
			this.apply = function (configuration) {
				configurationCache = configuration;
				pearAdmin.logoRender(configuration);
				pearAdmin.menuRender(configuration);
				pearAdmin.menuSearchRender(configuration);
				pearAdmin.bodyRender(configuration);
				pearAdmin.messageCenterRender(configuration);
				pearAdmin.themeRender(configuration);
				pearAdmin.keepLoad(configuration);
				window.PearAdmin = pearAdmin;
			}

			/**
			 * @since Pear Admin 4.0
			 * 
			 * 菜单搜索
			 */
			this.menuSearchRender = function (options) {
				menuSearch.render({
					elem: ".menuSearch",
					dataProvider: () => pearAdmin.instances.menu.cache(),
					select: (node) => {
						if (node.type == "1") {
							pearAdmin.instances.menu.selectItem(node.id);
							if (node.opentype === "_layer") {
								layer.open({
									type: 2,
									title: data.title,
									content: data.url,
									area: ['80%', '80%'],
									maxmin: true
								})
							} else {
								if (isMuiltTab(options) === "true" ||
									isMuiltTab(options) === true) {
									pearAdmin.instances.tabPage.changePage({
										id: node.id,
										title: node.title,
										type: node.opentype,
										url: node.url,
										close: true
									});
								} else {
									pearAdmin.instances.page.changePage({
										href: node.url,
										type: node.opentype
									});
								}
							}
						}
					}
				})
			}

			/**
			 * @since Pear Admin 4.0
			 * 
			 * 消息中心
			 */
			this.messageCenterRender = function (options) {
				messageCenter.render({
					elem: '.message',
					url: options.header.message,
					height: '250px'
				});
			}

			this.logoRender = function (param) {
				$(".layui-logo .logo").attr("src", param.logo.image);
				$(".layui-logo .title").html(param.logo.title);
				$("title").html(param.logo.title);
				$(".layui-footer .center").html(param.logo.copyright);
			}

			/**
			 * @since Pear Admin 4.0
			 * 
			 * 侧边菜单
			 */
			this.menuRender = function (param) {
				pearAdmin.instances.menu = menu.render({
					elem: 'side',
					async: param.menu.async,
					method: param.menu.method,
					control: isControl(param),
					controlWidth: param.menu.controlWidth,
					accordion: param.menu.accordion,
					data: param.menu.data,
					url: param.menu.data,
					parseData: false,
					defaultMenu: 0,
					change: function () {
						compatible();
					},
					done: function () {
						pearAdmin.instances.menu.isCollapse = param.menu.collapse;
						pearAdmin.instances.menu.selectItem(param.tab.index.id);
						if (param.menu.collapse) {
							if ($(window).width() >= 768) {
								collapse()
							}
						}
					}
				});
			}

			/**
			 * @since Pear Admin 4.0
			 * 
			 * 视图容器
			 */
			this.bodyRender = function (param) {

				body.on("click", ".refresh", function () {
					pearAdmin.refresh();
				})

				if (isMuiltTab(param) === "true" || isMuiltTab(param) === true) {

					pearAdmin.instances.tabPage = tabPage.render({
						elem: 'content',
						session: param.tab.session,
						index: 0,
						tabMax: param.tab.max,
						preload: param.tab.preload,
						closeEvent: function (id) {
							pearAdmin.instances.menu.selectItem(id);
						},
						data: [{
							id: param.tab.index.id,
							url: param.tab.index.href,
							title: param.tab.index.title,
							close: false
						}],
						success: function (id) {
							if (param.tab.session) {
								setTimeout(function () {
									pearAdmin.instances.menu.selectItem(id);
									pearAdmin.instances.tabPage.positionTab();
								}, 500)
							}
						}
					});

					pearAdmin.instances.tabPage.click(function (id) {
						if (!param.tab.keepState) {
							pearAdmin.instances.tabPage.refresh(false);
						}
						pearAdmin.instances.tabPage.positionTab();
						pearAdmin.instances.menu.selectItem(id); 
						window.layui.diyfuner(id);
					})

					pearAdmin.instances.menu.click(function (dom, data) {
						if (data.menuopentype === "_layer") {
							layer.open({ type: 2, title: data.menuTitle, content: data.menuUrl, area: ['80%', '80%'], maxmin: true })
						} else {
							pearAdmin.instances.tabPage.changePage({
								id: data.menuId,
								title: data.menuTitle,
								type: data.menuopentype,
								url: data.menuUrl,
								close: true
							});
						}
						compatible();
					})

				} else {

					pearAdmin.instances.page = page.render({
						elem: 'content',
						title: '首页',
						url: param.tab.index.href
					});

					pearAdmin.instances.menu.click(function (dom, data) {
						if (data.menuopentype === "_layer") {
							layer.open({ type: 2, title: data.menuTitle, content: data.menuUrl, area: ['80%', '80%'], maxmin: true })
						} else {
							pearAdmin.instances.page.changePage({ href: data.menuUrl, type: data.menuopentype });
						}
						compatible()
					})
				}
			}

			this.keepLoad = function (param) {
				compatible()
				setTimeout(function () {
					$(".loader-wrapper").fadeOut(200);
				}, param.theme.keepLoad)
			}

			/***
			 * @since Pear Admin 4.0
			 *
			 * 切换主题色
			 */
			this.changeTheme = function () {
				let AutoHead 		= localStorage.getItem("auto-head");
				newTheme.changeTheme(window,AutoHead);
			}

			/**
			 * @since Pear Admin 4.0
			 * 
			 * 主题配置
			 */
			this.themeRender = function (option) {
				if (option.theme.allowCustom === false) {
					$(".setting").remove();
				}
				var colorId 	= localStorage.getItem("theme-color");
				var currentColor= getColorById(colorId);
				localStorage.setItem("theme-color", currentColor.id);
				localStorage.setItem("theme-color-color", currentColor.color);
				localStorage.setItem("theme-color-second", currentColor.second);
				pearAdmin.changeTheme();
				 
				var menu = localStorage.getItem("theme-menu");
				if (menu === null) {
					menu = option.theme.defaultMenu;
				} else {
					if (option.theme.allowCustom === false) {
						menu = option.theme.defaultMenu;
					}
				}

				var header = localStorage.getItem("theme-header");
				if (header === null) {
					header = option.theme.defaultHeader;
				} else {
					if (option.theme.allowCustom === false) {
						header = option.theme.defaultHeader;
					}
				}

				var banner = localStorage.getItem("theme-banner");
				if (banner === null) {
					banner = option.theme.banner;
				} else {
					if (option.theme.allowCustom === false) {
						banner = option.theme.banner;
					}
				}

				var autoHead = localStorage.getItem("auto-head");
				if (autoHead === null) {
					autoHead = option.theme.autoHead;
				} else {
					if (option.theme.allowCustom === false) {
						autoHead = option.theme.autoHead;
					}
				}

				var muiltTab = localStorage.getItem("muilt-tab");
				if (muiltTab === null) {
					muiltTab = option.tab.enable;
				} else {
					if (option.theme.allowCustom === false) {
						muiltTab = option.tab.enable;
					}
				}

				var control = localStorage.getItem("theme-control");
				if (control === null) {
					control = option.menu.control;
				} else {
					if (option.theme.allowCustom === false) {
						control = option.menu.control;
					}
				}

				var footer = localStorage.getItem("footer");
				if (footer === null) {
					footer = option.theme.footer;
				} else {
					if (option.theme.allowCustom === false) {
						footer = option.theme.footer;
					}
				}

				var dark = localStorage.getItem("dark");
				if (dark === null) {
					dark = option.theme.dark;
				} else {
					if (option.theme.allowCustom === false) {
						dark = option.theme.dark;
					}
				}

				localStorage.setItem("muilt-tab", muiltTab);
				localStorage.setItem("theme-banner", banner);
				localStorage.setItem("theme-menu", menu);
				localStorage.setItem("footer", footer);
				localStorage.setItem("theme-control", control);
				localStorage.setItem("theme-header", header);
				localStorage.setItem("auto-head", autoHead);
				localStorage.setItem("dark", dark);
				this.menuSkin(menu);
				this.headerSkin(header);
				this.bannerSkin(banner);
				this.switchTheme(dark);
				this.footer(footer);
			}

			this.footer = function (footer) { 
				var bodyDOM = $(".pear-admin .layui-body");
				var footerDOM = $(".pear-admin .layui-footer");
				if (footer === true || footer === "true") {
					footerDOM.removeClass("close");
					bodyDOM.css("height", "calc(100% - 105px)");
				} else {
					footerDOM.addClass("close");
					bodyDOM.css("height", "calc(100% - 60px)");
				}
			}

			this.bannerSkin = function (theme) {
				var pearAdmin = $(".pear-admin");
				pearAdmin.removeClass("banner-layout");
				if (theme === true || theme === "true") {
					pearAdmin.addClass("banner-layout");
				}
			}

			this.switchTheme = function (checked) {
				var $pearAdmin = $(".pear-admin");
				$pearAdmin.removeClass("pear-admin-dark");
				if (checked === true || checked === "true") {
					$pearAdmin.addClass("pear-admin-dark");
				}
			}

			this.menuSkin = function (theme) {
				var pearAdmin = $(".pear-admin .layui-side,.pear-admin .layui-double");
				pearAdmin.removeClass("light-theme");
				pearAdmin.removeClass("dark-theme");
				pearAdmin.addClass(theme);
			}

			this.headerSkin = function (theme) {
				var pearAdmin = $(".pear-admin .layui-header");
				pearAdmin.removeClass("dark-theme");
				pearAdmin.removeClass("light-theme");
				pearAdmin.removeClass("auto-theme");
				pearAdmin.addClass(theme);
			}

			/**
			 * 设置注销逻辑
			 * 
			 * @param callback 实现
			 */
			this.logout = function (callback) {
				if (callback != undefined) {
					logout = callback;
				}
			}
			/**
			 * 设置刷新缓存
			 * 
			 * @param callback 实现
			 */
			this.cacheclear = function(callback) {
				if (callback != undefined) {
					cacheclear = callback;
				} 
			}

			/**
			 * @since Pear Admin 4.0.3
			 * 
			 * 刷新当前页面
			 */
			this.refresh = function () {
				var refreshBtn = $(".refresh a");
				refreshBtn.addClass("layui-anim layui-anim-rotate layui-anim-loop layui-icon-loading");
				refreshBtn.removeClass("layui-icon-refresh-1");
				if (isMuiltTab(configurationCache) === "true" || isMuiltTab(configurationCache) === true) pearAdmin.instances.tabPage.refresh(true);
				else pearAdmin.instances.page.refresh(true);
				setTimeout(function () {
					refreshBtn.removeClass("layui-anim layui-anim-rotate layui-anim-loop layui-icon-loading");
					refreshBtn.addClass("layui-icon-refresh-1");
				}, 600)
			}

			/**
			 * @since Pear Admin 4.0.3 
			 * 
			 * 切换内容页面
			 * 
			 * PS: tabPages 模式下，如果页面不存在则新增，反则仅做切换。
			 */
			this.changePage = function (data) {
				if (isMuiltTab(configurationCache) === "true" || isMuiltTab(configurationCache) === true) {
					pearAdmin.instances.tabPage.changePage({ id: data.id, title: data.title, url: data.url, type: data.type, close: true });
				} else {
					pearAdmin.instances.page.changePage({ href: data.url, type: data.type });
				}
			}

		};

		/**
		 * @since Pear Admin 4.0
		 * 
		 * 菜单折叠
		 */
		function collapse(width = false) {
			pearAdmin.instances.menu.collapse();
			let control = pearAdmin.instances.menu.option.control;
			var admin = $(".pear-admin");
			var left = $(".layui-icon-spread-left")
			var right = $(".layui-icon-shrink-right") 
			if (admin.is(".pear-mini")) { 
				if(control=="double"){
					$(".layui-double").removeClass("layui-hide");
					$(".pear-admin").addClass('pear-double');
				}
				left.addClass("layui-icon-shrink-right")
				left.removeClass("layui-icon-spread-left")
				admin.removeClass("pear-mini");
				pearAdmin.instances.menu.isCollapse = false;
			} else { 
				if(control=="double"){
					$(".layui-double").addClass("layui-hide");
					if(width){
						setTimeout(function () {
							$(".pear-admin").removeClass('pear-double');
						},200);
					}else{
						$(".pear-admin").removeClass('pear-double');
					}
				}
				right.addClass("layui-icon-spread-left")
				right.removeClass("layui-icon-shrink-right")
				admin.addClass("pear-mini");
				pearAdmin.instances.menu.isCollapse = true;
			}
		}

		/**
		 * @since Pear Admin 4.0
		 * 
		 * 使用 admin.logout(Function) 实现注销 
		 * 
		 * Promise<boolean> 作为返回值类型时，泛型内容为 true 时视为注销成功，则清除 pearAdmin.instances.tabPage 缓存
		 * 
		 * 否则视为注销失败，不做任何处置。
		 */
		body.on("click", ".logout", function () {
			var promise = logout();
			if (promise != undefined) {
				promise.then((asyncResult) => {
					if (asyncResult) {
						if (pearAdmin.instances.tabPage != undefined) {
							pearAdmin.instances.tabPage.clear();
						}
					}
				})
			} else {
				if (pearAdmin.instances.tabPage != undefined) {
					pearAdmin.instances.tabPage.clear();
				}
			}
		})

		body.on("click", ".cacheclear", function() {
			var promise = cacheclear();
			if (promise != undefined) {
				promise.then((asyncResult) => {
					if (asyncResult) {
						if (pearAdmin.instances.tabPage != undefined) {
							pearAdmin.instances.tabPage.clear();
						}
					}
				})
			} else {
				if (pearAdmin.instances.tabPage != undefined) {
					pearAdmin.instances.tabPage.clear();
				}
			}
		})

		body.on("click", ".collapse,.pear-cover", function () {
			collapse();
		});

		body.on("click", ".fullScreen", function () {
			if ($(this).hasClass("layui-icon-screen-restore")) {
				fullscreen.fullClose().then(function () {
					$(".fullScreen").eq(0).removeClass("layui-icon-screen-restore");
				});
			} else {
				fullscreen.fullScreen().then(function () {
					$(".fullScreen").eq(0).addClass("layui-icon-screen-restore");
				});
			}
		});

		body.on("click", '[user-menu-id]', function () {
			if (isMuiltTab(configurationCache) === "true" || isMuiltTab(configurationCache) === true) {
				pearAdmin.instances.tabPage.changePage({
					id: $(this).attr("user-menu-id"),
					title: $(this).attr("user-menu-title"),
					url: $(this).attr("user-menu-url"),
					close: true
				}, 300);
			} else {
				pearAdmin.instances.page.changePage({
					href: $(this).attr("user-menu-url"),
					type: "_component"
				}, true);
			}
		});

		body.on("click", ".setting", function () {

			var menuItem =
				'<li class="layui-this" data-select-bgcolor="dark-theme" >' +
				'<a href="javascript:;" data-skin="skin-blue" style="" class="clearfix full-opacity-hover">' +
				'<div><span style="display:block; width: 20%; float: left; height: 12px; background: #28333E;"></span><span style="display:block; width: 80%; float: left; height: 12px; background: white;"></span></div>' +
				'<div><span style="display:block; width: 20%; float: left; height: 40px; background: #28333E;"></span><span style="display:block; width: 80%; float: left; height: 40px; background: #f4f5f7;"></span></div>' +
				'</a>' +
				'</li>';

			menuItem +=
				'<li  data-select-bgcolor="light-theme" >' +
				'<a href="javascript:;" data-skin="skin-blue" style="" class="clearfix full-opacity-hover">' +
				'<div><span style="display:block; width: 20%; float: left; height: 12px; background: white;"></span><span style="display:block; width: 80%; float: left; height: 12px; background: white;"></span></div>' +
				'<div><span style="display:block; width: 20%; float: left; height: 40px; background: white;"></span><span style="display:block; width: 80%; float: left; height: 40px; background: #f4f5f7;"></span></div>' +
				'</a>' +
				'</li>';

			var menuHtml =
				'<div class="pearone-color">\n' +
				'<div class="color-title">菜单风格</div>\n' +
				'<div class="color-content">\n' +
				'<ul>\n' + menuItem + '</ul>\n' +
				'</div>\n' +
				'</div>';

			var headItem =
				'<li class="layui-this" data-select-header="light-theme" >' +
				'<a href="javascript:;" data-skin="skin-blue" style="" class="clearfix full-opacity-hover">' +
				'<div><span style="display:block; width: 20%; float: left; height: 12px; background: #28333E;"></span><span style="display:block; width: 80%; float: left; height: 12px; background: white;"></span></div>' +
				'<div><span style="display:block; width: 20%; float: left; height: 40px; background: #28333E;"></span><span style="display:block; width: 80%; float: left; height: 40px; background: #f4f5f7;"></span></div>' +
				'</a>' +
				'</li>';

			headItem +=
				'<li  data-select-header="dark-theme" >' +
				'<a href="javascript:;" data-skin="skin-blue" style="" class="clearfix full-opacity-hover">' +
				'<div><span style="display:block; width: 20%; float: left; height: 12px; background: #28333E;"></span><span style="display:block; width: 80%; float: left; height: 12px; background: #28333E;"></span></div>' +
				'<div><span style="display:block; width: 20%; float: left; height: 40px; background: #28333E;"></span><span style="display:block; width: 80%; float: left; height: 40px; background: #f4f5f7;"></span></div>' +
				'</a>' +
				'</li>';

			headItem +=
				'<li  data-select-header="auto-theme" >' +
				'<a href="javascript:;" data-skin="skin-blue" style="" class="clearfix full-opacity-hover">' +
				'<div><span style="display:block; width: 20%; float: left; height: 12px; background: #28333E;"></span><span style="display:block; width: 80%; float: left; height: 12px; background: var(--global-primary-color);" ></span></div>' +
				'<div><span style="display:block; width: 20%; float: left; height: 40px; background: #28333E;"></span><span style="display:block; width: 80%; float: left; height: 40px; background: #f4f5f7;"></span></div>' +
				'</a>' +
				'</li>';

			var headHtml =
				'<div class="pearone-color">\n' +
				'<div class="color-title">顶栏风格</div>\n' +
				'<div class="color-content">\n' +
				'<ul>\n' + headItem + '</ul>\n' +
				'</div>\n' +
				'</div>';
				var controlItem =
				'<li class="layui-this" data-select-control="side" title="单侧菜单">' +
				'<a href="javascript:;" data-skin="skin-blue" style="" class="clearfix full-opacity-hover">' +
				'<div><span style="display:block; width: 20%; float: left; height: 12px; background: #28333E;"></span><span style="display:block; width: 80%; float: left; height: 12px; background: white;"></span></div>' +
				'<div><span style="display:block; width: 20%; float: left; height: 40px; background: #28333E;"></span><span style="display:block; width: 80%; float: left; height: 40px; background: #f4f5f7;"></span></div>' +
				'</a>' +
				'</li>';

			controlItem +=
				'<li  data-select-control="double" title="双侧菜单">' +
				'<a href="javascript:;" data-skin="skin-blue" style="" class="clearfix full-opacity-hover">' +
				'<div><span style="display:block; width: 5%; float: left; height: 12px; background:#28333E;"></span><span style="display:block; width: 15%; float: left; height: 12px; background:#e2e2e2;"></span><span style="display:block; width: 80%; float: left; height: 12px; background: white;"></span></div>' +
				'<div><span style="display:block; width: 5%; float: left; height: 40px; background:#28333E;"></span><span style="display:block; width: 15%; float: left; height: 40px; background:#e2e2e2;"></span><span style="display:block; width: 80%; float: left; height: 40px; background: #f4f5f7;"></span></div>' +
				'</a>' +
				'</li>';

			controlItem +=
				'<li  data-select-control="control" title="顶侧菜单">' +
				'<a href="javascript:;" data-skin="skin-blue" style="" class="clearfix full-opacity-hover">' +
				'<div><span style="display:block; width: 20%; float: left; height: 12px; background: #e2e2e2;"></span><span style="display:block; width: 80%; float: left; height: 12px; background: #e2e2e2;"></span></div>' +
				'<div><span style="display:block; width: 20%; float: left; height: 40px; background: white;"></span><span style="display:block; width: 80%; float: left; height: 40px; background: #f4f5f7;"></span></div>' +
				'</a>' +
				'</li>';

			var controlHtml =
				'<div class="pearone-color">\n' +
				'<div class="color-title">菜单类型</div>\n' +
				'<div class="color-content">\n' +
				'<ul>\n' + controlItem + '</ul>\n' +
				'</div>\n' +
				'</div>';

			var moreItem =
				'<div class="layui-form-item"><div class="layui-input-inline" style="width:200px;"><input type="checkbox" name="muilt-tab" lay-filter="muilt-tab" lay-skin="switch"></div><span class="set-text">多选项卡</span></div>';

			moreItem +=
				'<div class="layui-form-item"><div class="layui-input-inline" style="width:200px;"><input type="checkbox" name="banner" lay-filter="banner" lay-skin="switch"></div><span class="set-text">通栏布局</span></div>';

			moreItem +=
				'<div class="layui-form-item"><div class="layui-input-inline" style="width:200px;"><input type="checkbox" name="footer" lay-filter="footer" lay-skin="switch"></div><span class="set-text">开启页脚</span></div>';

			//moreItem +=
				'<div class="layui-form-item"><div class="layui-input-inline" style="width:200px;"><input type="checkbox" name="dark" lay-filter="dark" lay-skin="switch"></div><span class="set-text">夜间模式</span></div>';

			var moreHtml = '<br><div class="pearone-color">\n' +
				'<div class="color-title">更多设置</div>\n' +
				'<div class="color-content">\n' +
				'<form class="layui-form">\n' + moreItem + '</form>\n' +
				'</div>\n' +
				'</div>';

			layer.open({
				type: 1,
				offset: 'r',
				area: ['320px', '100%'],
				title: false,
				shade: 0.1,
				closeBtn: 0,
				shadeClose: false,
				anim: -1,
				skin: 'layer-anim-right',
				move: false,
				content: menuHtml + headHtml + controlHtml + buildColorHtml() + moreHtml,
				success: function (layero, index) {

					form.render();

					var color 	= localStorage.getItem("theme-color");
					var menu 	= localStorage.getItem("theme-menu");
					var header 	= localStorage.getItem("theme-header");
					var control	= localStorage.getItem("theme-control");

					if (color !== "null") {
						$(".select-color-item").removeClass("layui-icon").removeClass("layui-icon-ok");
						$("*[color-id='" + color + "']").addClass("layui-icon").addClass("layui-icon-ok");
					}

					if (menu !== "null") {
						$("*[data-select-bgcolor]").removeClass("layui-this");
						$("[data-select-bgcolor='" + menu + "']").addClass("layui-this");
					}

					if (header !== "null") {
						$("*[data-select-header]").removeClass("layui-this");
						$("[data-select-header='" + header + "']").addClass("layui-this");
					}

					if (control !== "null") {
						$("*[data-select-control]").removeClass("layui-this");
						$("[data-select-control='" + control + "']").addClass("layui-this");
					}

					 

					$('#layui-layer-shade' + index).click(function () {
						var $layero = $('#layui-layer' + index);
						$layero.animate({
							left: $layero.offset().left + $layero.width()
						}, 200, function () {
							layer.close(index);
						});
					})

					// form.on('switch(control)', function (data) {
					// 	localStorage.setItem("control", this.checked);
					// 	window.location.reload();
					// })

					form.on('switch(muilt-tab)', function (data) {
						localStorage.setItem("muilt-tab", this.checked);
						window.location.reload();
					})

					form.on('switch(auto-head)', function (data) {
						localStorage.setItem("auto-head", this.checked);
						pearAdmin.changeTheme();
					})

					form.on('switch(banner)', function (data) {
						localStorage.setItem("theme-banner", this.checked);
						pearAdmin.bannerSkin(this.checked);
					})

					form.on('switch(footer)', function (data) {
						localStorage.setItem("footer", this.checked);
						pearAdmin.footer(this.checked);
					})

					form.on('switch(dark)', function (data) {
						localStorage.setItem("dark", this.checked);
						pearAdmin.switchTheme(this.checked);
					})

					if (localStorage.getItem('theme-banner') === 'true') {
						$('input[name="banner"]').attr('checked', 'checked')
					} else {
						$('input[name="banner"]').removeAttr('checked')
					}

					// if (localStorage.getItem('control') === 'true') {
					// 	$('input[name="control"]').attr('checked', 'checked')
					// } else {
					// 	$('input[name="control"]').removeAttr('checked')
					// }

					if (localStorage.getItem('muilt-tab') === 'true') {
						$('input[name="muilt-tab"]').attr('checked', 'checked')
					} else {
						$('input[name="muilt-tab"]').removeAttr('checked')
					}

					if (localStorage.getItem('footer') === 'true') {
						$('input[name="footer"]').attr('checked', 'checked')
					} else {
						$('input[name="footer"]').removeAttr('checked')
					}

					if (localStorage.getItem('dark') === 'true') {
						$('input[name="dark"]').attr('checked', 'checked')
					} else {
						$('input[name="dark"]').removeAttr('checked')
					}

					form.render('checkbox');
				}
			});
		});

		body.on('click', '[data-select-bgcolor]', function () {
			var theme = $(this).attr('data-select-bgcolor');
			$('[data-select-bgcolor]').removeClass("layui-this");
			$(this).addClass("layui-this");
			localStorage.setItem("theme-menu", theme);
			pearAdmin.menuSkin(theme);
		});

		body.on('click', '[data-select-header]', function () {
			var headerColor = $(this).attr('data-select-header');
			$('[data-select-header]').removeClass("layui-this");
			$(this).addClass("layui-this");
			localStorage.setItem("theme-header", headerColor);
			if (headerColor == "auto-theme") {
				localStorage.setItem("auto-head", true);
				pearAdmin.changeTheme();
			} else {
				localStorage.setItem("auto-head", false);
				pearAdmin.changeTheme();
			}
			pearAdmin.headerSkin(headerColor);
		});

		body.on('click', '[data-select-control]', function () {
			var theme = $(this).attr('data-select-control');
			$('[data-select-control]').removeClass("layui-this");
			$(this).addClass("layui-this");
			localStorage.setItem("theme-control", theme);
			window.location.reload();
		});

		body.on('click', '.select-color-item', function () {
			$(".select-color-item").removeClass("layui-icon").removeClass("layui-icon-ok");
			$(this).addClass("layui-icon").addClass("layui-icon-ok");
			var colorId = $(".select-color-item.layui-icon-ok").attr("color-id");
			var currentColor = getColorById(colorId);
			localStorage.setItem("theme-color", currentColor.id);
			localStorage.setItem("theme-color-color", currentColor.color);
			localStorage.setItem("theme-color-second", currentColor.second);
			pearAdmin.changeTheme();
		});

		function getColorById(id) {
			var color;
			var flag = false;
			$.each(configurationCache.colors, function (i, value) {
				if (value.id === id) {
					color = value;
					flag = true;
				}
			})
			if (flag === false || configurationCache.theme.allowCustom === false) {
				$.each(configurationCache.colors, function (i, value) {
					if (value.id === configurationCache.theme.defaultColor) {
						color = value;
					}
				})
			}
			return color;
		}

		function buildColorHtml() {
			var colors = "";
			$.each(configurationCache.colors, function (i, value) {
				colors += "<span class='select-color-item' color-id='" + value.id + "' style='background-color:" + value.color +
					";'></span>";
			})
			return "<div class='select-color'><div class='select-color-title'>主题颜色</div><div class='select-color-content'>" +
				colors + "</div></div>"
		}

		function compatible() {
			if ($(window).width() <= 768) {
				collapse($(window).width())
			}
		}

		function isControl(option) {
			if (option.theme.allowCustom) {
				if (localStorage.getItem("theme-control") != null) {
					return localStorage.getItem("theme-control")
				} else {
					return option.menu.control
				}
			} else {
				return option.menu.control
			}
		}

		function isMuiltTab(option) {
			if (option.theme.allowCustom) {
				if (localStorage.getItem("muilt-tab") != null) {
					return localStorage.getItem("muilt-tab")
				} else {
					return option.tab.enable
				}
			} else {
				return option.tab.enable
			}
		}

		window.onresize = function () {
			if (!fullscreen.isFullscreen()) {
				$(".fullScreen").eq(0).removeClass("layui-icon-screen-restore");
			}
		}

		$(window).on('resize', tools.debounce(function () {
			if (pearAdmin.instances.menu && !pearAdmin.instances.menu.isCollapse && $(window).width() <= 768) {
				collapse($(window).width());
			}
		}, 50));

		exports('admin', pearAdmin);
	})