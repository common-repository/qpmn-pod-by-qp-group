function openQpsonModal(event,modalName, url) {
    let modalList = document.getElementsByClassName("modal fade")
    if(modalList.length === 0){
        // let preview_button = document.getElementById("qpson-preview-buttton");
        url = event.currentTarget.value;
        document.body.appendChild(this.qpsonCreateModal(modalName, url));
        document.body.setAttribute('style', 'overflow: hidden;')
        var clientWidth = modal.clientWidth;
        let modalElemt = document.querySelector('.modal-dialog')
        let modalHeader = document.querySelector('.builder-modal-header');
        let modalFooter = document.querySelector('.builder-modal-footer');
        let modalFooterButton = document.querySelector('.builder-modal-footer .btn-default')
        if (clientWidth < 1152) {
            modalElemt.style.height = '90%';
            modalElemt.style.width = '96%';
            modalHeader.style.padding = '0 8px';
            modalFooter.style.padding = '0 8px';
            modalFooter.style.display = 'flex';
            modalFooter.style.alignItems = 'center';
            modalHeader.style.display = 'flex';
            modalHeader.style.alignItems = 'center';
            modalHeader.style.justifyContent = 'space-between';
        }
        if (clientWidth < 663) {
            modalHeader.style.padding = '4px';
            modalFooterButton.style.position = 'initial';
            modalFooterButton.style.width = '100%'
        }
        event.preventDefault();
        event.stopPropagation();
    }
}

/**
* Close the modal element
*/
function closeModal() {
    let modalList = document.getElementsByClassName("modal")
    if ( this.modal && modalList.length == 1 ) {
        this.modal.parentNode.removeChild(this.modal);
        this.modal = null;
        document.body.removeAttribute('style')
    }else if(this.modal && modalList.length > 1 ){
        let modalNumber = modalList.length;
        for (let i = 0; i < modalNumber; i++) {
            this.modal.parentNode.removeChild(modalList[0]);
            if(modalList.length == 0){
                this.modal = null;
                document.body.removeAttribute('style')
            }
        }
    }
}
/**
 * Create modal content
 * @param {string} modalName
 * @param {string} url
 * @returns {HTMLDivElement}
 */
function qpsonCreateModal(modalName, url) {

    // create iframe
    var modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.setAttribute('id', 'builderModal');
    modal.setAttribute('role', 'dialog');
    modal.setAttribute('style', 'opacity:1 ;display:block;z-index:99999;position:fixed;top:0;right:0;bottom:0;left:0;overflow: hidden;outline: 0;');


    var modalDialog = document.createElement('div');
    modalDialog.className = 'modal-dialog';
    modalDialog.setAttribute('style', 'height:88% !important;width:72% !important;transform: initial;margin:30px auto');

    var modalContent = document.createElement('div');
    modalContent.className = 'modal-content';
    modalContent.setAttribute('style', 'height:100%;box-shadow:0 5px 15px rgba(0,0,0,.5);position:relative;background-color:#fff;background-clip:padding-box;border:1px solid rgba(0,0,0,.2);border-radius:6px;');

    var modalHeader = document.createElement('div');
    modalHeader.className = 'builder-modal-header';
    modalHeader.setAttribute('style', 'padding:0 8px;');
    modalHeader.style.height = '5%';
    modalHeader.style.borderBottom = '1px solid #e5e5e5';
    modalHeader.style.display = 'flex';
    modalHeader.style.justifyContent = 'space-between';
    modalHeader.style.alignItems = 'center';
    modalHeader.style.padding = '0 8px';


    var modalHeaderTitle = document.createElement('h4');
    modalHeaderTitle.className = 'modal-title';
    modalHeaderTitle.setAttribute('style', 'float:left;margin:0;');
    modalHeaderTitle.innerText = modalName;
    var closeBtn = document.createElement('button');
    closeBtn.className = 'close';
    closeBtn.setAttribute('type', 'button');
    closeBtn.setAttribute('style', 'outline:none;font-size:36px;float:right;font-weight:700;line-height:26px;color:#000;opacity:0.2;padding:0;cursor:pointer;background:0 0;border:0;');
    closeBtn.innerText = "×";
    closeBtn.onclick = this.closeModal.bind(this);
    modalHeader.appendChild(modalHeaderTitle);
    modalHeader.appendChild(closeBtn);
    modalContent.appendChild(modalHeader);

    var modalBody = document.createElement('div');
    modalBody.className = 'modal-body';
    modalBody.setAttribute('style', 'height:88%;position:relative;padding:2px 15px');

    var iframe = document.createElement('iframe');
    iframe.setAttribute('id', 'builder-iframe');
    iframe.src = url;
    iframe.width = '100%';
    iframe.height = '100%';
    modalBody.appendChild(iframe);
    modalContent.appendChild(modalBody);


    var modalFooter = document.createElement('div');
    modalFooter.className = 'builder-modal-footer';
    modalFooter.setAttribute('style', 'padding:10px');
    modalFooter.style.borderTop = '1px solid #e5e5e5';
    modalFooter.style.height = '7%';

    var closeButton = document.createElement('button');
    closeButton.setAttribute('style', 'outline:none;color:#333;background-color:#fff;cursor:pointer;border: 1px solid #ccc;padding:6px 12px;font-size:14px;line-height:20px;border-radius:4px');
    closeButton.style.position = 'absolute';
    closeButton.style.right = '8px';
    // closeButton.style.bottom = '8px';
    closeButton.className = 'btn btn-default';
    closeButton.dataDismiss = 'modal';
    closeButton.innerText = "Close";
    closeButton.onclick = this.closeModal.bind(this);
    modalFooter.appendChild(closeButton);

    modalContent.appendChild(modalFooter);
    modalDialog.appendChild(modalContent)
    modal.appendChild(modalDialog)

    this.modal = modal;

    return modal;
}

