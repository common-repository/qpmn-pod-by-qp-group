(function ($) {
    // "use strict";
    $(document).ready(function () {
        // 获取可配置产品和成品产品的元素
        let configurable_attribute = document.getElementById("qpson-configurable-design-button") || document.getElementById("qpson-finished-product");
        // 获取价格列表的元素
        let qpson_price_list = document.getElementById("qpson-price-list");
        // sku产品
        if (!configurable_attribute) {
            if (qpson_price_list) {
                qpson_price_list.style.float = "initial";
            }

        } else {
            // 获取产品的信息
            let configurable_frame_src = configurable_attribute?.value;
            // 获取价格的外框
            let price_sku_name = document.getElementsByClassName("price-iframe-container")[0];
            // 获取属性表单的外框
            let sku_name = document.getElementsByClassName("iframe-container")[0];
            // 当这个产品不是属性价格
            if (sku_name && !price_sku_name) {
                if (!qpson_price_list) {
                    // sku_name.style.display = "block";
                } else {
                    sku_name.style.display = "inline-block";
                    sku_name.style.width = "calc(100% - 170px)";
                    if (qpson_price_list.offsetHeight > sku_name.style.height) {
                        sku_name.style.minHeight = qpson_price_list.offsetHeight + 8 + "px";
                    }
                }
            } else {

            }
            // 判断当前产品是否是可配置产品
            if (qpmnProductData.qpmnProductType === "configurable") {
                // 获取所有操作按钮
                let operating_button = document.getElementsByClassName("single_add_to_cart_button");
                // 禁用所有操作按钮
                for (let index = 0; index < operating_button.length; index++) {
                    let element = operating_button[index];
                    element.disabled = true;
                }
                // 获取属性表单的iframe
                let iframe = document.getElementById("product-attribut-frame");
                // 获取属性表单的骨架屏
                let skeleton_iframe = document.getElementById("skeleton-detail-product-attribute");
                // 获取价格的骨架屏
                let skeleton_iframe_price = document.getElementById("skeleton-detail-product-price");

                var isChange = false;

                // 监听iframe里的状态
                window.addEventListener('message', function (e) {
                    let data = e.data;
                    setTimeout(function() {
                        //TODO 需要处理属性表单版本
                        if (data && data.event == "onloaded") {
                            // 隐藏属性表单的iframe
                            iframe.style.visibility = "hidden";
                            // 获取参数
                            let is_attribute_price = qpmnProductData.attributePrice === 'attributePrice' ? true : false;
                            // 判断产品的价格策略是不是属性价格
                            if (is_attribute_price) {
                                // 禁用所有操作按钮
                                let operating_button = document.getElementsByClassName("single_add_to_cart_button");
                                for (let index = 0; index < operating_button.length; index++) {
                                    let element = operating_button[index];
                                    element.disabled = true;
                                }
                                // 获取价格列表和固定价格元素
                                let qpsonProductPriceList = document.getElementById("qpson-product-price-list");
                                let qpsonProductPriceFix = document.getElementById("qpson-product-price-fix");
                                // 隐藏价格列表和固定价格
                                if (qpsonProductPriceList) {
                                    qpsonProductPriceList.style.display = "none";
                                }
                                if (qpsonProductPriceFix) {
                                    qpsonProductPriceFix.style.display = "none";
                                }
                            }
                            // 获取token
                            let accessToken = qpmnProductData.accessToken;
                            let tokenType = qpmnProductData.tokenType;
                            // 获取店铺产品id
                            let qpson_store_product_id = qpmnProductData.storeProductId;
                            // 获取qpson产品配置id
                            let qpson_publishProfileIds = qpmnProductData.qpson_publishProfileIds && JSON.parse(qpmnProductData.qpson_publishProfileIds)[0]
                            // 获取qpson产品配置码
                            let qpson_publishProfileCodes = qpmnProductData.qpson_publishProfileCode && JSON.parse(qpmnProductData.qpson_publishProfileCodes)[0]
                            // 获取qpson产品的属性版本
                            let qpson_attributeVersion = qpmnProductData.qpson_attributeVersion;
                            // 获取PropertyModelId
                            let urlParams = new URLSearchParams(configurable_frame_src);
                            let property_model_id = urlParams.get('propertyModelId');


                            // 配置启动属性表单所需要的参数
                            let bootstrapParams = {
                                args: {
                                    token: accessToken,
                                    tokenType: tokenType,
                                    spId: qpson_store_product_id,
                                    profileId: qpson_publishProfileIds,
                                    style: {
                                        htmlSize: "3.5"
                                    },
                                    isChoke: true
                                },
                                event: 'bootstrap',
                            }
                            // qpson_publishProfileCodes可能没有，需要后台配置
                            if (qpson_publishProfileCodes) {
                                bootstrapParams.args.profileCode = qpson_publishProfileCodes;
                            }
                            // qpson_attributeVersion可能没有，需要后台配置
                            if (qpson_attributeVersion) {
                                bootstrapParams.args.propertyVersion = qpson_attributeVersion;
                            }
                            if (property_model_id) {
                                bootstrapParams.args.pmId = property_model_id
                                bootstrapParams.args.askChange = true;
                            } else {
                                let url = this.window.location.href;
                                let urlParams = new URLSearchParams(new URL(url).search);
                                let propertyModelId = urlParams.get('propertyModelId');
                                if (propertyModelId) {
                                    bootstrapParams.args.pmId = propertyModelId;
                                    let newUrl = addOrgToUrl(configurable_frame_src, "propertyModelId", propertyModelId);
                                    configurable_attribute.value = newUrl;
                                }
                            }
                            // 启动属性表单
                            iframe.contentWindow.postMessage(bootstrapParams, '*');
                        }
                        // 属性表单启动完成
                        if (data && data.event == "postLink") {
                            // 隐藏属性表单的骨架屏
                            skeleton_iframe.style.visibility = "hidden";
                            // 设置属性表单的外框的高度
                            sku_name.style.height = data.args.htmlHeight + 1 + "px";
                            // sku_name.style.width = data.args.htmlWidth + "px"; //有滚动条
                            // 设置属性表单的iframe的高度
                            iframe.style.height = data.args.htmlHeight + 1 + "px";
                            // iframe.style.width = data.args.htmlWidth + "px"; //有滚动条
                            // 显示属性表单iframe
                            iframe.style.visibility = "initial";
                            let is_finished_product = qpmnProductData.isQpmnFinishedProduct == true ? true : false;
                            // 变体产品调用保存ProperyModelId的方法
                            if (is_finished_product) {
                                let skuProductIdInput = document.querySelector('input[name="skuProductId"]');
                                skuProductIdInput.value = '';
                                getProductKeyValueFun();
                                saveNewProperyModel();
                            }
                            // 如果是价格列表
                            if (qpson_price_list) {
                                if (qpson_price_list.offsetHeight > sku_name.style.height) {
                                    sku_name.style.minHeight = qpson_price_list.offsetHeight + 8 + "px";
                                }
                            }
                            let is_attribute_price = qpmnProductData.attributePrice === 'attributePrice' ? true : false;
                            if (is_attribute_price) {
                                // let getProductKeyValueParams = {
                                //     event: 'getProductKeyValue',
                                // }
                                debounce(getProductKeyValueFun)
                            } else {
                                if (!is_finished_product) {
                                    // 启动所有操作按钮
                                    let operating_button = document.getElementsByClassName("single_add_to_cart_button");
                                    for (let index = 0; index < operating_button.length; index++) {
                                        let element = operating_button[index];
                                        element.disabled = false;
                                    }
                                }
                            }

                        }
                        // 获取产品属性表单的属性值
                        if (data && data.event == "returnsProductKeyValue") {
                            let productKeyValue = data.args;
                            if (productKeyValue) {
                                let is_finished_product = qpmnProductData.isQpmnFinishedProduct == true ? true : false;
                                let accessToken = qpmnProductData.accessToken;
                                let tokenType = qpmnProductData.tokenType;
                                // 获取token
                                let Token = tokenType + " " + accessToken;
                                // 获取url上的店铺产品id
                                let qpson_store_product_id = qpmnProductData.storeProductId;
                                let qpson_product_id = qpmnProductData.qpsonProductId;
                                // 获取qpson接口地址
                                let qpson_server = qpmnProductData.server;
                                // 获取qpson产品的属性版本
                                let qpson_attributeVersion = qpmnProductData.qpson_attributeVersion;
                                let productMoq;
                                // // 拼接获取moq的url
                                let newProductKeyValue = productKeyValue.map(function (obj) {
                                    let value = obj.optionIds ? obj.optionIds.split(",").map(Number) : obj.value;
                                    return {
                                        'attributeId': obj.attributeId,
                                        'value': value
                                    }
                                })
                                // console.log(newProductKeyValue)
                                let moqUrl = qpson_server + `api/products/${qpson_product_id}/moq`;
                                let moqData = {
                                    "attributeValues": newProductKeyValue
                                }
                                let ProductKeyValueInput = document.querySelector('input[name="ProductKeyValue"]');
                                if (ProductKeyValueInput) {
                                    ProductKeyValueInput.value = JSON.stringify(newProductKeyValue);
                                }
                                if (qpson_attributeVersion) {
                                    moqData['versionedAttributeId'] = qpson_attributeVersion;
                                }
                                // if (is_finished_product) {
                                //     readData("POST", moqUrl, Token, moqData).then((res) => {
                                //         let result = JSON.parse(res);
                                //         productMoq = result.data;
                                //         // productMoq = 16;
                                //         let qpmnProductInputMoq = this.document.getElementById('qpmn-product-input-moq');
                                //         let input = document.getElementsByClassName('input-text')[0];
                                //         if (productMoq > 1) {
                                //             qpmnProductInputMoq.innerHTML = `The minimum order quantity is ${productMoq}.`;
                                //             qpmnProductInputMoq.style.display = 'inline';
                                //             input.min = productMoq;
                                //             input.value = productMoq;
                                //         }else{
                                //             qpmnProductInputMoq.style.display = 'none';
                                //             input.min = 1;
                                //             input.value = 1;
                                //         }
                                //     })
                                // } else {
                                    // 获取货币码
                                    let currencyCode = qpmnProductData.currencyCode;
                                    // 拼接获取属性价格的url
                                    let pricingUrl = qpson_server + `api/v2/storeProduct/${qpson_store_product_id}/pricing/strategy?currencyCode=${currencyCode}`;
                                    readData("POST", moqUrl, Token, moqData).then((res) => {
                                        let result = JSON.parse(res);
                                        productMoq = result.data;
                                        // productMoq = 16;
                                        let qpmnProductInputMoq = this.document.getElementById('qpmn-product-input-moq');
                                        let input = document.getElementsByClassName('input-text')[0];
                                        let propertyModelId = document.querySelector('input[name="propertyModelId"]');
                                        let qpmnProductPriceMoq = this.document.getElementById('qpmn-product-price-moq');
                                        if (productMoq > 1) {
                                            if (propertyModelId) {

                                                qpmnProductInputMoq.innerHTML = `The minimum order quantity is ${productMoq}.`;
                                                qpmnProductInputMoq.style.display = 'inline'
                                                input.min = productMoq;
                                                input.value = productMoq;
                                            } else {
                                                qpmnProductPriceMoq.innerHTML = `The minimum order quantity is ${productMoq}.`;
                                                qpmnProductPriceMoq.style.display = 'block';
                                            }
                                        }else{
                                            if (propertyModelId) {
                                                qpmnProductInputMoq.style.display = 'none';
                                                input.min = 1;
                                                input.value = 1;
                                            }else{
                                                qpmnProductPriceMoq.style.display = 'none';
                                            }
                                        }

                                        mythrottle(readData, 3000, "POST", pricingUrl, Token, productKeyValue).then((res) => {
                                            if(res){
                                                let result = JSON.parse(res)
                                                modifyPrice(result, productMoq)
                                            }
                                        })
                                    })
                                // }
                            }
                        }

                        //
                        if (data && data.event == "askChange") {
                            let askChangeValue = data.args;
                            // 禁用所有操作按钮
                            let urlParams = new URLSearchParams(configurable_frame_src);
                            let property_model_id = urlParams.get('propertyModelId');
                            if (property_model_id) {
                                // 如果修改属性会导致之前的设计丢失
                                let shouldContinue = confirm("Changing properties will cause the previous customization to be lost, do you want to continue changing?");
                                if (shouldContinue) {
                                    isChange = true;
                                    let askChangeParams = {
                                        event: 'askChanged',
                                        args: {
                                            ...askChangeValue
                                        }
                                    }
                                    askChangeParams.args.isContinue = true;
                                    iframe.contentWindow.postMessage(askChangeParams, '*');
                                } else {

                                }
                            }
                        }

                        // 属性表单的属性开始更改
                        if (data && data.event == "startChange") {
                            // 禁用所有操作按钮
                            let operating_button = document.getElementsByClassName("single_add_to_cart_button");
                            for (let index = 0; index < operating_button.length; index++) {
                                let element = operating_button[index];
                                element.disabled = true;
                            }

                            let is_attribute_price = qpmnProductData.attributePrice === 'attributePrice' ? true : false;
                            // 判断是否是属性价格
                            if (is_attribute_price) {
                                // 隐藏固定价格和价格列表
                                let qpsonProductPriceList = document.getElementById("qpson-product-price-list");
                                let qpsonProductPriceFix = document.getElementById("qpson-product-price-fix");
                                if (qpsonProductPriceList) {
                                    qpsonProductPriceList.style.display = "none";
                                }
                                if (qpsonProductPriceFix) {
                                    qpsonProductPriceFix.style.display = "none";
                                }
                                // 打开iframe骨架屏
                                skeleton_iframe_price.style.display = "block";
                                skeleton_iframe_price.style.height = "initial";
                            }
                        }

                        // 属性表单是属性更改完成
                        if (data && data.event == "changeFinish") {
                            let urlParams = new URLSearchParams(configurable_frame_src);
                            let is_attribute_price = qpmnProductData.attributePrice === 'attributePrice' ? true : false;
                            let is_finished_product = qpmnProductData.isQpmnFinishedProduct == true ? true : false;
                            let property_model_id = urlParams.get('propertyModelId');
                            if (property_model_id) {
                                saveNewProperyModel();
                            }
                            // 当产品是成品产品就获取ProperyModelId
                            if (is_finished_product) {
                                let skuProductIdInput = document.querySelector('input[name="skuProductId"]');
                                skuProductIdInput.value = '';
                                getProductKeyValueFun();
                                saveNewProperyModel();
                            }
                            // 当产品是属性价格，获取表单的属性值
                            if (is_attribute_price && iframe) {

                                debounce(getProductKeyValueFun)
                            }else{
                                // 启动所有操作按钮
                                let operating_button = document.getElementsByClassName("single_add_to_cart_button");
                                for (let index = 0; index < operating_button.length; index++) {
                                    let element = operating_button[index];
                                    element.disabled = false;
                                }
                            }

                        }

                        // if (data && data.event == "expired") {
                        //     iframe.style.display = "none";
                        //     skeleton_iframe.style.display = "block";
                        //     configurable_attribute.disabled = true;
                        //     let expiredParams = {
                        //         args: tokenType + " " + accessToken,
                        //         event: 'resetToken',
                        //     }
                        //     iframe.contentWindow.postMessage(expiredParams, '*');
                        // }

                        // 返回属性表单的keyvalue
                        if (data && data.event == "returnsProperyModelId") {
                            let propertyModelId = data.args;
                            let urlParams = new URLSearchParams(configurable_frame_src);
                            let is_finished_product = qpmnProductData.isQpmnFinishedProduct == true ? true : false;
                            let property_model_id = urlParams.get('propertyModelId');
                            if (property_model_id && isChange) {
                                let url = window.location.href;
                                let newUrl = addOrgToUrl(url, "propertyModelId", propertyModelId);
                                window.location.href = newUrl;
                                return
                            }

                            // 不是成品产品就跳转到builder
                            if (!is_finished_product) {
                                configurable_attribute.disabled = false;
                                let new_frame_src = addOrgToUrl(configurable_frame_src, "propertyModelId", propertyModelId);
                                window.location.href = new_frame_src;
                            } else {
                                let skuProductIdInput = document.querySelector('input[name="skuProductId"]');
                                skuProductIdInput.value = '';
                                let PropertyModelIdElement = document.querySelector('input[name="propertyModelId"]');
                                PropertyModelIdElement.value = propertyModelId;
                                //获取token
                                let accessToken = qpmnProductData.accessToken;
                                let tokenType = qpmnProductData.tokenType;
                                let Token = tokenType + " " + accessToken;
                                // 获取产品id
                                let qpson_product_id = qpmnProductData.qpsonProductId;
                                // 获取qpson接口地址
                                let qpson_server = qpmnProductData.server;
                                // 拼接获取属性价格的url
                                let url = qpson_server + 'api/attributeProperty/' + qpson_product_id + '/generateSkuProduct/' + propertyModelId + '/v3';
                                // 调用接口
                                // mythrottle(readData, 3000, "POST", url, Token, "", getSkuProductId)

                                readData("POST", url, Token, "", null).then((res) => {
                                    getSkuProductId(res)
                                })

                            }

                        }

                        // 获取表单大小
                        if (data && data.event == "onPageSize") {
                            let sku_name = document.getElementsByClassName("iframe-container")[0];
                            sku_name.style.height = data.args.htmlHeight + 1 + "px";
                            // sku_name.style.width = data.args.htmlWidth + "px";
                            iframe.style.height = data.args.htmlHeight + 1 + "px";
                            // iframe.style.width = data.args.htmlWidth + "px";
                        }
                        // 当属性表单报错时
                        if (data && data.event == "onDestroy") {
                            // 获取所有操作按钮
                            let operating_button = document.getElementsByClassName("single_add_to_cart_button");
                            for (let index = 0; index < operating_button.length; index++) {
                                let element = operating_button[index];
                                element.disabled = true;
                            }

                        }
                        // 当属性表单销毁时
                        if (data && data.event == "onError") {
                            // 获取所有操作按钮
                            let operating_button = document.getElementsByClassName("single_add_to_cart_button");
                            for (let index = 0; index < operating_button.length; index++) {
                                let element = operating_button[index];
                                element.disabled = true;
                            }
                            window.alert("Product properties error, please refresh the page or return to previous step");

                        }
                    }, 0);
                })
                iframe.src = iframe.name;
            }


        }

        if (qpmnProductData.qpmnProductType === 'sku' || qpmnProductData.qpmnProductType === "configurable") {
            let input = document.getElementsByClassName('input-text')[0];

            let delayTimer;

            input?.addEventListener('input', function () {
                let isAlertShown = false;
                clearTimeout(delayTimer);

                delayTimer = setTimeout(function () {
                    let min = Number(input.min);
                    let value = parseInt(input.value);
                    let add_cart_button = document.getElementsByClassName("single_add_to_cart_button")[0];

                    if (value < min) {
                        if (!isAlertShown) {
                            input.value = min;
                            let infoValue = `The minimum order quantity of this product is ${min}.`;
                            this.qpsonCreateInfoModal('Hint', infoValue);
                            if (qpmnProductData.qpmnProductType === "sku") {
                                add_cart_button.disabled = false;
                            }
                            if (qpmnProductData.qpmnProductType === "configurable") {
                                let skeletonPrice = document.getElementById('skeleton-detail-product-price');
                                if (skeletonPrice.style.display === 'none') {
                                    add_cart_button.disabled = false;
                                }
                            }
                            isAlertShown = true;
                        }
                    } else {
                        if (!isAlertShown) {
                            isAlertShown = true;
                            add_cart_button.disabled = false;
                        }
                    }
                }, 1000);

            });

            input?.addEventListener('blur', function () {
                // 获取加入购物车按钮
                let add_cart_button = document.getElementsByClassName("single_add_to_cart_button")[0];
                if (Number(input.min) > Number(input.value)) {
                    // 禁用所有操作按钮
                    add_cart_button.disabled = true;
                } else {
                    if (qpmnProductData.qpmnProductType === "configurable") {
                        let skeletonPrice = document.getElementById('skeleton-detail-product-price');
                        if (skeletonPrice.style.display === 'none') {
                            add_cart_button.disabled = false;
                        }
                    }
                    if (qpmnProductData.qpmnProductType === "sku") {
                        add_cart_button.disabled = false;
                    }
                }
                clearTimeout(delayTimer);
            });
        } else {

        }


    });



})(jQuery);

