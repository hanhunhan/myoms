//回顶部
function gotop() {
    window.scrollTo(0, 1);
}

function goback() {
    return window.history.go( - 1);
}

function refresh() {
    window.location.reload();
}

function gourl(url) {
    window.location = url;
}

function back_to_oa_app_index()
{
    if (navigator.userAgent.match(/android/i))
    {
        window.house365js.backToHome();
    }
    else if (navigator.userAgent.match(/iPhone|iPad|iPod/i))
    {
        window.location.href = 'house365js:backToHome()';
    }
}

//判断是否为安卓设备
function is_android_device()
{
    //alert(navigator.userAgent);
    var is_android = false;

    if (navigator.userAgent.match(/android/i))
    {
        is_android = true;
    }

    return is_android;
}


//判断是否为IOS设备
function is_ios_device()
{
    var is_ios = false;

    if (navigator.userAgent.match(/iPhone|iPad|iPod/i))
    {
        is_ios = true;
    }

    return is_ios;
}