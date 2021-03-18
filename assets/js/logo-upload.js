(function () {
    var filesUpload = document.getElementById("logo_file"),
        fileType 	= ["jpg", "jpeg", "png", "gif"];
    function uploadFile (file) {
        var ext = file.name.substr(file.name.lastIndexOf('.') + 1);
        var check = fileType.indexOf(ext);
        if(check == -1){
            window.alert("Please select Image File(jpg, png)");
            return false;
        }
        var myFormData = new FormData();
        myFormData.append('file', file);
        $.ajax({
            url: baseURL+'get/uploadMedia',
            type: 'POST',
            processData: false, // important
            contentType: false, // important
            dataType : 'json',
            data: myFormData,
            success: function(result){
                if(result.success){
                    $("#logo_img").attr("src", baseURL+result.msg);
                    $("input[name='data[logo]']").val(result.msg);
                }else{
                    window.alert("Failed to upload file");
                }
            },
            error: function(){

            }
        });
    }
    function traverseFiles (files) {
        if (typeof files !== "undefined") {
            for (var i=0, l=files.length; i<l; i++) {
                uploadFile(files[i]);
            }
        }
    }
    document.getElementById('logo_file').addEventListener("change", function () {
        traverseFiles(filesUpload.files);
    }, false);

})();