// 新增url
function addOrgToUrl(url, paramName, replaceWith) {

    if (url.indexOf(paramName) > -1) {
        let re = eval('/(' + paramName + '=)([^&]*)/gi');
        url = url.replace(re, paramName + '=' + replaceWith);
    } else {
        let paraStr = paramName + '=' + replaceWith;

        let idx = url.indexOf('?');
        if (idx < 0) {
            url += '?';
        }
        else if (idx >= 0 && idx != url.length - 1) {
            url += '&';
        }
        url = url + paraStr;
    }
    return url;
}

// 属性价格接口成功后调用的方法
function modifyPrice(res, productMoq) {
    if (res.success) {
        // 获取属性价格
        let data = res.data;
        // 当产品是可配置产品和成品产品
        let configurable_attribute = document.getElementById("qpson-configurable-design-button") || document.getElementById("qpson-finished-product");
        if (configurable_attribute && data) {
            // 获取货币符号
            let currencySymbol = qpmnProductData.currencySymbol;
            // 获取价格策略
            let productPrcingStrategy = data.strategy;
            let productPrcingList = data.priceTableDTOS;
            let productPrcingFix = data.priceFixDTOS;

            // 当价格策略是价格列表和固定价格时
            if (productPrcingList && productPrcingStrategy == "PriceTable" || productPrcingFix && productPrcingStrategy == "PriceFix") {
                let qpsonProductPriceFix = document.getElementById("qpson-product-price-fix");
                // 隐藏固定价格
                qpsonProductPriceFix.innerHTML = "";
                // 删除原有的价格列表
                for (let i = 0; i < qpsonProductPriceFix.childNodes.length - 1; i++) {
                    qpsonProductPriceFix.removeChild(qpsonProductPriceFix.childNodes[i]);
                }
                // let priceItem = currencySymbol + productPrcingFix;
                let productPrcingFixItem = document.createElement('span');
                productPrcingFixItem.className = 'woocommerce-Price-currencySymbol';
                productPrcingFixItem.innerHTML = currencySymbol;
                qpsonProductPriceFix.appendChild(productPrcingFixItem);
                // 获取价格列表的第一个价格
                if (productPrcingList && productPrcingStrategy == "PriceTable") {
                    if (productMoq) {
                        for (let i = 0; i < productPrcingList.length; i++) {
                            if (productPrcingList[i].from <= productMoq && productMoq <= productPrcingList[i].to) {
                                qpsonProductPriceFix.innerHTML += productPrcingList[i].price;
                                break;
                            }
                        }
                    } else {
                        qpsonProductPriceFix.innerHTML += productPrcingList[0].price;
                    }

                }
                // 获取的固定价格
                if (productPrcingFix && productPrcingStrategy == "PriceFix") {
                    qpsonProductPriceFix.innerHTML += productPrcingFix;
                }

                setTimeout(() => {
                    // 获取价格的骨架屏
                    let skeleton_iframe_price = document.getElementById("skeleton-detail-product-price");
                    // 获取价格元素的高度
                    let price_height = skeleton_iframe_price.offsetHeight;
                    // 获取价格的外框
                    let price_sku_name = document.getElementsByClassName("price-iframe-container")[0];
                    // 设置价格的外框的高度
                    price_sku_name.style.height = price_height + "px";
                    price_sku_name.style.width = "auto";
                    // 隐藏产品价格列表
                    let qpsonProductPriceList = document.getElementById("qpson-product-price-list");
                    if (qpsonProductPriceList) {
                        qpsonProductPriceList.style.display = "none";
                    }
                    // 隐藏iframe骨架屏
                    if (skeleton_iframe_price) {
                        skeleton_iframe_price.style.display = "none";
                    }
                    // skeleton_iframe_price.style.height = 0;
                    // skeleton_iframe_price.style.width = 0;
                    // 显示产品固定价格
                    qpsonProductPriceFix.style.display = "block";

                    executeList = [];
                    // 判断接口执行列表是否为空
                    if (stayExecuteList.length > 0) {
                        debounce(getProductKeyValueFun);
                    } else {
                        let skuProductIdInput = document.querySelector('input[name="skuProductId"]');
                        let is_finished_product = qpmnProductData.isQpmnFinishedProduct == true ? true : false;
                        let operating_button = document.getElementsByClassName("single_add_to_cart_button");
                        let input = document.getElementsByClassName('input-text')[0];
                        let inputMin = input?.min;
                        let inputValue = input?.value;

                        if (!is_finished_product) {
                            // 启用所有操作按钮
                            if (inputMin > inputValue) {
                                for (let index = 1; index < operating_button.length; index++) {
                                    let element = operating_button[index];
                                    element.disabled = false;
                                }
                            } else {
                                for (let index = 0; index < operating_button.length; index++) {
                                    let element = operating_button[index];
                                    element.disabled = false;
                                }
                            }
                        } else {
                            if (!skuProductIdInput.value) {
                                // 启用所有操作按钮
                                for (let index = 0; index < operating_button.length; index++) {
                                    let element = operating_button[index];
                                    element.disabled = true;
                                }
                            } else {
                                if (inputMin > inputValue) {
                                    for (let index = 1; index < operating_button.length; index++) {
                                        let element = operating_button[index];
                                        element.disabled = false;
                                    }
                                } else {
                                    for (let index = 0; index < operating_button.length; index++) {
                                        let element = operating_button[index];
                                        element.disabled = false;
                                    }
                                }
                            }

                        }
                    }

                }, 500);
            }

        }

    }
}

