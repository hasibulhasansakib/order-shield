jQuery(document).ready(function($) {

    if ($('.os-dashboard-wrap').length === 0) return;

    // --- TABS LOGIC ---
    $('.os-tab-btn').on('click', function() {
        $('.os-tab-btn').removeClass('active');
        $('.os-tab-pane').removeClass('active');

        $(this).addClass('active');
        $('#' + $(this).data('target')).addClass('active');
    });

    // --- ACTIVITY LOG ---
    function loadStats() {
        $.post(osData.ajaxurl, {
            action: 'os_get_stats',
            nonce: osData.nonce
        }, function(response) {
            if (response.success) {
                $('#os-stat-total').text(response.data.total_today);
                $('#os-stat-success').text(response.data.success_today);
                $('#os-stat-blocked').text(response.data.blocked_today);
            }
        });
    }

    function loadLogs() {
        $('#os-logs-tbody').html('<tr><td colspan="6" class="os-text-center">Loading logs...</td></tr>');
        
        $.post(osData.ajaxurl, {
            action: 'os_get_logs',
            nonce: osData.nonce,
            page: 1
        }, function(response) {
            if (response.success && response.data.logs) {
                var html = '';
                var logs = response.data.logs;
                
                if (logs.length === 0) {
                    html = '<tr><td colspan="6" class="os-text-center">No activity recorded yet.</td></tr>';
                } else {
                    logs.forEach(function(log) {
                        var statusBadge = log.status === 'blocked' 
                            ? '<span class="os-badge os-badge-blocked">Blocked</span>'
                            : '<span class="os-badge os-badge-success">Success</span>';
                            
                        var locationParts = [];
                        if (log.city) locationParts.push(log.city);
                        if (log.region) locationParts.push(log.region);
                        
                        var locationStr = locationParts.length > 0 ? locationParts.join(', ') : 'Unknown';
                        if (log.zip) locationStr += ' - ' + log.zip;
                        if (log.country) locationStr += '<br><small>' + log.country + '</small>';
                        
                        var productsCol = '-';
                        if (log.cart_data) {
                            try {
                                var cartItems = JSON.parse(log.cart_data);
                                if (cartItems && cartItems.length > 0) {
                                    var firstItemName = cartItems[0].name.substring(0, 20) + (cartItems[0].name.length > 20 ? '...' : '');
                                    productsCol = '<div style="display:flex; align-items:center; gap:8px;">' + 
                                                  '<button class="os-btn-icon os-view-products" data-cart=\'' + escape(JSON.stringify(cartItems)) + '\' title="View Products">' +
                                                  '<svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>' +
                                                  '</button>' +
                                                  '<span style="font-size:12px; color:#64748b;">' + firstItemName + (cartItems.length > 1 ? ' (+' + (cartItems.length-1) + ')' : '') + '</span>' +
                                                  '</div>';
                                }
                            } catch (e) {
                                console.error('Failed to parse cart data', e);
                            }
                        }

                        html += '<tr>';
                        html += '<td>' + new Date(log.created_at).toLocaleString() + '</td>';
                        html += '<td><strong>' + log.ip_address + '</strong><br><small>' + (log.isp || '') + '</small></td>';
                        html += '<td>' + locationStr + '</td>';
                        html += '<td>' + (log.phone_number || '-') + '<br><small>' + (log.email_address || '') + '</small></td>';
                        html += '<td>' + productsCol + '</td>';
                        html += '<td>' + statusBadge + '</td>';
                        html += '<td><button class="os-btn os-btn-outline os-block-btn" data-ip="' + log.ip_address + '">Block IP</button></td>';
                        html += '</tr>';
                    });
                }
                $('#os-logs-tbody').html(html);
            }
        });
    }

    // --- RULES & BLOCKLIST ---
    function loadRules() {
        $('#os-rules-tbody').html('<tr><td colspan="6" class="os-text-center">Loading rules...</td></tr>');
        
        $.post(osData.ajaxurl, {
            action: 'os_get_rules',
            nonce: osData.nonce
        }, function(response) {
            if (response.success && response.data.rules) {
                var html = '';
                var rules = response.data.rules;
                
                if (rules.length === 0) {
                    html = '<tr><td colspan="6" class="os-text-center">No rules defined yet.</td></tr>';
                } else {
                    rules.forEach(function(rule) {
                        var badgeClass = rule.rule_type === 'whitelist' ? 'os-badge-success' : 'os-badge-blocked';
                        html += '<tr>';
                        html += '<td><span class="os-badge ' + badgeClass + '">' + rule.rule_type.toUpperCase() + '</span></td>';
                        html += '<td>' + rule.target_type.toUpperCase() + '</td>';
                        html += '<td><strong>' + rule.target_value + '</strong></td>';
                        html += '<td>' + (rule.reason || '-') + '</td>';
                        html += '<td>' + new Date(rule.created_at).toLocaleDateString() + '</td>';
                        html += '<td><button class="os-btn os-btn-outline os-delete-rule" data-id="' + rule.id + '">Remove</button></td>';
                        html += '</tr>';
                    });
                }
                $('#os-rules-tbody').html(html);
            }
        });
    }

    // Add Block Rule from Activity Log
    $(document).on('click', '.os-block-btn', function() {
        var ip = $(this).data('ip');
        if (!confirm('Are you sure you want to permanently block IP: ' + ip + '?')) return;
        
        $.post(osData.ajaxurl, {
            action: 'os_add_rule',
            nonce: osData.nonce,
            rule_type: 'blacklist',
            target_type: 'ip',
            target_value: ip,
            reason: 'Blocked from Activity Log'
        }, function(response) {
            if (response.success) {
                alert('IP Blocked Successfully!');
                loadRules();
            } else {
                alert('Error: ' + response.data);
            }
        });
    });

    // Delete Rule
    $(document).on('click', '.os-delete-rule', function() {
        var id = $(this).data('id');
        if (!confirm('Are you sure you want to remove this rule?')) return;
        
        $.post(osData.ajaxurl, {
            action: 'os_delete_rule',
            nonce: osData.nonce,
            id: id
        }, function(response) {
            if (response.success) {
                loadRules();
            }
        });
    });

    // --- SETTINGS ---
    function loadSettings() {
        $.post(osData.ajaxurl, {
            action: 'os_get_settings',
            nonce: osData.nonce
        }, function(response) {
            if (response.success) {
                $('#setting_max_orders').val(response.data.os_max_orders_per_day || 3);
                $('#setting_block_msg').val(response.data.os_block_msg || 'You have been blocked from placing orders. Please contact support.');
                $('#setting_limit_msg').val(response.data.os_limit_msg || 'You have exceeded the maximum number of orders allowed per day.');
                $('#setting_fake_phone').val(response.data.os_fake_phone || 'yes');
            }
        });
    }

    $('#os-save-settings').on('click', function() {
        var btn = $(this);
        btn.text('Saving...').prop('disabled', true);
        
        $.post(osData.ajaxurl, {
            action: 'os_save_settings',
            nonce: osData.nonce,
            os_max_orders_per_day: $('#setting_max_orders').val(),
            os_block_msg: $('#setting_block_msg').val(),
            os_limit_msg: $('#setting_limit_msg').val(),
            os_fake_phone: $('#setting_fake_phone').val()
        }, function(response) {
            btn.text('Save Settings').prop('disabled', false);
            if (response.success) {
                alert('Settings Saved!');
            }
        });
    });

    // --- MODAL LOGIC ---
    $('#os-add-rule-btn').on('click', function() {
        $('#new_target_value').val('');
        $('#new_reason').val('');
        $('#os-rule-modal').addClass('show');
    });

    $('.os-modal-close').on('click', function() {
        $(this).closest('.os-modal').removeClass('show');
    });

    $('.os-modal').on('click', function(e) {
        if ($(e.target).hasClass('os-modal')) {
            $(this).removeClass('show');
        }
    });

    $('#os-submit-rule').on('click', function() {
        var btn = $(this);
        var rule_type = $('#new_rule_type').val();
        var target_type = $('#new_target_type').val();
        var target_value = $('#new_target_value').val().trim();
        var reason = $('#new_reason').val().trim();

        if (!target_value) {
            alert('Please enter a target value (IP, Phone, or Email).');
            return;
        }

        btn.text('Saving...').prop('disabled', true);

        $.post(osData.ajaxurl, {
            action: 'os_add_rule',
            nonce: osData.nonce,
            rule_type: rule_type,
            target_type: target_type,
            target_value: target_value,
            reason: reason
        }, function(response) {
            btn.text('Save Rule').prop('disabled', false);
            if (response.success) {
                $('#os-rule-modal').removeClass('show');
                loadRules(); // Refresh table
            } else {
                alert('Error saving rule.');
            }
        });
    });

    $('#os-refresh-logs').on('click', function() {
        loadStats();
        loadLogs();
    });

    // Clear Old Logs
    $('#os-clear-logs-btn').on('click', function() {
        var dateVal = $('#os_cleanup_date').val();
        if (!dateVal) {
            alert('Please select a date first.');
            return;
        }

        if (!confirm('Are you sure you want to permanently delete all logs older than ' + dateVal + '? This cannot be undone.')) {
            return;
        }

        var btn = $(this);
        var originalText = btn.text();
        btn.text('Deleting...').prop('disabled', true);

        $.post(osData.ajaxurl, {
            action: 'os_clear_logs',
            nonce: osData.nonce,
            date: dateVal
        }, function(response) {
            btn.text(originalText).prop('disabled', false);
            if (response.success) {
                alert('Successfully deleted ' + response.data.deleted + ' old log entries.');
                loadStats();
                loadLogs();
            } else {
                alert('Error: ' + response.data);
            }
        });
    });

    // View Products Modal
    $(document).on('click', '.os-view-products', function() {
        var cartDataStr = unescape($(this).attr('data-cart'));
        try {
            var cartItems = JSON.parse(cartDataStr);
            var html = '';
            
            cartItems.forEach(function(item) {
                var imgSrc = item.image ? item.image : 'https://via.placeholder.com/60?text=No+Image';
                html += '<div class="os-product-item">';
                html += '<div class="os-product-img-wrapper"><img src="' + imgSrc + '" alt="Product Image"></div>';
                html += '<div class="os-product-details">';
                if (item.permalink) {
                    html += '<a href="' + item.permalink + '" target="_blank" class="os-product-name">' + item.name + '</a>';
                } else {
                    html += '<span class="os-product-name">' + item.name + '</span>';
                }
                html += '<div class="os-product-meta">';
                html += '<span class="os-product-price">' + item.price + '</span>';
                html += '<span class="os-product-qty">Qty: ' + item.qty + '</span>';
                html += '</div></div></div>';
            });
            
            $('#os-products-list').html(html);
            $('#os-products-modal').addClass('show');
        } catch (e) {
            alert('Failed to load product details.');
        }
    });

    // Initial Load
    loadStats();
    loadLogs();
    loadRules();
    loadSettings();
});
