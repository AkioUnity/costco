<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>

    <title>Costco Ordering</title>

    <link href="<?php echo base_url('assets/css/bootstrap.min.css'); ?>" rel="stylesheet" type="text/css" />
    <link href="<?php echo base_url('assets/css/font-awesome.css'); ?>" rel="stylesheet" type="text/css" />

    <link href="<?php echo base_url('assets/css/animate.css'); ?>" rel="stylesheet" type="text/css" />
    <link href="<?php echo base_url('assets/css/style.css'); ?>" rel="stylesheet" type="text/css" />
    <link href="<?php echo base_url('assets/css/panel.css'); ?>" rel="stylesheet" type="text/css" />

    <link rel="shortcut icon" href="<?php echo base_url('images/favicon.ico')?>"/>
    <script>
        var baseURL = "<?php echo base_url();?>";
    </script>
</head>

<body class="gray-bg">
<div class="login">
    <div class="middle-box text-center loginscreen animated fadeInDown">
        <div>
            <h3>Login to your account</h3>
            <form class="m-t" id="form" action="#" method="post">
                <div class="form-group">
                    <input type="text" name="username" class="form-control" placeholder="Username" required>
                </div>
                <div class="form-group">
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                </div>
                <button type="submit" class="btn btn-primary block full-width m-b">Login</button>
                <a data-toggle='modal' data-target='#recover_modal'>Recover password</a>
            </form>
        </div>
    </div>
</div>
<div class="overlay" style="display: none;"></div>
<div class="loading-img" style="display: none;"></div>

<div class="modal fade" id="recover_modal" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-sm">
        <!-- Modal content-->
        <form id="recover_form" action="#">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Recover your password</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <input type="email" name="email" class="form-control" placeholder="Enter your email" autocomplete="off" required />
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-default">Send mail</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Mainly scripts -->
<script src="<?php echo base_url('assets/js/jquery-2.1.1.js'); ?>"></script>
<script src="<?php echo base_url('assets/js/bootstrap.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/js/page.js'); ?>"></script>

<script>
    $("#form").submit(function(e) {
        e.preventDefault();
        if (!e.target.checkValidity()) {
            window.alert('Please fill the form');
            return;
        }
        setMask(1);
        $.ajax({
            url: baseURL+'get/login',
            type: 'post',
            dataType: 'json',
            data: $("#form").serialize(),
            success: function(result) {
                if (result.success == true){
                    window.location.assign(baseURL+'home');
                }
                else{
                    window.alert("Login Failed\n"+result.msg);
                    setMask(false);
                }
            },
            error: function() {
                window.alert("Login Filed. Please contact to  administrator 1.");
                setMask();
            }
        });
    });

    $("#recover_form").submit(function(e) {
        e.preventDefault();
        var actionurl = baseURL+'get/recover';
        $.ajax({
            url: actionurl,
            type: 'post',
            dataType: 'json',
            data: $(this).serialize(),
            success: function(result) {
                if (result.success == true){
                    window.alert("Password is reset. Please check your email.");
                    $("#recover_modal").modal("hide");
                }
                else{
                    window.alert("Recovering Failed\n"+result.msg);
                }
            }
        });
    });
</script>
</body>
</html>