// 接口获取getSkuProductId后调用方法
async function getSkuProductId(res) {
    try {
        let result = JSON.parse(res);
        if (result.success) {
            // 获取属性价格
            let skuProductId = result.data;
            // 把返回的propertyModelId返回到表单input上
            let skuProductIdInput = document.querySelector('input[name="skuProductId"]');
            skuProductIdInput.value = skuProductId;

            // 获取价格的骨架屏
            let skeleton_iframe_price = document.getElementById("skeleton-detail-product-price");
            let operating_button = document.getElementsByClassName("single_add_to_cart_button");
            if ((!skeleton_iframe_price || (skeleton_iframe_price && skeleton_iframe_price?.style.display =="none") )) {
                // 启用所有操作按钮
                for (let index = 0; index < operating_button.length; index++) {
                    let element = operating_button[index];
                    element.disabled = false;
                }
            } else {
                for (let index = 0; index < operating_button.length; index++) {
                    let element = operating_button[index];
                    element.disabled = true;
                }
            }
        }
    } catch (error) {
        console.error("Error in getSkuProductId:", error);
    }
}

// 传入接口类型，地址，token，产品的属性对象，方法
async function readData(methodType, url, token, data) {
    try {
        const response = await syncRequest({
            method: methodType,
            url: url,
            headers: {
                'Content-Type': 'application/json',
                'Authorization': token
            },
            data: data
        });

        return response;
    } catch (error) {
        console.error("Error in readData:", error);
    }
}


