layui.define(['jquery', 'element'],
    function (exports) {

        var $ = layui.jquery;
        var tools = new function () {

            /**
             * @since 防抖算法 
             * 
             * @param fn 要执行的方法
             * @param time 防抖时间参数
             */
            this.debounce = function (fn, time) {
                var timer = null
                return function () {
                    var arguments = arguments[0]
                    if (timer) {
                        clearTimeout(timer)
                    }
                    timer = setTimeout(function () {
                        fn(arguments)
                    }, time)
                }
            }

            // image 转 base64
            this.imageToBase64 = function (img) {
                var canvas = document.createElement("canvas");
                canvas.width = img.width;
                canvas.height = img.height;
                var ctx = canvas.getContext("2d");
                ctx.drawImage(img, 0, 0, img.width, img.height);
                var ext = img.src.substring(img.src.lastIndexOf(".") + 1).toLowerCase();
                var dataURL = canvas.toDataURL("image/" + ext);
                return dataURL;
            }

            // image 转 base64
            this.divdataval = function (elem, data) {
                // 判断是否传入data对象用于赋值
                if (typeof data === 'object' && data !== null) {
                    // 遍历data对象进行赋值
                    Object.keys(data).forEach(function(key) {
                        var value = data[key];
                        // 查找所有同名表单元素
                        var $elements = $(elem).find('[name="' + key + '"]');
                        if (!$elements.length) return; // 无对应元素则跳过
            
                        // 处理多选情况（复选框组或多选下拉框）
                        if (Array.isArray(value)) {
                            $elements.filter(':checkbox').each(function() {
                                $(this).prop('checked', value.includes($(this).val()));
                            });
                            $elements.filter('select[multiple]').val(value);
                        } else {
                            // 处理单选或单值输入
                            $elements.each(function() {
                                var $el = $(this);
                                if ($el.is(':checkbox') || $el.is(':radio')) {
                                    $el.prop('checked', $el.val() === value);
                                } else if ($el.is('select')) {
                                    $el.val(value);
                                } else {
                                    $el.val(value);
                                }
                            });
                        }
                    });
                }
                // 获取表单数据
                var formData = $(elem).find('input, select, textarea')
                    .not(':button, :submit') // 排除按钮
                    .serializeArray();
            
                // 转换为对象并返回
                var result = {};
                formData.forEach(function(item) {
                    result[item.name] = item.value;
                });
                return result;
            };

            this.flattenObject = function(obj, prefix = '', result = {}) {
                for (const key in obj) {
                    if (!obj.hasOwnProperty(key)) continue; // 添加安全检查
                    const newKey = prefix ? `${prefix}[${key}]` : key;
                    if (typeof obj[key] === 'object' && obj[key] !== null) {
                        this.flattenObject(obj[key], newKey, result); // 确保传递 result
                    } else {
                        result[newKey] = obj[key];
                    }
                }
                return result;
            };

            this.errortonodata = function (elem) {
                var  html = '<div class="io_404_cl"><img src="/app/webniu/skins/admin/images/nodata.svg" alt=""></div>';
                return $(elem).html(html);
            };
             
        };

        exports('tools', tools);
    })
