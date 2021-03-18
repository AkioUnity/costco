(function () {
    var filesUpload = document.getElementById("favicon_file"),
        fileType 	= ["ico"];
    function uploadFile (file) {
        var ext = file.name.substr(file.name.lastIndexOf('.') + 1);
        var check = fileType.indexOf(ext);
        if(check == -1){
            window.alert("Please select ICON File");
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
                    $("#favicon_img").attr("src", baseURL+result.msg);
                    $("input[name='data[favicon]']").val(result.msg);
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
    document.getElementById('favicon_file').addEventListener("change", function () {
        traverseFiles(filesUpload.files);
    }, false);

})();