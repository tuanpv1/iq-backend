<?php
/**
 * Created by PhpStorm.
 * User: Thuc
 * Date: 10/16/2015
 * Time: 10:31 AM
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title></title>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script>
        function ok() {
            var $url = "http://dangky.mobifone.com.vn/wap/html/sp/confirm.jsp?sp_id=013&link=1Gy37IhRyKWn4eQcqvfZ06TYVm0ajBPhNzIFAZtQra/VqeFguYAg/QoUklOxFBjNI3a+OtMhUfUNpVJKIecemM09qhU488lY+ZIixNbLvCc4rKO+w5uCSFYZo3p2eP+xibnVuIXz3gFbCsXtCX+RKgtOxd7B57jiE18KSoR63Q4bOBKc5O4CmtcuYw7J/QojvaXNfqfqz9tjxbCGuIW4yUn01bHBjd8h1Z3R0eqr2HkJuVBUduedxyz8UQtFw1xbduMwPtkhiK83xQBInUHS7Q==";
            //window.location.href = "";
            $.ajax({
                url: $url,
                success: function( data ) {
                    var pos = data.indexOf("function ok()");
                    var trim = data.substr(pos, 1000);
                    alert("Found: " + trim);
                }
            });
//            alert("OK");
            return false;
        }
        function refuse() {
            alert("refuse");
            return false;
        }
    </script>
</head>
<body>
<h2>Đăng ký gói AT1 của ALOTV?</h2>
<div class="line-action">
    <div class="text-right btn-boqua">
        <a href="javascript:refuse();" class="boqua"><img src="http://wap.mobifone.com.vn/wap/html/sp/img/4.png"/></a>
    </div>
    <div class="text-left btn-dongy">
        <a href="javascript:ok();" class="dongy"><img src="http://wap.mobifone.com.vn/wap/html/sp/img/dongy.png"/></a>
    </div>
    <iframe
</div>

</body>
</html>
