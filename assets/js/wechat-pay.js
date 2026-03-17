/**
 * WeChat Pay Frontend Handler
 */
jQuery(document).ready(function($) {
    var orderId = 0;
    var paymentPolling = null;
    var qrCode = null;

    // Initialize payment buttons
    $('.conf-wechat-pay-btn').on('click', function(e) {
        e.preventDefault();
        orderId = $(this).data('order-id');
        var paymentType = $(this).data('payment-type') || 'auto';
        
        initiateWeChatPay(orderId, paymentType);
    });

    function initiateWeChatPay(orderId, paymentType) {
        var $btn = $('.conf-wechat-pay-btn');
        var $modal = $('#wechat-pay-modal');
        
        $btn.prop('disabled', true).text('Processing...');
        
        $.ajax({
            url: conf_vars.ajax_url,
            method: 'POST',
            data: {
                action: 'conf_wechat_create_order',
                order_id: orderId,
                payment_type: paymentType,
                nonce: conf_vars.nonce
            },
            success: function(response) {
                $btn.prop('disabled', false).text('WeChat Pay');
                
                if (response.success) {
                    if (response.data.payment_type === 'native') {
                        showQRCode(response.data.code_url, orderId);
                    } else if (response.data.payment_type === 'h5') {
                        redirectToH5Payment(response.data.mweb_url, orderId);
                    }
                } else {
                    alert(response.data.message || 'Failed to create payment order');
                }
            },
            error: function() {
                $btn.prop('disabled', false).text('WeChat Pay');
                alert('Network error. Please try again.');
            }
        });
    }

    function showQRCode(codeUrl, orderId) {
        var $modal = $('#wechat-pay-modal');
        var $container = $('#qrcode-container');
        
        // Create modal if not exists
        if (!$modal.length) {
            $('body').append('<div id="wechat-pay-modal" class="conf-modal">' +
                '<div class="conf-modal-content">' +
                '<span class="conf-modal-close">&times;</span>' +
                '<h3>Scan QR Code to Pay</h3>' +
                '<div id="qrcode-container" style="text-align: center;"></div>' +
                '<p style="margin-top: 15px; color: #64748b;">Open WeChat and scan the QR code</p>' +
                '<div id="payment-status" style="margin-top: 15px;"></div>' +
                '</div></div>');
            
            $('.conf-modal-close').on('click', function() {
                closeModal();
            });
        }
        
        // Clear previous QR code
        $container.empty();
        $('#payment-status').html('<p style="color: #64748b;">Waiting for payment...</p>');
        
        // Generate QR code using API
        var qrApiUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' + encodeURIComponent(codeUrl);
        $('<img>').attr('src', qrApiUrl).attr('alt', 'Payment QR Code').appendTo($container);
        
        // Show modal
        $modal.fadeIn();
        
        // Start polling for payment status
        startPaymentPolling(orderId);
    }

    function redirectToH5Payment(mwebUrl, orderId) {
        // Store order ID for callback check
        sessionStorage.setItem('wechat_pay_order_id', orderId);
        
        // Redirect to WeChat payment page
        window.location.href = mwebUrl + '&redirect_url=' + encodeURIComponent(window.location.href + '&wechat_paid=1');
    }

    function startPaymentPolling(orderId) {
        if (paymentPolling) {
            clearInterval(paymentPolling);
        }
        
        var checkCount = 0;
        var maxChecks = 120; // 2 minutes max
        
        paymentPolling = setInterval(function() {
            checkCount++;
            
            $.ajax({
                url: conf_vars.ajax_url,
                method: 'GET',
                data: {
                    action: 'conf_wechat_query_order',
                    order_id: orderId,
                    nonce: conf_vars.nonce
                },
                success: function(response) {
                    if (response.success && response.data.paid) {
                        clearInterval(paymentPolling);
                        $('#payment-status').html('<p style="color: #16a34a; font-weight: bold;">Payment Successful!</p>');
                        setTimeout(function() {
                            closeModal();
                            window.location.href = window.location.href.split('?')[0] + '?action=order&id=' + orderId;
                        }, 1500);
                    }
                },
                error: function() {
                    // Silent fail, continue polling
                }
            });
            
            // Timeout after 2 minutes
            if (checkCount >= maxChecks) {
                clearInterval(paymentPolling);
                $('#payment-status').html('<p style="color: #dc2626;">Payment timeout. Please try again.</p>');
            }
        }, 2000); // Check every 2 seconds
    }

    function closeModal() {
        if (paymentPolling) {
            clearInterval(paymentPolling);
        }
        $('#wechat-pay-modal').fadeOut();
    }

    // Check for return from H5 payment
    if (window.location.search.indexOf('wechat_paid=1') > -1) {
        var paidOrderId = sessionStorage.getItem('wechat_pay_order_id');
        if (paidOrderId) {
            // Verify payment status
            $.ajax({
                url: conf_vars.ajax_url,
                method: 'GET',
                data: {
                    action: 'conf_wechat_query_order',
                    order_id: paidOrderId,
                    nonce: conf_vars.nonce
                },
                success: function(response) {
                    sessionStorage.removeItem('wechat_pay_order_id');
                    // Clean URL
                    window.history.replaceState({}, document.title, window.location.pathname + '?action=order&id=' + paidOrderId);
                    
                    if (response.success && response.data.paid) {
                        alert('Payment successful!');
                    } else {
                        alert('Payment verification failed. Please check your order status.');
                    }
                }
            });
        }
    }

    // Modal CSS
    $('body').append('<style>' +
        '.conf-modal {' +
        '  display: none;' +
        '  position: fixed;' +
        '  z-index: 9999;' +
        '  left: 0;' +
        '  top: 0;' +
        '  width: 100%;' +
        '  height: 100%;' +
        '  background-color: rgba(0,0,0,0.5);' +
        '}' +
        '.conf-modal-content {' +
        '  background-color: #fff;' +
        '  margin: 15% auto;' +
        '  padding: 30px;' +
        '  border-radius: 12px;' +
        '  max-width: 350px;' +
        '  text-align: center;' +
        '  position: relative;' +
        '}' +
        '.conf-modal-close {' +
        '  position: absolute;' +
        '  right: 15px;' +
        '  top: 10px;' +
        '  font-size: 28px;' +
        '  cursor: pointer;' +
        '  color: #999;' +
        '}' +
        '.conf-modal-close:hover {' +
        '  color: #333;' +
        '}' +
        '@media (max-width: 640px) {' +
        '  .conf-modal-content {' +
        '    margin: 30% 15px;' +
        '    padding: 20px;' +
        '  }' +
        '}' +
        '</style>');
});
