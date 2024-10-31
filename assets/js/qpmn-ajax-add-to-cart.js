(function ($) {
    $(document).ready(function ($) {
        $(document.body).on('adding_to_cart', function (event, button, data) {
            // button所在的form元素
            var form = button.parents('form.cart');
            // 获取form表单下所有input元素
            var fields = form.find('input');
            // data为对象类型
            if (data && isObject(data)) {
                fields.each(function (index, field) {
                    var fieldName = $(field).attr('name')
                    if (fieldName && !data[fieldName]) {
                        data[fieldName] = $(field).val();
                    }
                })
            }
            // data为对象数组类型
            else if ($.isArray(data)) {
                fields.each(function (index, field) {
                    if (data.find(function (item) {
                        return item.name == $(field).attr('name');
                    }) == undefined) {
                        data.push({
                            name: $(field).attr('name'),
                            value: $(field).val()
                        })
                    }
                })
                // 过滤掉data中name为空的item
                for (var i = data.length - 1; i > 0; i--) {
                    if (!data[i]['name']) {
                        data.splice(i, 1);
                    }
                }
            }
        });
        function isObject(target) {
            return Object.prototype.toString.call(target) == '[object Object]';
        }
    });
})(jQuery);