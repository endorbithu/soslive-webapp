$(document).ready(function () {
    "use strict";
    $("#submit").click(function () {
        var username = $("#myusername").val(), password = $("#mypassword").val(), csrftoken = $("#csrf-token").val();

        if ((username === "") || (password === "") || (csrftoken === "")) {
			
        } else {
            $.ajax({
                type: "POST",
                url: "/ajax.php?act=loginpost",
                data: "myusername=" + username + "&mypassword=" + password + "&csrf-token=" + csrftoken,
                dataType: 'JSON',
                success: function (html) {
                    //console.log(html.response + ' ' + html.username);
                    if (html.response === 'true') {
                        //location.assign("../index.php");
                       location.reload();
                        return html.username;
                    } else {
                        $("#message").html(html.response);
                    }
                },
                error: function (textStatus, errorThrown) {
                    console.log(textStatus);
                    console.log(errorThrown);
                },
                beforeSend: function () {
                    $("#message").html("<p class='text-center'><img src='/style/ajax-loader.gif'></p>");
                }
            });
        }
        return false;
    });
});
