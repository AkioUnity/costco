<!--    <link href="--><?php //echo base_url('assets/css/bootstrap.min.css') ?><!--" rel="stylesheet" type="text/css" />-->
<!--    <link href="--><?php //echo base_url('assets/css/font-awesome.css') ?><!--" rel="stylesheet" type="text/css" />-->
<!--    <link href="--><?php //echo base_url('assets/css/AdminLTE.css') ?><!--" rel="stylesheet" type="text/css" />-->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/s/bs-3.3.5/jq-2.1.4,dt-1.10.10,r-2.0.0/datatables.min.css"/>
    <link href="<?php echo base_url('assets/css/bootstrap-colorpicker.min.css') ?>" rel="stylesheet" type="text/css" />
    <link href="<?php echo base_url('assets/plugins/datetimepicker/jquery.timepicker.css') ?>" rel="stylesheet" type="text/css" />
    <link href="<?php echo base_url('assets/css/daterangepicker-bs3.css') ?>" rel="stylesheet" type="text/css" />
    <link href="<?php echo base_url('assets/css/components.css') ?>" rel="stylesheet" type="text/css" />
<!--    <link href="--><?php //echo base_url('assets/css/panel.css') ?><!--" rel="stylesheet" type="text/css" />-->
    <style>
        .box-header>.box-tools {
             position:relative;
            top:0px;
            right:0px;
        }
        .loading-img {
            position: absolute;
            top: 0;
            left: 10%;
            width: 100%;
            height: 100%;
            z-index: 1020;
            background: transparent url("<?php echo base_url('assets/images/ajax-loader1.gif') ?>") 50% 50% no-repeat;
        }
        .overlay {
            z-index: 1010;
            background: rgba(255, 255, 255, 0.7);
            position: absolute;
            top: 0;
            left: 10%;
            width: 100%;
            height: 100%;
        }


    </style>
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
    <link rel="shortcut icon" href="<?php echo base_url('favicon.ico')?>"/>
    <script>
        var baseURL = "<?php echo base_url(); ?>";
        let multiple_order="<?php echo $multiple_order; ?>";
    </script>

