$(function () {
    $('#access_request_form').validator();

    $('#access_request_form').on('submit', function (e) {

        $('#request_submit').addClass('d-none');

        // if the validator does not prevent form submit
        if (!e.isDefaultPrevented()) {
            var url = "register-form.php";

            // POST values in the background the the script URL
            $.ajax({
                type: "POST",
                url: url,
                data: $(this).serialize(),
                success: function (data)
                {
                    if (data == "Message has been sent") {
                        $('#request_submit').removeClass('d-none');
                        var messageAlert = 'alert-success';
                        var messageText = "Your form successfully submitted. Thank you, we will review your message and get back to you shortly.";
                    } else {
                        $('#request_submit').removeClass('d-none');
                        var messageAlert = 'alert-warn';
                        var messageText = "There was an error while submitting the form. Please try again later"
                    }

                    // let's compose Bootstrap alert box HTML
                    var alertBox = '<div class="alert ' + messageAlert + ' alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>' + messageText + '</div>';

                    // If we have messageAlert and messageText
                    if (messageAlert && messageText) {
                        // inject the alert to .messages div in our form
                        $('#access_request_form').find('.messages').html(alertBox);
                        // empty the form
                        $('#access_request_form')[0].reset();
                    }
                }
            });
            return false;
        }
    })
});