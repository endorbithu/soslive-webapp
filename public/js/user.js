$(document).ready(function(){

  $("#submit").click(function(){

    var username = $("#newuser").val();
    var uid = typeof $("#uid").val() === 'undefined' ? "" : $("#uid").val();
    var fullname = $("#fullname").val();
    var group = $("#group").val();
    var role = $("#role").val();
    var password = $("#password1").val();
    var password2 = $("#password2").val();
	var verified = ($("#verified").length == 0 || ($("#verified")[0].checked  === true) ? "1" : "0");
		
	
    if((username == "") || (fullname == "")  || (group == "") || (role == "")) {
      $("#message").html("<div class=\"alert alert-danger alert-dismissable\"><button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-hidden=\"true\">&times;</button>Please fill all fields</div>");
    }
    else {
      $.ajax({
        type: "POST",
        url: "userpost.php",
        data: "uid=" + uid +"&verified="+verified+"&newuser="+username+"&fullname="+fullname+"&password1="+password+"&password2="+password2+"&role="+role+"&group="+group,
        success: function(html){

			var text = $(html).text();
			//Pulls hidden div that includes "true" in the success response
			var response = text.substr(text.length - 4);

          if(response == "true"){

			$("#message").html(html);

					$('#submit').hide();
			}
		else {
			$("#message").html(html);
			$('#submit').show();
			}
        },
        beforeSend: function()
        {
          $("#message").html("<p class='text-center'><img src='/style/ajax-loader.gif'></p>")
        }
      });
    }
    return false;
  });
});
