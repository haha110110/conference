/**
 * Conference Registration App Logic
 */
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('conf-registration-form');
    if (!form) return;

    let currentStep = 'step-1';
    let attendeeCount = 1;

    // --- Navigation ---
    const showStep = (stepId) => {
        document.querySelectorAll('.step-section').forEach(el => el.classList.remove('active'));
        document.getElementById(stepId).classList.add('active');
        currentStep = stepId;

        // Auto-scroll to top
        window.scrollTo({ top: 0, behavior: 'smooth' });

        if (stepId === 'step-3') {
            updateSummary();
        }
    };

    document.querySelectorAll('.btn-next, .btn-prev').forEach(btn => {
        btn.addEventListener('click', function() {
            // Validation before proceeding
            if (this.classList.contains('btn-next')) {
                const currentSection = document.getElementById(currentStep);
                const inputs = currentSection.querySelectorAll('input[required]');
                let isValid = true;
                inputs.forEach(input => {
                    if (!input.value.trim()) {
                        isValid = false;
                        input.style.borderBottomColor = '#ba1a1a'; // error color
                    } else {
                        input.style.borderBottomColor = ''; // reset
                    }
                });

                if (!isValid) {
                    alert('Please fill out all required fields.');
                    return;
                }
            }

            const target = this.getAttribute('data-target');
            showStep(target);
        });
    });

    // --- Attendee Management ---
    const btnAddAttendee = document.getElementById('btn-add-attendee');
    const attendeesContainer = document.getElementById('attendees-container');
    const template = document.getElementById('attendee-template');

    if (btnAddAttendee && template) {
        btnAddAttendee.addEventListener('click', () => {
            const clone = template.content.cloneNode(true);
            const wrapper = clone.querySelector('.attendee-item');
            
            // Update indexes
            wrapper.innerHTML = wrapper.innerHTML.replace(/{index}/g, attendeeCount);
            
            // Update display number
            wrapper.querySelector('.attendee-number').textContent = attendeeCount + 1;
            
            attendeesContainer.appendChild(clone);
            attendeeCount++;
            
            // Re-attach remove listeners
            attachRemoveListeners();
        });
    }

    const attachRemoveListeners = () => {
        document.querySelectorAll('.btn-remove-attendee').forEach(btn => {
            btn.onclick = function() {
                this.closest('.attendee-item').remove();
                attendeeCount--;
                // Re-index displayed numbers (optional, but good for UX)
                let num = 1;
                document.querySelectorAll('.attendee-number').forEach(badge => {
                    badge.textContent = num++;
                });
                
                // Re-index names to ensure continuous array for PHP
                let idx = 0;
                document.querySelectorAll('.attendee-item').forEach(item => {
                    item.querySelectorAll('input').forEach(input => {
                        input.name = input.name.replace(/attendees\[\d+\]/, `attendees[${idx}]`);
                    });
                    idx++;
                });
            };
        });
    };

    // --- Summary Update ---
    const updateSummary = () => {
        // Count attendees
        const count = document.querySelectorAll('.attendee-item').length;
        
        // Get selected tier
        const selectedTierInput = document.querySelector('input[name="ticket_tier"]:checked');
        const tierName = selectedTierInput ? selectedTierInput.value : 'Standard';
        const tierPrice = selectedTierInput ? parseFloat(selectedTierInput.getAttribute('data-price')) : 0;

        const total = count * tierPrice;

        document.getElementById('summary-tier-name').textContent = tierName;
        document.getElementById('summary-attendee-count').textContent = `${count} Attendee(s)`;
        document.getElementById('summary-total-price').textContent = `¥${total.toFixed(2)}`;
    };

    // --- Payment Methods Toggle ---
    const paymentRadios = document.querySelectorAll('.payment-radio');
    const bankDetailsWrap = document.getElementById('bank-details-wrap');
    
    paymentRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'bank') {
                bankDetailsWrap.classList.remove('hidden');
            } else {
                bankDetailsWrap.classList.add('hidden');
            }
        });
    });

    // File upload preview
    const receiptInput = document.getElementById('bank_receipt_upload');
    if (receiptInput) {
        receiptInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const display = document.getElementById('bank-file-name-display');
                display.textContent = this.files[0].name;
                display.classList.remove('hidden');
            } else {
                const display = document.getElementById('bank-file-name-display');
                display.textContent = '';
                display.classList.add('hidden');
            }
        });
    }

    // --- Form Submission ---
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const btnSubmit = document.getElementById('btn-submit');
        const errorMsg = document.getElementById('form-error-message');
        
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = `<span class="material-symbols-outlined animate-spin">refresh</span> Processing...`;
        errorMsg.classList.add('hidden');

        const formData = new FormData(form);
        formData.append('action', 'conf_submit_registration');
        formData.append('nonce', conf_vars.nonce);

        fetch(conf_vars.ajax_url, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const orderId = data.data.order_id;
                const sixDigitCode = data.data.six_digit_code;
                const paymentMethod = formData.get('payment_method');
                
                if (paymentMethod === 'wechat') {
                    // Trigger WeChat Pay
                    initWeChatPay(orderId, sixDigitCode);
                } else if (paymentMethod === 'bank') {
                    // Show bank transfer details step
                    const totalStr = document.getElementById('summary-total-price').textContent.replace('¥', '');
                    document.getElementById('bank-transfer-amount').textContent = totalStr;
                    document.getElementById('bank-transfer-order-id').textContent = `#SUM24-${orderId}`;
                    
                    // Attach orderId to the submit button
                    const submitReceiptBtn = document.getElementById('btn-submit-receipt');
                    if (submitReceiptBtn) {
                        submitReceiptBtn.dataset.orderId = orderId;
                    }
                    
                    showStep('step-bank-transfer');
                } else {
                    // Show success directly for Onsite
                    renderSuccessPage(orderId, paymentMethod, sixDigitCode);
                }
            } else {
                showError(data.data.message || 'Registration failed.');
            }
        })
        .catch(err => {
            showError('Network error occurred. Please try again.');
        });
    });

    const submitReceiptBtn = document.getElementById('btn-submit-receipt');
    if (submitReceiptBtn) {
        submitReceiptBtn.addEventListener('click', function() {
            const orderId = this.dataset.orderId;
            const fileInput = document.getElementById('bank_receipt_upload');
            const errorMsg = document.getElementById('bank-upload-error-message');
            
            if (!fileInput.files || fileInput.files.length === 0) {
                errorMsg.textContent = 'Please select a file to upload.';
                errorMsg.classList.remove('hidden');
                return;
            }

            this.disabled = true;
            this.innerHTML = `<span class="material-symbols-outlined animate-spin">refresh</span> Uploading...`;
            errorMsg.classList.add('hidden');

            const formData = new FormData();
            formData.append('action', 'conf_upload_bank_receipt');
            formData.append('nonce', conf_vars.nonce);
            formData.append('order_id', orderId);
            formData.append('bank_receipt', fileInput.files[0]);

            fetch(conf_vars.ajax_url, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showStep('step-bank-success');
                } else {
                    this.disabled = false;
                    this.innerHTML = `Submit Receipt 提交凭证`;
                    errorMsg.textContent = data.data.message || 'Upload failed.';
                    errorMsg.classList.remove('hidden');
                }
            })
            .catch(err => {
                this.disabled = false;
                this.innerHTML = `Submit Receipt 提交凭证`;
                errorMsg.textContent = 'Network error occurred. Please try again.';
                errorMsg.classList.remove('hidden');
            });
        });
    }

    const showError = (msg) => {
        const btnSubmit = document.getElementById('btn-submit');
        const errorMsg = document.getElementById('form-error-message');
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = `Confirm & Pay <span class="material-symbols-outlined text-lg">arrow_forward</span>`;
        errorMsg.textContent = msg;
        errorMsg.classList.remove('hidden');
    };

    const initWeChatPay = (orderId, sixDigitCode) => {
        const formData = new FormData();
        formData.append('action', 'conf_wechat_create_order');
        formData.append('nonce', conf_vars.nonce);
        formData.append('order_id', orderId);
        
        // Simple logic for h5 vs native based on user agent
        const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        formData.append('payment_type', isMobile ? 'h5' : 'native');

        fetch(conf_vars.ajax_url, {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (data.data.payment_type === 'h5' && data.data.mweb_url) {
                    // Redirect to WeChat app
                    window.location.href = data.data.mweb_url;
                } else if (data.data.payment_type === 'native' && data.data.code_url) {
                    // We should show a QR code for native. 
                    // For the sake of the UX, let's show an alert and render success, but ideally we show a QR modal here.
                    alert("A PC QR Code has been generated. (Integration required to show QR code visually).");
                    renderSuccessPage(orderId, 'wechat_native', sixDigitCode);
                } else {
                    renderSuccessPage(orderId, 'wechat_success', sixDigitCode); // fallback
                }
            } else {
                showError(data.data.message || 'Failed to initialize WeChat Pay.');
            }
        })
        .catch(err => {
            showError('WeChat Pay network error.');
        });
    };

    const renderSuccessPage = (orderId, paymentMethod, sixDigitCode) => {
        showStep('step-success');
        
        document.getElementById('success-order-id').textContent = `#SUM24-${orderId}`;
        
        const codeEl = document.getElementById('success-six-digit-code');
        if (codeEl && sixDigitCode) {
            codeEl.textContent = sixDigitCode;
        }
        
        // Gather attendee names from inputs to display them vertically
        const attendeeNames = [];
        document.querySelectorAll('input[name^="attendees"][name$="[name]"]').forEach(input => {
            if(input.value.trim() !== '') attendeeNames.push(input.value.trim());
        });

        const listContainer = document.getElementById('success-attendee-list');
        listContainer.innerHTML = '';
        attendeeNames.forEach(name => {
            const li = document.createElement('li');
            li.textContent = name;
            listContainer.appendChild(li);
        });

        const subtitle = document.getElementById('success-subtitle');
        if (paymentMethod === 'bank') {
            subtitle.textContent = "Your receipt is under review. You will receive an email once confirmed.";
        } else if (paymentMethod === 'onsite') {
            subtitle.textContent = "Please complete the payment at the registration desk on the day of the event.";
        } else {
            subtitle.textContent = "Payment successful. Your seats are reserved.";
        }
    };
});
