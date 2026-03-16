jQuery(document).ready(function($) {
    let attendeeIndex = 1;

    $('#add-attendee').on('click', function() {
        const attendeeTemplate = `
            <div class="attendee-row" data-index="${attendeeIndex}">
                <span class="remove-attendee">X</span>
                <h3>Attendee ${attendeeIndex + 1}</h3>
                <p>
                    <label>Name:</label>
                    <input type="text" name="attendees[${attendeeIndex}][name]" required>
                </p>
                <p>
                    <label>Phone:</label>
                    <input type="text" name="attendees[${attendeeIndex}][phone]" required>
                </p>
                <p>
                    <label>Company:</label>
                    <input type="text" name="attendees[${attendeeIndex}][company]">
                </p>
                <p>
                    <label>Job Title:</label>
                    <input type="text" name="attendees[${attendeeIndex}][job_title]">
                </p>
            </div>`;
        $('#attendees-list').append(attendeeTemplate);
        attendeeIndex++;
    });

    $(document).on('click', '.remove-attendee', function() {
        $(this).closest('.attendee-row').remove();
    });

    $('#conf-registration-form').on('submit', function(e) {
        e.preventDefault();
        const $form = $(this);
        const $submitBtn = $('#submit-registration');
        const $msgContainer = $('#registration-message');

        $submitBtn.prop('disabled', true);
        $msgContainer.html('Submitting...');

        $.ajax({
            url: conf_vars.ajax_url,
            type: 'POST',
            data: $form.serialize() + '&action=conf_submit_registration&nonce=' + conf_vars.nonce,
            success: function(response) {
                if (response.success) {
                    $msgContainer.html('<p style="color: green;">' + response.data.message + '</p>');
                    // Handle redirect or next step (e.g. WeChat Pay)
                    if ($('input[name="payment_method"]:checked').val() === 'wechat') {
                        $msgContainer.append('<p>Redirecting to payment...</p>');
                        // window.location.href = response.data.payment_url;
                    }
                } else {
                    $msgContainer.html('<p style="color: red;">' + response.data.message + '</p>');
                    $submitBtn.prop('disabled', false);
                }
            },
            error: function() {
                $msgContainer.html('<p style="color: red;">Something went wrong.</p>');
                $submitBtn.prop('disabled', false);
            }
        });
    });
});
