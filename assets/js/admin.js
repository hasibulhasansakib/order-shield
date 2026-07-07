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
                        var mapLink = (log.lat && log.lon) 
                            ? '<br><a href="https://www.google.com/maps?q=' + log.lat + ',' + log.lon + '" target="_blank" style="font-size: 11px; color: #3b82f6; text-decoration: none;">View on Map 📍</a>'
                            : '';
                        
                        html += '<tr>';
                        html += '<td>' + new Date(log.created_at).toLocaleString() + '</td>';
                        html += '<td><strong>' + log.ip_address + '</strong><br><small>' + (log.isp || '') + '</small></td>';
                        html += '<td>' + locationStr + mapLink + '</td>';
                        html += '<td>' + (log.phone_number || '-') + '<br><small>' + (log.email_address || '') + '</small></td>';
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
        $('#os-rule-modal').removeClass('show');
    });

    $('#os-rule-modal').on('click', function(e) {
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

    // Initial Load
    loadStats();
    loadLogs();
    loadRules();
    loadSettings();
});
