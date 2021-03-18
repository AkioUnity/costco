function setMask(bool){
    if(bool){
        $(".overlay").css("display", "inline");
        $(".loading-img").css("display", "inline");
    }else{
        $(".overlay").css("display", "none");
        $(".loading-img").css("display", "none");
    }
}