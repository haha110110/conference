jQuery(document).ready(function($) {
    let attendeeIndex = 1;

    // Add Attendee
    $('#add-attendee').on('click', function() {
        const index = attendeeIndex;
        const compReq = conf_vars.company_req == '1' ? 'required' : '';
        const compAst = conf_vars.company_req == '1' ? ' *' : '';
        const jobReq = conf_vars.jobtitle_req == '1' ? 'required' : '';
        const jobAst = conf_vars.jobtitle_req == '1' ? ' *' : '';

        const attendeeTemplate = `
            <div class="attendee-card" data-index="${index}">
                <span class="remove-attendee">&times;</span>
                <h3>Attendee ${index + 1}</h3>
                <div class="conf-form-group">
                    <label>Full Name *</label>
                    <input type="text" name="attendees[${index}][name]" class="conf-input" required>
                </div>
                <div class="conf-form-group">
                    <label>Phone Number *</label>
                    <input type="text" name="attendees[${index}][phone]" class="conf-input" required>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="conf-form-group">
                        <label>Company${compAst}</label>
                        <input type="text" name="attendees[${index}][company]" class="conf-input" ${compReq}>
                    </div>
                    <div class="conf-form-group">
                        <label>Job Title${jobAst}</label>
                        <input type="text" name="attendees[${index}][job_title]" class="conf-input" ${jobReq}>
                    </div>
                </div>
            </div>`;
        $('#attendees-list').append(attendeeTemplate);
        attendeeIndex++;
    });

    // Remove Attendee
    $(document).on('click', '.remove-attendee', function() {
        $(this).closest('.attendee-card').remove();
    });

    // Step Transitions
    $('.next-step').on('click', function() {
        const nextStep = $(this).data('next');
        const currentStep = nextStep - 1;

        // Simple validation for Step 1
        if (currentStep === 1) {
            let valid = true;
            $('#step-1 input[required]').each(function() {
                if (!$(this).val()) {
                    $(this).css('border-color', '#dc2626');
                    valid = false;
                } else {
                    $(this).css('border-color', '#d1d5db');
                }
            });
            if (!valid) return;
        }

        // Prepare Review Data (Step 2 -> Step 3)
        if (nextStep === 3) {
            populateReview();
        }

        goToStep(nextStep);
    });

    $('.prev-step').on('click', function() {
        goToStep($(this).data('prev'));
    });

    function goToStep(step) {
        $('.registration-step').hide();
        $(`#step-${step}`).fadeIn();
        
        // Update Stepper
        $('.conf-step').removeClass('active completed');
        $(`.conf-step[data-step="${step}"]`).addClass('active');
        $(`.conf-step`).each(function() {
            if ($(this).data('step') < step) {
                $(this).addClass('completed');
            }
        });
    }

    function populateReview() {
        const $tbody = $('#review-table-body');
        $tbody.empty();

        const attendees = [];
        $('.attendee-card').each(function() {
            const name = $(this).find('input[name*="[name]"]').val();
            if (name) attendees.push(name);
        });

        const ticketName = $('.ticket-card.selected h3').text();
        const ticketPrice = parseFloat($('.ticket-card.selected').data('price'));
        const total = attendees.length * ticketPrice;

        attendees.forEach(name => {
            $tbody.append(`
                <tr>
                    <td>${name} - ${ticketName}</td>
                    <td style="text-align: right;">¥${ticketPrice.toFixed(2)}</td>
                </tr>
            `);
        });

        $('#review-total-price').text(`¥${total.toFixed(2)}`);
    }

    // Payment Method Toggle
    $('input[name="payment_method"]').on('change', function() {
        if ($(this).val() === 'bank') {
            $('#bank-transfer-instructions').slideDown();
        } else {
            $('#bank-transfer-instructions').slideUp();
        }
    });

    // Form Submission
    $('#conf-registration-form').on('submit', function(e) {
        e.preventDefault();
        const $form = $(this);
        const $submitBtn = $('#submit-registration');
        const $msgContainer = $('#registration-message');

        $submitBtn.prop('disabled', true).text('Processing...');
        $msgContainer.html('');

        const formData = new FormData(this);
        formData.append('action', 'conf_submit_registration');
        formData.append('nonce', conf_vars.nonce);

        $.ajax({
            url: conf_vars.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $msgContainer.html('<div style="color: #166534; background: #dcfce7; padding: 15px; border-radius: 8px;">' + response.data.message + '</div>');
                    setTimeout(() => {
                        window.location.href = window.location.href.split('?')[0] + '?action=order&id=' + response.data.order_id;
                    }, 1500);
                } else {
                    $msgContainer.html('<div style="color: #991b1b; background: #fee2e2; padding: 15px; border-radius: 8px;">' + response.data.message + '</div>');
                    $submitBtn.prop('disabled', false).text('Complete Registration');
                }
            },
            error: function() {
                $msgContainer.html('<div style="color: #991b1b; background: #fee2e2; padding: 15px; border-radius: 8px;">Something went wrong.</div>');
                $submitBtn.prop('disabled', false).text('Complete Registration');
            }
        });
    });
});