function qpsonCreateInfoModal(modalName, value) {

    // create iframe
    var modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.setAttribute('id', 'builderModal');
    modal.setAttribute('role', 'dialog');
    // modal.onclick = this.closeInfoModal.bind(this);
    modal.setAttribute('style', 'opacity:1 ;display:block;z-index:100000;position:fixed;top:0;right:0;bottom:0;left:0;overflow: hidden;outline: 0;');

    var modalBackdrop = document.createElement('div');
    modalBackdrop.className = 'modal-backdrop';
    modalBackdrop.setAttribute('style', 'opacity:0.6 ;background-color:#000;display:block;z-index:99999;position:fixed;top:0;right:0;bottom:0;left:0;overflow: hidden;outline: 0;');

    var modalDialog = document.createElement('div');
    modalDialog.className = 'modal-dialog';
    modalDialog.setAttribute('style', 'height:20% !important;width:30% !important;transform: initial;margin:8% auto');

    var modalContent = document.createElement('div');
    modalContent.className = 'modal-content';
    modalContent.setAttribute('style', 'height:100%;box-shadow:0 5px 15px rgba(0,0,0,.5);position:relative;background-color:#fff;background-clip:padding-box;border:1px solid rgba(0,0,0,.2);border-radius:6px;');

    var modalHeader = document.createElement('div');
    modalHeader.className = 'builder-modal-header';
    modalHeader.setAttribute('style', 'padding:0 8px;');
    modalHeader.style.height = '20%';
    modalHeader.style.display = 'flex';
    modalHeader.style.justifyContent = 'space-between';
    modalHeader.style.alignItems = 'center';
    modalHeader.style.padding = '0 8px';


    var modalHeaderTitle = document.createElement('h4');
    modalHeaderTitle.className = 'modal-title';
    modalHeaderTitle.setAttribute('style', 'float:left;margin:0;');
    modalHeaderTitle.innerText = modalName;
    var closeBtn = document.createElement('button');
    closeBtn.className = 'close';
    closeBtn.setAttribute('type', 'button');
    closeBtn.setAttribute('style', 'outline:none;font-size:36px;float:right;font-weight:700;line-height:26px;color:#000;opacity:0.2;padding:0;cursor:pointer;background:0 0;border:0;');
    closeBtn.innerText = "×";
    closeBtn.onclick = this.closeInfoModal.bind(this);
    modalHeader.appendChild(modalHeaderTitle);
    modalHeader.appendChild(closeBtn);
    modalContent.appendChild(modalHeader);

    var modalBody = document.createElement('div');
    modalBody.className = 'modal-body';
    modalBody.setAttribute('style', 'height:50%;position:relative;padding:2px 15px;font-size:16px;display:flex;align-items:center');

    var modalBodyContent = document.createElement('div');
    modalBody.appendChild(modalBodyContent);
    modalBody.innerText = value;
    modalContent.appendChild(modalBody);


    var modalFooter = document.createElement('div');
    modalFooter.className = 'builder-modal-footer';
    modalFooter.setAttribute('style', 'padding:10px');
    // modalFooter.style.borderTop = '1px solid #e5e5e5';
    modalFooter.style.height = '30%';

    var closeButton = document.createElement('button');
    closeButton.setAttribute('style', 'outline:none;color:#333;background-color:#fff;cursor:pointer;border: 1px solid #ccc;padding:6px 12px;font-size:14px;line-height:20px;border-radius:4px');
    closeButton.style.position = 'absolute';
    closeButton.style.right = '8px';
    // closeButton.style.bottom = '8px';
    closeButton.className = 'btn btn-default';
    closeButton.dataDismiss = 'modal';
    closeButton.innerText = "OK";
    closeButton.onclick = this.closeInfoModal.bind(this);
    modalFooter.appendChild(closeButton);

    modalContent.appendChild(modalFooter);
    modalDialog.appendChild(modalContent)
    modal.appendChild(modalDialog)

    this.modal = modal;
    this.modalBackdrop = modalBackdrop;

    document.body.appendChild(modal);
    document.body.appendChild(modalBackdrop);
    document.body.setAttribute('style', 'overflow: hidden;margin-right:17px')

}