<div class="right-side" style="margin-left: 0;">
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs">
                        <li class="active"><a data-toggle="tab" href="#new_orders">New Orders</a></li>
                        <li><a data-toggle="tab" href="#placed_orders">Placed Orders</a></li>
                        <li><a data-toggle="tab" href="#price_check">Price Check</a></li>
                    </ul>
                    <div class="tab-content">
                        <div id="new_orders" class="tab-pane fade in active">
                            <div class="box box-primary box-solid">
                                <div class="box-header">
                                    <div class="box-tools form-tool pull-right">
                                        <button class="btn btn-info" onclick="importList();">Import</button>
                                        <button class="btn btn-info" onclick="emptyCart();">EmptyCart</button>
                                        <button class="btn btn-info" onclick="makeOrder(0);">Order</button>
                                        <button class="btn btn-info" onclick="makeOrder(0,true);">Multiple address Order</button>
                                        <button class="btn btn-info" onclick="del(0);">Delete</button>
                                    </div>
                                </div>
                                <div class="box-body table-responsive">
                                    <table id="order_list" class="table table-bordered table-striped" cellspacing="0" width="100%">
                                        <thead>
                                        <tr>
                                            <th><input type="checkbox" id="select_all" /></th>
                                            <th>No</th>
                                            <th>AmazonId</th>
                                            <th>ItemNum</th>
                                            <th>Quantity</th>
                                            <th>Delivery Option</th>
                                            <th>Customer Detail</th>
                                            <th>Order Result</th>
                                            <th>Action</th>
                                        </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div id="placed_orders" class="tab-pane fade">
                            <div class="box box-primary box-solid">
                                <div class="box-header">
                                    <select id="status" class="btn btn-default" style="margin-left: 30px; margin-top: 5px; background: ghostwhite;" onchange="searchPlacedOrders();">
                                        <option value=0 selected>All Status</option>
                                        <option value=1>Order Received</option>
                                        <option value=2>Shipping</option>
                                    </select>
                                    <button class="btn btn-info" id="daterange-btn" style="margin-top: 5px;">
                                        <i class="fa fa-calendar"></i> <span id="period">All Duration</span>
                                        <i class="fa fa-caret-down"></i>
                                    </button>

                                    <button class="btn btn-info" onclick="searchPlacedOrders();" style="margin-top: 5px;">Search</button>
                                    <div class="box-tools form-tool pull-right">
                                        <button class="btn btn-info" onclick="updateTrack(0);">Track</button>
                                        <button class="btn btn-info" onclick="updateTrack(-1);">Multiple Address Track</button>
                                        <button class="btn btn-info" onclick="makeReOrder(0);">Re-Order</button>
                                        <button class="btn btn-info" onclick="delPlaced(0);">Delete</button>
                                        <button class="btn btn-info" onclick="exportList();">Export</button>
                                    </div>
                                </div>
                                <div class="box-body table-responsive">
                                    <table id="placed_order_list" class="table table-bordered table-striped" cellspacing="0" width="100%">
                                        <thead>
                                        <tr>
                                            <th><input type="checkbox" id="placed_select_all" /></th>
                                            <th>No</th>
                                            <th>AmazonId</th>
                                            <th>Product Name</th>
                                            <th>Quantity</th>
                                            <th>Delivery Option</th>
                                            <th>Customer Detail</th>
                                            <th>Order Result</th>
                                            <th>Track Number</th>
                                            <th>Etc</th>
                                            <th>Action</th>
                                        </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div id="price_check" class="tab-pane fade">
                            <div class="box box-primary box-solid">
                                <div class="box-header">
                                    <div class="box-tools form-tool pull-right">
                                        <button class="btn btn-info" onclick="importPriceList();">Import</button>
                                        <button class="btn btn-info" onclick="clearPrice();">Clear</button>
                                        <button class="btn btn-info" onclick="makeCheck(0);">Check</button>
                                        <button class="btn btn-info" onclick="delPrice(0);">Delete</button>
                                        <button class="btn btn-info" onclick="exportPriceList(0);">Export</button>
                                    </div>
                                </div>
                                <div class="box-body table-responsive">
                                    <table id="price_list" class="table table-bordered table-striped" cellspacing="0" width="100%">
                                        <thead>
                                        <tr>
                                            <th><input type="checkbox" id="price_select_all" /></th>
                                            <th>No</th>
                                            <th>ASIN</th>
                                            <th>Title</th>
                                            <th>Costco Number</th>
                                            <th>Regular Price</th>
                                            <th>S_H</th>
                                            <th>Coupon</th>
                                            <th>Final Price</th>
                                            <th>Coupon Start</th>
                                            <th>Coupon End</th>
                                            <th>Out of Stock</th>
                                            <th>Action</th>
                                        </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<div class="overlay" style="display: none;"></div>
<div class="loading-img" style="display: none;"></div>

<div class="modal fade" id="import_modal" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog">
        <form id="import_form" action="#" method="post" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"><span class="modal-email"><?php echo $cur_email; ?></span> Import order list from xlsx file</h4>
                </div>
                <input type="hidden" name="email" value="<?php echo $cur_email; ?>" />
                <div class="modal-body">
                    <div class="form-group">
                        <input type="file" name="file" class="form-control" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" required />
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