function syncRequest(obj) {
    const promise = new Promise(function (resolve, reject) {
        const handler = function () {
            if (this.readyState !== 4) {
                return
            }
            if (this.status === 200) {
                resolve(this.response)
            } else {
                reject(new Error(this.statusText));
            }
        }
        let oXmlHttp = createXMLHttpRequest();
        oXmlHttp.open(obj.method, obj.url, true);
        oXmlHttp.onreadystatechange = handler;

        if (obj.headers) {
            for (let h in obj.headers) {
                oXmlHttp.setRequestHeader(h, obj.headers[h]);
            }
        }

        if (obj.data) {
            oXmlHttp.send(JSON.stringify(obj.data));
        } else {
            oXmlHttp.send();
        }
    })
    return promise;
}

function createXMLHttpRequest() {
    try {
        // Firefox, Opera 8.0+, Safari
        xmlHttp = new XMLHttpRequest();
    } catch (e) {
        // Internet Explorer
        try {
            xmlHttp = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
            xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
    }
    return xmlHttp;
}

let executeList = [];
let stayExecuteList = [];
let executeListIndex = 0;
// 防抖
function debounce(fun) {

    executeListIndex++;
    stayExecuteList.push(executeListIndex);
    if (executeList.length == 0) {
        fun();
        executeList.push(executeListIndex);
    } else {
        return
    }

}

// 节流
async function mythrottle(fun, wait, type, url, Token, productKeyValue) {
    if (stayExecuteList.length > 0) {
        const response = fun(type, url, Token, productKeyValue);
        executeList = stayExecuteList;
        stayExecuteList = [];
        // debounce(getProductKeyValueFun)
        return response;
    } else {
        return
    }


}

// 获取产品属性表单的属性值
function getProductKeyValueFun() {
    let iframe = document.getElementById("product-attribut-frame");
    if (iframe) {
        let getProductKeyValueParams = {
            event: 'getProductKeyValue',
        }
        iframe.contentWindow.postMessage(getProductKeyValueParams, '*');
    }
}

// 可配置产品定制|编辑按钮调用方法
function saveProperyModel(event) {
    let iframe = document.getElementById("product-attribut-frame");
    let saveProperyModelParams = {
        args: false,
        event: 'saveProperyModel',
    }

    iframe && iframe.contentWindow.postMessage(saveProperyModelParams, '*');
    event?.preventDefault();
    event?.stopPropagation();
}

// 获取新的ProperyModelId的方法
function saveNewProperyModel(event) {
    let iframe = document.getElementById("product-attribut-frame");
    let saveProperyModelParams = {
        args: true,
        event: 'saveProperyModel',
    }

    iframe && iframe.contentWindow.postMessage(saveProperyModelParams, '*');
    event?.preventDefault();
    event?.stopPropagation();
    event?.preventDefault();
}

// sku产品调用方法
function openSkuBuilder(event) {
    let sku_attribute = document.getElementById("qpson-sku-design-button");
    let sku_src = sku_attribute.value;
    window.location.href = sku_src;
    event.preventDefault();
    event.stopPropagation();
}