function closeInfoModal() {
    let modalList = document.getElementsByClassName("modal")
    if (this.modal && modalList.length == 1 && this.modalBackdrop) {
        this.modal.parentNode.removeChild(this.modal);
        this.modalBackdrop.parentNode.removeChild(this.modalBackdrop);
        this.modal = null;
        this.modalBackdrop = null;
        document.body.removeAttribute('style')
        // console.log(this.modalBackdrop)
    } else if (this.modal && modalList.length > 1) {
        let modalNumber = modalList.length;
        for (let i = 0; i < modalNumber; i++) {
            this.modal.parentNode.removeChild(modalList[0]);
            if (modalList.length == 0) {
                this.modal = null;
                document.body.removeAttribute('style')
            }
        }
    }
}

(function ($) {
    // "use strict";

    $(document).ready(function () {
        var sku_name = document.getElementsByClassName("qpson-thumbnail-container");
        if (sku_name) {
            $.each(sku_name, function (i) {
                sku_name[i].style.display = "flex";
            })
        }
        var builderTarget = document.querySelector('#order_review');
        if (!builderTarget) {
            builderTarget = document.querySelector('.woocommerce-order-details');
        }
        // Create an observer instance.
        var builderObserver = new MutationObserver((mutations) => {
            var previewButton = document.getElementsByClassName("order-preview");
            $.each(previewButton, function (i) {
                var modalText = previewButton[i].innerText;
                var builderUrl = previewButton[i].value;
                previewButton[i].addEventListener("click", function () {
                    openQpsonModal(event,modalText, builderUrl)
                })
            })
        });

        // Pass in the target node, as well as the observer options.
        if (builderTarget) {
            builderObserver.observe(builderTarget, {
                // attributes: true,
                childList: true,
                characterData: true,
                subtree: true,
            });
        }


        var previewButton = document.getElementsByClassName("order-preview");
        $.each(previewButton, function (i) {
            var modalText = previewButton[i].innerText;
            var builderUrl = previewButton[i].value;
            previewButton[i].addEventListener("click", function () {
                openQpsonModal(event,modalText, builderUrl)
            })
        })



        var cartPreviewButton = document.getElementsByClassName("qpson-cart-preview-button");
        if(!cartPreviewButton){
            var cartPreviewButton = document.querySelectorAll(".woo-c_cart_table_item_thumbnail button");
        }
        if (cartPreviewButton) {
            var cartTarget = document.querySelector('.woocommerce');
            $.each(cartPreviewButton, function (i) {
                if (!cartPreviewButton[i].onclick) {
                    var cartObserver = new MutationObserver((mutations) => {
                        var modalText = cartPreviewButton[i].innerText;
                        var builderUrl = cartPreviewButton[i].value;
                        cartPreviewButton[i].style.border = "none";
                        cartPreviewButton[i].style.boxShadow = "none";
                        cartPreviewButton[i].addEventListener("click", function () {
                            openQpsonModal(event,modalText, builderUrl)
                        })
                        var sku_name = document.getElementsByClassName("qpson-thumbnail-container");
                        if (sku_name) {
                            $.each(sku_name, function (i) {
                                sku_name[i].style.display = "flex";
                            })
                        }
                    });

                    if (cartTarget) {
                        cartObserver.observe(cartTarget, {
                            // attributes: true,
                            childList: true,
                            characterData: true,
                            subtree: true,
                        });
                    }
                }
            })
        }


        // var cartPreviewButton = document.getElementsByClassName("qpson-cart-preview-button");
        // $.each(cartPreviewButton, function (i) {
        //     var modalText = cartPreviewButtpon[i].innerText;
        //     var builderUrl = cartPreviewButton[i].value;
        //     cartPreviewButton[i].style.border = "none";
        //     cartPreviewButton[i].style.boxShadow = "none";
        //     cartPreviewButton[i].addEventListener("click", function () {
        //         openQpsonModal(modalText, builderUrl)
        //     })
        // })

    });



})(jQuery);