<div class="modal fade" id="edit_modal" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog">
        <form id="edit_form" action="#">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Edit customer detail</h4>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="data[id]" value="0" />
                    <div class="form-group">
                        <label>Name*</label><br/>
                        <input type="text" name="data[recipient_name]" class="form-control" autocomplete="off" required />
                    </div>
                    <div class="form-group">
                        <label>Address1*</label><br/>
                        <input type="text" name="data[ship_address_1]" class="form-control" autocomplete="off" required />
                    </div>
                    <div class="form-group">
                        <label>Address2</label><br/>
                        <input type="text" name="data[ship_address_2]" class="form-control" autocomplete="off" />
                    </div>
                    <div class="form-group">
                        <label>City*</label><br/>
                        <input type="text" name="data[ship_city]" class="form-control" autocomplete="off" required />
                    </div>
                    <div class="form-group">
                        <label>State*</label><br/>
                        <input type="text" name="data[ship_state]" class="form-control" autocomplete="off" required />
                    </div>
                    <div class="form-group">
                        <label>PostalCode*</label><br/>
                        <input type="text" name="data[ship_postal_code]" class="form-control" autocomplete="off" required />
                    </div>
                    <div class="form-group">
                        <label>PhoneNumber*</label><br/>
                        <input type="text" name="data[buyer_phone_number]" class="form-control" autocomplete="off" required />
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-default">Save</button>
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
                    <h4 class="modal-title"><span class="modal-email"><?php echo $cur_email; ?></span>  Import item list from xlsx file</h4>
                </div>
                <input type="hidden" name="email" value="<?php echo $cur_email; ?>" />
                <div class="modal-body">
                    <div class="form-group">
                        <input type="file" name="file" class="form-control" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" required />
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

<!--<script src="--><?php //echo base_url('assets/js/jquery-2.1.1.js') ?><!--"></script>-->
<!--<script src="--><?php //echo base_url('assets/js/bootstrap.min.js') ?><!--"></script>-->
<!--<script src="--><?php //echo base_url('assets/js/AdminLTEapp.js') ?><!--"></script>-->
<script type="text/javascript" src="https://cdn.datatables.net/s/bs-3.3.5/jq-2.1.4,dt-1.10.10,r-2.0.0/datatables.min.js"></script>
<script src="<?php echo base_url('assets/js/plugins/jquery.form.js') ?>"></script>
<script src="<?php echo base_url('assets/js/bootstrap-colorpicker.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/plugins/datetimepicker/jquery.timepicker.js') ?>"></script>
<script src="<?php echo base_url('assets/js/plugins/daterangepicker.js') ?>"></script>
<script src="<?php echo base_url('assets/js/plugins/jquery.table2excel.js') ?>"></script>
<script src="<?php echo base_url('assets/js/page.js') ?>"></script>

