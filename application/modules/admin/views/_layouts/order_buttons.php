<?php include('waiting.php') ?>
<script>
    let baseURL = "<?php echo base_url(); ?>";
</script>
<script src="<?php echo base_url('assets/js/page.js') ?>"></script>


<div style="margin-left: 0;">
    <button class="btn btn-primary" onclick="importList();">Import</button>
    <?php if ($order_type == 'new') { ?>
        <button class="btn btn-info" onclick="makeOrder();">Order</button>
        <button class="btn btn-info" onclick="makeOrder(true);">Multiple address
            Order
        </button>
    <?php } else if ($order_type == 'placed') { ?>
        <button class="btn btn-info" onclick="updateTrack();">Track</button>
        <button class="btn btn-info" onclick="updateTrack(true);">Multiple Address Track
        </button>
        <!--        <button class="btn btn-info" onclick="makeReOrder(0);">Re-Order</button>-->
    <?php } else if ($order_type == 'price') { ?>
<!--        <button class="btn btn-info" onclick="importPriceList();">Import</button>-->
<!--        <button class="btn btn-info" onclick="clearPrice();">Clear</button>-->
        <button class="btn btn-info" onclick="makeCheck(0);">Check</button>
    <?php } ?>
    <label>   Time Interval(ms)</label>
    <input type="number" id="interval" value="1000"/>
</div>
<div class="overlay" style="display: none;"></div>
<div class="loading-img" style="display: none;"></div>

