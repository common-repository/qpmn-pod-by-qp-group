jQuery(function ($) {
    "use strict";
    $(function () {

        var observer = new MutationObserver(function (mutations) {
            initialize()
        });

        // var imgElement = document.querySelector('.qpson-preview-img');
        var cartTarget = document.querySelector('.widget_shopping_cart');
        if (cartTarget) {
            // 监听img元素的属性变化
            observer.observe(cartTarget, {
                // attributes: true,
                childList: true,
                characterData: true,
                subtree: true,
            });
        }

        var cartFrom = document.querySelector('.entry-content');
        if (cartFrom) {
            // 监听img元素的属性变化
            observer.observe(cartFrom, {
                // attributes: true,
                childList: true,
                characterData: true,
                subtree: true,
            });
        }

        var checkOutTarget = document.querySelector('.woocommerce-checkout');
        if (checkOutTarget) {
            // 监听img元素的属性变化
            observer.observe(checkOutTarget, {
                // attributes: true,
                childList: true,
                characterData: true,
                subtree: true,
            });
        }


        if (myData.type === 'Cart') {
            let inputList = document.getElementsByClassName('input-text qty text');

            Array.from(inputList).forEach((input)=>{
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
    
                                isAlertShown = true;
                            }
                        } else {
                            if (!isAlertShown) {
                                isAlertShown = true;
                            }
                        }
                    }, 1000);
    
                });
    
                input?.addEventListener('blur', function () {
                    clearTimeout(delayTimer);
                });
            })

            
        } else {

        }


        initialize()
    });


    function initialize() {
        var imgEs = $(".qpson-preview-img"), intervals = [];
        let mockImg = $(".qpson-preview-img-mock");
        if ((mockImg && mockImg.length > 0) || (imgEs && imgEs.length > 0)) {
            let img;
            let parentElement;
            let parentWidth;
            let parentHeight;
            if (imgEs && imgEs.length > 0) {
                img = imgEs[0];
                parentElement = img.parentElement;
                parentWidth = parentElement.offsetWidth;
                parentHeight = parentElement.offsetHeight;

            } else {
                if (mockImg && mockImg.length > 0) {
                    img = mockImg[0];
                    parentElement = img.parentElement;
                    parentWidth = parentElement.offsetWidth;
                    parentHeight = parentElement.offsetHeight;
                }
            }
            const constomStyle = `
                <style>
                    @keyframes borderAround {
                        0%,
                        100% {
                        clip: rect(0 ${parentWidth + 2}px 0 0);
                        }
                        25% {
                        clip: rect(0 ${parentWidth + 2}px ${parentHeight + 2}px ${parentWidth + 2}px);
                        }
                        50% {
                        clip: rect(${parentHeight + 2}px ${parentWidth + 2}px ${parentHeight + 2}px 0);
                        }
                        75% {
                        clip: rect(0 0 ${parentHeight + 2}px 0);
                        }
                    }
                </style>`;
            setTimeout(() => {
                $('head').append(constomStyle);
            })
        }

        /**
         * @description 多图片重复加载处理
         ** 1.外部循环 每6秒一次 直到图片加载成功后停止（外部整体）
         ** 2.内部循环 每1秒一次 共2次（2次后停止）
        */

        if (imgEs && imgEs.length > 0) {
            for (var k = 0; k < imgEs.length; k++) {
                let img = imgEs[k];
                if(img['dataset'].src){
                    img.parentNode.setAttribute('style', "border:none;");
                    img.parentElement.classList.add("qpmn_load");
                    var imgUrl = img ? img['dataset'].src : null;
                    imgUrl ? fetchFile(imgUrl).then((resp) => {
                        setImgPath(resp, img);
                    }, (error) => {
                        var old = findInterval(imgUrl);
                        if (!old) {
                            intervals.push({
                                imgUrl: imgUrl,
                                interval: createRetryInterval(fetchFile, imgUrl, setImgPath, img)
                            });
                        }
                    }) : console.log(`imgUrl data-src is null`);
                }  
            }
        }

        if (mockImg && mockImg.length > 0) {
            for (var k = 0; k < mockImg.length; k++) {
                let img = mockImg[k];
                img.parentNode.setAttribute('style', "border:none;");
                img.parentElement.classList.add("qpmn_load");
                var imgUrl = img ? img['dataset'].src : null;
                let imgUrlObj = {
                    url: imgUrl
                }
                // setImgPath(imgUrlObj, img)
                let mockId = img ? img['dataset'].mockid : null;
                let url = myData.server + `api/mockupImages/${mockId}/status`;
                let token = 'Basic ' + myData.token;
                let header = {
                    "Authorization": token,
                }


                getMockInfo(imgUrlObj, img, url, 'GET', null, header, mockId);
            }
        }

        function getMockInfo(imgUrlObj, img, url, method, data, header, mockId) {
            ajax(url, method, data, header).then(function (response) {
                let res = JSON.parse(response);
                if (res.success && res.data && res.data.status === 'SUCCESS') {
                    let newImgUrl = myData.file + res.data.fileName;
                    img['dataset'].src = newImgUrl;
                    let mockImgUrlObj = {
                        url: newImgUrl,
                        status: 200
                    }
                    let newMockId = res.data.id;
                    setImgPath(mockImgUrlObj, img, null, newMockId);
                } else {
                    setImgPath(imgUrlObj, img, null, mockId)

                    setTimeout(function () {
                        getMockInfo(imgUrlObj, img, url, method, data, header)
                    }, 4000)
                }
            })
                .catch(function (error) {
                    // console.log(131)
                })
        }


        function ajax(url, method, data, header) {
            return new Promise((resolve, reject) => {
                let xhr = new XMLHttpRequest();
                xhr.open(method, url);
                xhr.onload = function () {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        resolve(xhr.responseText)
                    } else {
                        reject(new Error(xhr.statusText))
                    }
                }

                if (header) {
                    for (let key in header) {
                        xhr.setRequestHeader(key, header[key])
                    }
                }

                xhr.onerror = function () {
                    reject(new Error("Network error"))
                }
                if (data) {
                    xhr.send(JSON.stringify(data))
                } else {
                    xhr.send();
                }
            })
        }

        /** 
         * @param {Function} executeMethod 
         * @param {string} params 
         * @param {Function} resolveCallback 
         * @param {imgElement} resolveParams 
         * @description 创建图片加载 定时器
         */
        function createRetryInterval(executeMethod, params, resolveCallback, resolveParams) {
            return setInterval(() => {
                var qty = 0, coreInterval = setInterval(() => {
                    qty = qty + 1;
                    if (qty <= 2 && executeMethod) {
                        executeMethod(params).then((resp) => {
                            var imgUrl = resolveParams ? resolveParams['dataset'].src : null;
                            var img = getImgByUrl(imgUrl);
                            resolveCallback(resp, img, () => {
                                var intervalData = findInterval(imgUrl);
                                intervalData && intervalData.interval && clearInterval(intervalData.interval);
                            });
                        }, (error) => { console.error(error); });
                    } else {
                        clearInterval(coreInterval);
                    }
                }, 4000);
            }, 6000);
        }

        /**
         * @param {string} imgUrl 
         * @description 查找 定时中是否存在定时器
         */
        function findInterval(imgUrl) {
            var result = intervals.find((item) => {
                return item && item.imgUrl && item.imgUrl != "" && item.imgUrl == imgUrl;
            });
            return result ? result : null;
        }

        /**
        * @param {string} url 
        * @description 加载图片
        */
        function fetchFile(url) {
            var fileRequest = new Request(url);
            return new Promise((resolve, reject) => {
                // 下载图片
                fetch(fileRequest, {
                    mode: 'cors'
                }).then(resolveResp => {
                    if (resolveResp.status != 200 || !resolveResp.ok) {
                        // 网络通畅,下载失败
                        reject(resolveResp);
                    } else {
                        // 网络通畅,下载成功
                        resolve(resolveResp);
                    }
                }, rejectResp => {
                    // 网络异常处理
                    reject(rejectResp);
                });
            });
        }

        /**
         * @param {object} fetchResp 
         * @param {Element} imgElement 
         * @description 设置图片URL属性
         */
        function setImgPath(fetchResp, imgElement, callback, id) {

            var imgTarget = document.querySelector('.entry-content');
            var imgObserver = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {

                    if (mutation.type === 'attributes' && mutation.attributeName === 'src' && mutation.target instanceof HTMLImageElement) {
                        var img = mutation.target;
                        var imgSrc = img?.src;
                        let mockid = img?.dataset.mockid;
                        var imgDataSrc;
                        if (mockid) {
                            imgDataSrc = fetchResp.url;
                        } else {
                            imgDataSrc = img?.dataset.src;
                        }
                        var imgDataSrcWH = imgDataSrc + "/300/300 300w," + imgDataSrc + "/150/150/ 150w," + imgDataSrc + "/100/100/ 100w,"
                        if (imgSrc && imgDataSrc && imgSrc != imgDataSrc) {
                            setTimeout(() => {
                                if (fetchResp.status & fetchResp.status == 200 && img.parentElement && img.parentElement.classList.contains("qpmn_load")) {
                                    img.src = imgDataSrc;
                                    img.srcset = imgDataSrcWH;
                                    img.parentElement.classList.remove("qpmn_load");
                                }
                            }, 500)

                        }
                    }
                })
            });


            imgTarget = imgTarget ? imgTarget : imgElement;

            imgObserver.observe(imgTarget, {
                attributes: true,
                childList: true,
                characterData: true,
                subtree: true,
            });


            setTimeout(function () {
                imgElement.parentNode.style.display = 'flex';
                const parentComputedStyle = window.getComputedStyle(imgElement.parentNode);
                const parentHeightValue = parentComputedStyle.getPropertyValue("height");
                if(!parentHeightValue){
                    imgElement.parentNode.setAttribute('style', `height:${imgElement.height}px;width:${imgElement.width}px;display:flex;`);
                }
                imgElement.setAttribute('style', "object-fit:contain;");
                imgElement.src = fetchResp.url;
                imgElement.srcset = `${fetchResp.url}/300/300 300w,${fetchResp.url}/150/150 150w,${fetchResp.url}/100/100 100w,`;
                if (fetchResp.status && fetchResp.status == 200) {
                    if (imgElement.parentElement && imgElement.parentElement.classList.contains("qpmn_load")) {
                        imgElement.parentElement.classList.remove("qpmn_load");
                    }
                }

            }, 500)
            callback && callback();
        }

        function getImgByUrl(url, id) {
            var imgList = $(".attachment-woocommerce_thumbnail"), img;
            for (let j = 0; j < imgList.length; j++) {
                const imgData = imgList[j];
                if (imgData.dataset.src == url) {
                    img = imgData;
                    break;
                } else {
                    if (imgData.dataset.mockid && imgData.dataset.mockid == id) {
                        img = imgData;
                        break;
                    }
                }
            }
            return img;
        }
    }






});