<script>
    var order_table = 0,
        placed_order_table = 0,
        price_table = 0;
    var startdate = '', enddate = '';
    $("document").ready(function(){
        $('#daterange-btn').daterangepicker(
            {
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract('days', 1), moment().subtract('days', 1)],
                    'Last 7 Days': [moment().subtract('days', 6), moment()],
                    'Last 30 Days': [moment().subtract('days', 29), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1).endOf('month')]
                },
                startDate: moment().subtract('days', 29),
                endDate: moment()
            },
            function(start, end) {
                startdate = start.format('YYYY-MM-DD');
                enddate = end.format('YYYY-MM-DD');
            }
        );
        searchOrders();
        searchPlacedOrders();
        searchPriceLists();
    });

    function importList(){
        $('#import_modal').modal('show');
    }
    $('#import_form').submit(function(e) {
        e.preventDefault();
        if(!window.confirm('will you import this file?'))
            return false;
        setMask(1);
        $(this).ajaxSubmit({
            url: baseURL+'get/importList',
            type: 'post',
            dataType: 'json',
            success:function(result){
                if(result.success){
                    $('#import_modal').modal('hide');
                    searchOrders();
                    searchPlacedOrders();
                }else{
                    window.alert(result.msg);
                    setMask(0);
                }
            },
            error: function(result){
                setMask(0);
                $('#import_modal').modal('hide');
                searchOrders();
                searchPlacedOrders();
            },
            resetForm: true
        });
        return false;
    });

    function searchOrders(){
        setMask(1);
        if(order_table)
            order_table.destroy();
        order_table = $('#order_list').DataTable({
            paging: false,
            ordering: false,
            searching: false,
            ajax:{
                url: baseURL+"get/getOrderList",
                type: 'post',
                dataType: 'json',
                dataSrc: '',
                data:{'email':'<?php echo $cur_email; ?>'}
            },
            columns: [
                {data: 'id'},
                {data: 'no'},
                {data: 'order_id'},
                {data: 'product_number'},
                {data: 'quantity_purchased'},
                {data: 'ship_service'},
                {data: 'customer_detail'},
                {data: 'order_detail'},
                {data: 'action'}
            ],
            initComplete: function() {
                setMask(0);
            },
            'columnDefs': [{
                'targets': 0,
                'searchable':false,
                'orderable':false,
                'className': 'dt-body-center',
                'render': function (data, type, full, meta){
                    return '<input type="checkbox" name="id[]" value="'
                        + $('<div/>').text(data).html() + '">';
                }
            }],
            fnDrawCallback: function( oSettings ) {
                $('[data-toggle="tooltip"]').tooltip();
            }
        });
        $('#select_all').on('click', function(){
            var rows = order_table.rows({ 'search': 'applied' }).nodes();
            $('input[type="checkbox"]', rows).prop('checked', this.checked);
        });
        $('#order_list tbody').on('change', 'input[type="checkbox"]', function(){
            if(!this.checked){
                var el = $('#select_all').get(0);
                if(el && el.checked && ('indeterminate' in el)){
                    el.indeterminate = true;
                }
            }
        });
    }

    function searchPlacedOrders(){
        setMask(1);
        if(placed_order_table)
            placed_order_table.destroy();
        placed_order_table = $('#placed_order_list').DataTable({
            ordering: false,
            ajax:{
                url: baseURL+"get/getPlacedOrderList",
                type: 'post',
                dataType: 'json',
                dataSrc: '',
                data: {'status': $('#status').val(), 'startdate': startdate, 'enddate': enddate,'email':'<?php echo $cur_email; ?>'}
            },
            columns: [
                {data: 'id'},
                {data: 'no'},
                {data: 'order_id'},
                {data: 'product_name'},
                {data: 'quantity_purchased'},
                {data: 'ship_service'},
                {data: 'customer_detail'},
                {data: 'order_detail'},
                {data: 'track_number'},
                {data: 'etc'},
                {data: 'action', width: 120}
            ],
            initComplete: function() {
                if(startdate!=''){
                    if(startdate==enddate){
                        $("#period").html(startdate);
                    }else{
                        $("#period").html(startdate+'~'+enddate);
                    }
                }
                setMask(0);
            },
            aLengthMenu: [[25, 50, -1], [25, 50, "All"]],
            'columnDefs': [{
                'targets': 0,
                'searchable':false,
                'orderable':false,
                'className': 'dt-body-center',
                'render': function (data, type, full, meta){
                    return '<input type="checkbox" name="id[]" value="'
                        + $('<div/>').text(data).html() + '">';
                }
            }],
            fnDrawCallback: function( oSettings ) {
                $('[data-toggle="tooltip"]').tooltip();
            }
        });
        $('#placed_select_all').on('click', function(){
            var rows = placed_order_table.rows({ 'search': 'applied' }).nodes();
            $('input[type="checkbox"]', rows).prop('checked', this.checked);
        });
        $('#placed_order_list tbody').on('change', 'input[type="checkbox"]', function(){
            if(!this.checked){
                var el = $('#placed_select_all').get(0);
                if(el && el.checked && ('indeterminate' in el)){
                    el.indeterminate = true;
                }
            }
        });
    }

    function edit(id){
        setMask(1);
        $.ajax({
            url: baseURL+'get/getOrderData',
            type: 'post',
            dataType: 'json',
            data: {id: id},
            success: function(result){
                for(idx in result){
                    $("#edit_form input[name='data["+idx+"]']").val(result[idx]);
                }
                $("#edit_modal").modal('show');
                setMask(0);
            }
        });
    }
    $("#edit_form").submit(function(e) {
        e.preventDefault();
        if (!e.target.checkValidity()) {
            window.alert('Please fill the form');
            return;
        }
        $.ajax({
            url: baseURL+'get/updateOrder',
            type: 'post',
            dataType: 'json',
            data: $(this).serialize(),
            success: function(result) {
                if(result.success){
                    $("#edit_modal").modal("hide");
                    searchOrders();
                }else{
                    window.alert(result.msg);
                    searchOrders();
                    $("#edit_modal").modal("hide");
                }
            }
        });
    });
    function emptyCart() {
        var msg = "will you really clean current cart?";
        if(!window.confirm(msg))
            return false;
        setMask(1);
        $.ajax({
            url: baseURL + "get/cleanCart",
            type: "post",
            dataType: "json",
            success: function (result) {
                if(result.success) {
                    window.alert('cleaned!');
                }else{
                    window.alert(result.msg);
                }
                setMask(0);
            },
            error: function(result){
                setMask(0);
                window.alert('error');
            }
        });
    }
    // let url="http://localhost:8111/";
    let url="http://localhost:8111/";
    function makeOrder(id,isMultiple=false){
        if(!id){
            var checked_list = $("#order_list tbody input[type=checkbox]:checked");
            if(! checked_list.length){
                window.alert("Please select items");
                return;
            }
            id = [];
            checked_list.each(function(){
                id.push(this.value);
            });
        }
        if(!$.isArray(id))
            id = [id];

        var msg = "will you really place order?";
        let stepList=[];
        if (isMultiple){
            msg = "will you really place Multiple Address  order?";
            stepList=multiple_order.split(',');
        }

        if(!window.confirm(msg))
            return false;
        setMask(1);
        SendOrderAjax(id,isMultiple,stepList);

    }

    function SendOrderAjax(idList,isMultiple,order_step) {
        let remainList=idList;
        if (isMultiple){
            idList=idList.slice(0,order_step[0]);
            remainList=remainList.slice(order_step[0],remainList.length);
            order_step=order_step.slice(1,order_step.length);
            console.log(idList,order_step);
        }
        $.ajax({
            url: url+"placeOrder",
            type: "POST",
            dataType: "json",
            timeout : 600000000,
            crossDomain: true,
            xhrFields: { withCredentials: true },
            data: {"id": idList,"isMultiple":isMultiple,'email':'<?php echo $cur_email; ?>'},
            success: function (result) {
                console.log("Result", result);
                if (isMultiple && order_step.length>0 && remainList.length>0)
                    SendOrderAjax(remainList,isMultiple,order_step);
                else
                    finishedAjax(result);
            },
            error: function(result){
                console.log("Error", result);
                setMask(0);
            }
        });
    }

    function finishedAjax(result) {
        if(result.success){
            searchOrders();
            searchPlacedOrders();
            $('#select_all').prop("checked", false);
        }else{
            window.alert(result.msg);
            searchOrders();
            searchPlacedOrders();
            $('#select_all').prop("checked", false);
        }
        setMask(0);
    }

    function makeReOrder(id){
        if(!id){
            var checked_list = $("#placed_order_list tbody input[type=checkbox]:checked");
            if(! checked_list.length){
                window.alert("Please select items");
                return;
            }
            id = [];
            checked_list.each(function(){
                id.push(this.value);
            });
        }
        if(!$.isArray(id))
            id = [id];

        var msg = "will you really place order?";
        if(!window.confirm(msg))
            return false;
        setMask(1);
        $.ajax({
            url: "http://localhost:8111/placeOrders/" + id,
            type: "post",
            dataType: "json",
            data: {"id": id,'email':'<?php echo $cur_email; ?>'},
            success: function (result) {
                if(result.success){
                    searchOrders();
                    searchPlacedOrders();
                    $('#placed_select_all').prop("checked", false);
                }else{
                    window.alert(result.msg);
                }
                setMask(0);
            },
            error: function(result){
                setMask(0);
            }
        });
    }
    function del(id){
        if(!id){
            var checked_list = $("#order_list tbody input[type=checkbox]:checked");
            if(! checked_list.length){
                window.alert("Please select items");
                return;
            }
            id = [];
            checked_list.each(function(){
                id.push(this.value);
            });
        }
        if(!$.isArray(id))
            id = [id];

        var msg = "will you really delete?";
        if(!window.confirm(msg))
            return false;
        setMask(1);
        $.ajax({
            url: baseURL+"get/del",
            type: "post",
            dataType: "json",
            data: {"id": id},
            success: function(result){
                searchOrders();
                searchPlacedOrders();
                $('#select_all').prop("checked", false);
                setMask(0);
            }
        });
    }
    function delPlaced(id){
        if(!id){
            var checked_list = $("#placed_order_list tbody input[type=checkbox]:checked");
            if(! checked_list.length){
                window.alert("Please select items");
                return;
            }
            id = [];
            checked_list.each(function(){
                id.push(this.value);
            });
        }
        if(!$.isArray(id))
            id = [id];

        var msg = "will you really delete?";
        if(!window.confirm(msg))
            return false;
        setMask(1);
        $.ajax({
            url: baseURL+"get/del",
            type: "post",
            dataType: "json",
            data: {"id": id},
            success: function(result){
                searchOrders();
                searchPlacedOrders();
                $('#placed_select_all').prop("checked", false);
                setMask(0);
            }
        });
    }
    function updateTrack(id){
        if(!window.confirm("will you update track numbers?"))
            return false;
        setMask(1);
        $.ajax({
            url: "http://localhost:8111/updateTrack",
            type: "post",
            dataType: "json",
            data: {"id": id,'email':'<?php echo $cur_email; ?>'},
            success: function (result) {
                if(result.success){
                    searchPlacedOrders();
                }else{
                    window.alert(result.msg);
                }
                setMask(0);
            },
            error: function(result){
                setMask(0);
            }
        });
    }

    function exportList(){
        if(!window.confirm("will you export list?"))
            return false;
        setMask(1);
        $.ajax({
            url: baseURL+'get/exportList',
            type: "post",
            dataType: "json",
            data: {'status': $('#status').val(), 'startdate': startdate, 'enddate': enddate},
            success: function(result){
                setMask(0);
                window.alert("Fil was created in "+result.msg);
                // var dlLink = document.createElement('a');
                // dlLink.download = "order-result";
                // dlLink.target = "_blank";
                // dlLink.href = result.msg;
                // document.body.appendChild(dlLink);
                // dlLink.click();
                // document.body.removeChild(dlLink);
            }
        });
    }

    function importPriceList(){
        $('#price_import_modal').modal('show');
    }
    $('#price_import_form').submit(function(e) {
        e.preventDefault();
        if(!window.confirm('will you import this file?'))
            return false;
        setMask(1);
        $(this).ajaxSubmit({
            url: baseURL+'get/importPriceList',
            type: 'post',
            dataType: 'json',
            success:function(result){
                if(result.success){
                    $('#price_import_modal').modal('hide');
                    searchPriceLists();
                }else{
                    window.alert(result.msg);
                    setMask(0);
                }
            },
            error: function(result){
                setMask(0);
                $('#price_import_modal').modal('hide');
                searchPriceLists();
            },
            resetForm: true
        });
        return false;
    });
    function searchPriceLists(){
        setMask(1);
        if(price_table)
            price_table.destroy();
        price_table = $('#price_list').DataTable({
            ordering: false,
            ajax:{
                url: baseURL+"get/getPriceList",
                type: 'post',
                dataType: 'json',
                dataSrc: '',
                data:{'email':'<?php echo $cur_email; ?>'}
            },
            columns: [
                {data: 'id'},
                {data: 'no'},
                {data: 'asin'},
                {data: 'title'},
                {data: 'costco_number'},
                {data: 'regular_price'},
                {data: 's_h'},
                {data: 'coupon'},
                {data: 'final_price'},
                {data: 'coupon_start'},
                {data: 'coupon_end'},
                {data: 'out_of_stock'},
                {data: 'action'}
            ],
            iDisplayLength: -1,
			aLengthMenu: [[50, 100, -1], [50, 100, "All"]],
            initComplete: function() {
                setMask(0);
            },
            'columnDefs': [{
                'targets': 0,
                'searchable':false,
                'orderable':false,
                'className': 'dt-body-center',
                'render': function (data, type, full, meta){
                    return '<input type="checkbox" name="id[]" value="'
                        + $('<div/>').text(data).html() + '">';
                }
            }],
            fnDrawCallback: function( oSettings ) {
                $('[data-toggle="tooltip"]').tooltip();
            }
        });
        $('#price_select_all').on('click', function(){
            var rows = price_table.rows({ 'search': 'applied' }).nodes();
            $('input[type="checkbox"]', rows).prop('checked', this.checked);
        });
        $('#price_list tbody').on('change', 'input[type="checkbox"]', function(){
            if(!this.checked){
                var el = $('#price_select_all').get(0);
                if(el && el.checked && ('indeterminate' in el)){
                    el.indeterminate = true;
                }
            }
        });
    }
    function clearPrice() {
        var msg = "will you really clear prices?";
        if(!window.confirm(msg))
            return false;
        setMask(1);
        $.ajax({
            url: baseURL + "get/clearPrice",
            type: "post",
            dataType: "json",
            success: function (result) {
                if(result.success){
                    searchPriceLists();
                    $('#price_select_all').prop("checked", false);
                }else{
                    window.alert(result.msg);
                }
                setMask(0);
            },
            error: function(result){
                setMask(0);
            }
        });
    }
    function makeCheck(id){
        if(!id){
            var checked_list = $("#price_list tbody input[type=checkbox]:checked");
            if(! checked_list.length){
                window.alert("Please select items");
                return;
            }
            id = [];
            checked_list.each(function(){
                id.push(this.value);
            });
        }
        if(!$.isArray(id))
            id = [id];

        var msg = "will you really update prices?";
        if(!window.confirm(msg))
            return false;
        setMask(1);
        $.ajax({
            url: "http://localhost:8111/makeCheck",
            type: "post",
            dataType: "json",
            data: {"id": id,'email':'<?php echo $cur_email; ?>'},
            success: function (result) {
                if(result.success){
                    searchPriceLists();
                    $('#price_select_all').prop("checked", false);
                }else{
                    window.alert(result.msg);
                }
                setMask(0);
            },
            error: function(result){
                searchPriceLists();
                setMask(0);
            }
        });
    }
    function delPrice(id){
        if(!id){
            var checked_list = $("#price_list tbody input[type=checkbox]:checked");
            if(! checked_list.length){
                window.alert("Please select items");
                return;
            }
            id = [];
            checked_list.each(function(){
                id.push(this.value);
            });
        }
        if(!$.isArray(id))
            id = [id];

        var msg = "will you really delete?";
        if(!window.confirm(msg))
            return false;
        setMask(1);
        $.ajax({
            url: baseURL+"get/delPrice",
            type: "post",
            dataType: "json",
            data: {"id": id},
            success: function(result){
                searchPriceLists();
                $('#price_select_all').prop("checked", false);
                setMask(0);
            }
        });
    }
    function exportPriceList(){
        if(!window.confirm("will you export list?"))
            return false;
        setMask(1);
        $.ajax({
            url: baseURL+'get/exportPriceList',
            type: "post",
            dataType: "json",
            success: function(result){
                setMask(0);
                var dlLink = document.createElement('a');
                dlLink.download = "price-result";
                dlLink.target = "_blank";
                dlLink.href = result.msg;
                document.body.appendChild(dlLink);
                dlLink.click();
                document.body.removeChild(dlLink);
            }
        });
    }

</script>
