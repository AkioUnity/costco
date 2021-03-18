<?php
/**
 * Created by PhpStorm.
 * User: akio
 * Date: 2019.09.08
 * Time: 4:21 下午
 */
?>

<style>
    /** SPINNER CREATION **/

    .loader {
        position: relative;
        text-align: center;
        margin: 15px auto 35px auto;
        z-index: 9999;
        display: block;
        width: 80px;
        height: 80px;
        border: 10px solid rgba(0, 0, 0, .3);
        border-radius: 50%;
        border-top-color: #000;
        animation: spin 1s ease-in-out infinite;
        -webkit-animation: spin 1s ease-in-out infinite;
    }

    @keyframes spin {
        to {
            -webkit-transform: rotate(360deg);
        }
    }

    @-webkit-keyframes spin {
        to {
            -webkit-transform: rotate(360deg);
        }
    }

    /** MODAL STYLING **/

    .modal-content {
        border-radius: 0px;
        box-shadow: 0 0 20px 8px rgba(0, 0, 0, 0.7);
    }
</style>
<!-- Modal -->
<div class="modal fade" id="loadMe" tabindex="-1" role="dialog" aria-labelledby="loadMeLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="loader"></div>
                <div class="loader-txt">
                    <h2 id="modal_title" style="margin-bottom: 20px"></h2>
                    <div id="modal_body" style="font-size: 12px; ">
                        <div class='copy-row row'>
                            <div class="col-sm-4" style="color: #0f467d">
                                <label>#</label>
                                <label class="email">Email</label>
                            </div>
                            <div class="col-sm-8">
                                <label id="status" class="status-message">Status</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let isFirst = true;

    function addSection(email, status) {
        let email_body = email.replace("@gmail.com", '');
        email_body = email_body.replace("+", '_');
        let statusObj = $('#' + email_body);
        if (!statusObj.length) {
            // console.log(statusObj);
            let wrap = $("#modal_body");
            let clone;
            if (isFirst) {
                isFirst = false;
                clone = wrap.find('.copy-row:first');
            }
            else {
                clone = wrap.find('.copy-row:first').clone();
                wrap.find(".copy-row:last").after(clone);
            }
            clone.find('.email').text(email);
            statusObj = clone.find('.status-message');
            statusObj.attr('id', email_body);
        }
        statusObj.text(status);
    }

    function LoadModal(title) {
        // e.preventDefault();
        isFirst = true;
        isFirstAjax=true;
        $("#modal_title").text(title);
        $("#loadMe").modal({
            backdrop: "static", //remove ability to close modal with click
            keyboard: false, //remove option to close with keyboard
            show: true //Display loader!
        });
        // ajaxCall();
        setInterval(ajaxCall, 3000);
    }
    let isFirstAjax=true;
    function ajaxCall() {
        let testUrl = baseURL + "api/waits/event";
        // if (isFirstAjax){
        //     testUrl = baseURL + "api/waits/reset";
        // }
        $.get(
            testUrl,
            function (response) {
                if (isFirstAjax){
                    isFirstAjax=false;
                    return;
                }
                if (response.length == 0)
                    EndModal();
                else {
                    let isFinished=false;
                    response.forEach(function (entry) {
                        // console.log(entry);
                        addSection(entry.email, entry.status);
                        if (entry.status!="success" && entry.status!="finished"){
                            isFinished=true;
                        }
                    });
                    if (!isFinished){
                        $("#modal_title").text("Finished!");
                        setTimeout(function () {
                            location.reload();
                        }, 2000);
                    }
                }
            },
            "json"
        );
    }

    function EndModal() {
        clearInterval(ajaxCall());
        $("#loadMe").modal("hide");
    }
</script>