<div class="modal fade" id="import_modal" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog">
        <form id="import_form" action="#" method="post" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Import order list
                        from xlsx file</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <input type="file" name="file" class="form-control"
                               accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel"
                               required/>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-default">Import</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="price_import_modal" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog">
        <form id="price_import_form" action="#" method="post" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Import item list
                        from xlsx file</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <input type="file" name="file" class="form-control"
                               accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel"
                               required/>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-default">Import</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function importList() {
        $('#import_modal').modal('show');
    }

    $('#import_form').submit(function (e) {
        e.preventDefault();
        // if (!window.confirm('will you import this file?'))
        //     return false;
        setMask(1);
        $(this).ajaxSubmit({
            url: baseURL + 'get/importList',
            type: 'post',
            dataType: 'json',
            success: function (result) {
                console.log(result);
                if (result.success) {
                    $('#import_modal').modal('hide');
                    searchOrders();
                } else {
                    window.alert(result.msg);
                    setMask(0);
                }
            },
            error: function (result) {
                setMask(0);
                console.log(result.responseText);
                $('#import_modal').modal('hide');
                searchOrders();
            },
            resetForm: true
        });
        return false;
    });

    function searchOrders() {
        // window.location.href = "order/new_orders";
    }

    // let url="http://localhost:8111/";
    let url = "http://localhost:8111/";

    async function GetCheckedList(modal_title, msg, isMultiple, type = 'order') {
        let checked_list = [];
        $('.select-row:checked').each(function () {
            let email = $(this).data('email');
            if (!checked_list[email])
                checked_list[email] = [$(this).data('id')];
            else
                checked_list[email].push($(this).data('id'));
            // email_list.push(email);
        });
        if (!Object.keys(checked_list).length) {
            window.alert("Please select items");
            return;
        }

        LoadModal(modal_title);
        if (!window.confirm(msg))
            return;
        let stepList = [];
        console.log(checked_list);
        for (let k in checked_list) {
            if (checked_list.hasOwnProperty(k)) {
                console.log("ajax call email is " + k);
                // console.log(checked_list[k]);
                if (type=='track') {
                    let email=k;
                    $.ajax({
                        url: "http://localhost:8111/updateTrack",
                        type: "post",
                        dataType: "json",
                        data: {"id": checked_list[k], "isMultiple": isMultiple, email: email},
                        success: function (result) {
                            console.log(email, "Result", result);
                        },
                        error: function (result) {
                            console.log(email, "Error", result);
                        }
                    });
                }
                else {  //order
                    SendOrderAjax(checked_list[k], isMultiple, stepList, k);
                }
                let interval=parseInt($('#interval').val()) || 1000;
                await sleep(interval);
            }
        }
    }

    function makeOrder(isMultiple = false) {
        let msg = "will you really place order?";
        let modal_title = "Waiting for Order";
        if (isMultiple) {
            msg = "will you really place Multiple Address  order?";
            modal_title = "Waiting for Multiple Address Order";
            // stepList = multiple_order.split(',');
        }
        GetCheckedList(modal_title, msg, isMultiple);
    }

    function sleep(ms) {
        console.log('sleep ',ms);
        return new Promise(resolve => setTimeout(resolve, ms));
        // let start = new Date().getTime();
        // while (new Date().getTime() < start + delay) ;
    }

    function SendOrderAjax(idList, isMultiple, order_step, email) {
        let remainList = idList;
        // if (isMultiple) {
        //     idList = idList.slice(0, order_step[0]);
        //     remainList = remainList.slice(order_step[0], remainList.length);
        //     order_step = order_step.slice(1, order_step.length);
        //     console.log(idList, order_step);
        // }
        $.ajax({
            url: url + "placeOrder",
            type: "POST",
            dataType: "json",
            timeout: 600000000,
            crossDomain: true,
            xhrFields: {withCredentials: true},
            data: {"id": idList, "isMultiple": isMultiple, email: email},
            success: function (result) {
                console.log(email, "Result", result);
                // if (isMultiple && order_step.length > 0 && remainList.length > 0)
                //     SendOrderAjax(remainList, isMultiple, order_step);
                // else

            },
            error: function (result) {
                console.log(email, "Error", result);
            }
        });
    }

    function updateTrack(isMultiple = false) {  //id:0:single, id:-1:multiple
        let msg = "will you update track numbers?";
        let modal_title = "Update Track";
        GetCheckedList(modal_title, msg, isMultiple, 'track');
    }

    function importPriceList() {
        $('#price_import_modal').modal('show');
    }

    $('#price_import_form').submit(function (e) {
        e.preventDefault();
        if (!window.confirm('will you import this file?'))
            return false;
        setMask(1);
        $(this).ajaxSubmit({
            url: baseURL + 'get/importPriceList',
            type: 'post',
            dataType: 'json',
            success: function (result) {
                if (result.success) {
                    $('#price_import_modal').modal('hide');
                    searchPriceLists();
                } else {
                    window.alert(result.msg);
                    setMask(0);
                }
            },
            error: function (result) {
                setMask(0);
                $('#price_import_modal').modal('hide');
                searchPriceLists();
            },
            resetForm: true
        });
        return false;
    });

    function searchPriceLists() {
        setMask(1);
    }

    function clearPrice() {
        var msg = "will you really clear prices?";
        if (!window.confirm(msg))
            return false;
        setMask(1);
        $.ajax({
            url: baseURL + "get/clearPrice",
            type: "post",
            dataType: "json",
            success: function (result) {
                if (result.success) {
                    searchPriceLists();
                    $('#price_select_all').prop("checked", false);
                } else {
                    window.alert(result.msg);
                }
                setMask(0);
            },
            error: function (result) {
                setMask(0);
            }
        });
    }

    function makeCheck(id) {
        let msg = "will you really update prices?";
        let modal_title = "Price Check";
        GetCheckedList(modal_title, msg, isMultiple, 'price');
        if (!id) {
            var checked_list = $("#price_list tbody input[type=checkbox]:checked");
            if (!checked_list.length) {
                window.alert("Please select items");
                return;
            }
            id = [];
            checked_list.each(function () {
                id.push(this.value);
            });
        }
        if (!$.isArray(id))
            id = [id];

        msg = "";
        if (!window.confirm(msg))
            return false;
        setMask(1);
        $.ajax({
            url: "http://localhost:8111/makeCheck",
            type: "post",
            dataType: "json",
            data: {"id": id},
            success: function (result) {
                if (result.success) {
                    searchPriceLists();
                    $('#price_select_all').prop("checked", false);
                } else {
                    window.alert(result.msg);
                }
                setMask(0);
            },
            error: function (result) {
                searchPriceLists();
                setMask(0);
            }
        });
    }

    function searchPlacedOrders() {

    }

</script>
