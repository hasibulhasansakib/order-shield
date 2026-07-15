<div class="os-dashboard-wrap">
    <div class="os-dashboard-header">
        <div class="os-logo">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
            <h2>Order Shield</h2>
        </div>
        <div class="os-header-actions">
            <span>By Hasibul Hasan Sakib</span>
        </div>
    </div>

    <div class="os-dashboard-stats">
        <div class="os-stat-card">
            <div class="os-stat-icon" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            </div>
            <div class="os-stat-info">
                <h4>Today's Attempts</h4>
                <h2 id="os-stat-total">0</h2>
            </div>
        </div>
        <div class="os-stat-card">
            <div class="os-stat-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
            </div>
            <div class="os-stat-info">
                <h4>Successful Orders</h4>
                <h2 id="os-stat-success">0</h2>
            </div>
        </div>
        <div class="os-stat-card">
            <div class="os-stat-icon" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
            </div>
            <div class="os-stat-info">
                <h4>Blocked Frauds</h4>
                <h2 id="os-stat-blocked">0</h2>
            </div>
        </div>
    </div>
    
    <div class="os-dashboard-main">
        <div class="os-dashboard-filters" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; padding: 16px 24px; background: #fff; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid #e2e8f0;">
            <div class="os-filter-group" style="display: flex; align-items: center; gap: 12px;">
                <label style="font-size: 14px; font-weight: 600; color: #475569;">Date Range:</label>
                <select id="os-date-filter" class="os-input" style="min-width: 180px; margin: 0; padding-top: 6px; padding-bottom: 6px;">
                    <option value="today">Today</option>
                    <option value="yesterday">Yesterday</option>
                    <option value="last_7_days">Last 7 Days</option>
                    <option value="this_month">This Month</option>
                    <option value="all_time">All Time</option>
                </select>
            </div>
            
            <div class="os-cleanup-group" style="display: flex; align-items: center; gap: 12px; border-left: 1px solid #e2e8f0; padding-left: 20px;">
                <label style="font-size: 13px; color: #64748b;">Cleanup old logs:</label>
                <div style="display: flex; gap: 8px;">
                    <input type="date" id="os_cleanup_date" class="os-input" style="max-width: 140px; margin: 0; padding-top: 6px; padding-bottom: 6px; font-size: 13px;">
                    <button class="os-btn os-btn-outline" id="os-clear-logs-btn" style="color: #ef4444; border-color: #ef4444; padding: 6px 12px; font-size: 13px;">Clear</button>
                </div>
            </div>
        </div>

        <div class="os-tabs">
            <button class="os-tab-btn active" data-target="tab-activity">Activity Log</button>
            <button class="os-tab-btn" data-target="tab-rules">Rules & Blocklist</button>
            <button class="os-tab-btn" data-target="tab-settings">Settings</button>
        </div>

        <div class="os-panel">
            <!-- TAB: ACTIVITY LOG -->
            <div id="tab-activity" class="os-tab-pane active">
                <div class="os-panel-header">
                    <h3>Recent Activity Log</h3>
                    <button class="os-btn os-btn-outline" id="os-refresh-logs">Refresh</button>
                </div>
                <div class="os-table-wrapper">
                    <table class="os-table">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>IP Address</th>
                                <th>Location</th>
                                <th>Phone / Email</th>
                                <th>Products</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="os-logs-tbody">
                            <tr><td colspan="6" class="os-text-center">Loading logs...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB: RULES & BLOCKLIST -->
            <div id="tab-rules" class="os-tab-pane">
                <div class="os-panel-header">
                    <h3>Active Rules</h3>
                    <button class="os-btn os-btn-primary" id="os-add-rule-btn">+ Add Rule</button>
                </div>
                <div class="os-table-wrapper">
                    <table class="os-table">
                        <thead>
                            <tr>
                                <th>Rule Type</th>
                                <th>Target</th>
                                <th>Value</th>
                                <th>Reason / Note</th>
                                <th>Date Added</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="os-rules-tbody">
                            <tr><td colspan="6" class="os-text-center">Loading rules...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB: SETTINGS -->
            <div id="tab-settings" class="os-tab-pane">
                <div class="os-panel-header">
                    <h3>Plugin Settings</h3>
                    <button class="os-btn os-btn-primary" id="os-save-settings">Save Settings</button>
                </div>
                <div class="os-settings-form">
                    <div class="os-form-group">
                        <label>Max Orders Per Day</label>
                        <input type="number" id="setting_max_orders" class="os-input" min="1" value="3">
                        <small>How many successful orders a user can place in a 24-hour period.</small>
                    </div>
                    <div class="os-form-group">
                        <label>Block Message</label>
                        <textarea id="setting_block_msg" class="os-input" rows="3">You have been blocked from placing orders. Please contact support.</textarea>
                        <small>The exact error message shown to blocked users at checkout.</small>
                    </div>
                    <div class="os-form-group">
                        <label>Limit Exceeded Message</label>
                        <textarea id="setting_limit_msg" class="os-input" rows="3">You have exceeded the maximum number of orders allowed per day.</textarea>
                        <small>The error message shown when a user hits their daily order limit.</small>
                    </div>
                    <div class="os-form-group">
                        <label>Enable Fake Phone Detection</label>
                        <select id="setting_fake_phone" class="os-input">
                            <option value="yes">Yes (Block repeating numbers like 01700000000)</option>
                            <option value="no">No (Allow all numbers)</option>
                        </select>
                        <small>Automatically blocks common fake phone number patterns.</small>
                    </div>
                </div>

                <!-- Developer Card -->
                <div class="os-developer-card" style="margin-top: 40px; padding: 24px; background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0; display: flex; gap: 20px; align-items: center;">
                    <div style="background: #0f172a; color: #fff; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: bold;">
                        HHS
                    </div>
                    <div>
                        <h4 style="margin: 0 0 5px 0; font-size: 16px; color: #0f172a;">Developed by Hasibul Hasan Sakib</h4>
                        <p style="margin: 0 0 10px 0; color: #64748b; font-size: 13px;">Web, WooCommerce & AI Automation Specialist</p>
                        <a href="https://github.com/hasibulhasansakib" target="_blank" class="os-btn os-btn-primary" style="text-decoration: none; display: inline-block;">View GitHub Profile</a>
                    </div>
                </div>

            </div>
        </div>
    </div>
    
    <div style="text-align: center; margin-top: 30px; color: #94a3b8; font-size: 12px; font-weight: 500;">
        Powered by HHS Framework &bull; Order Shield v1.0
    </div>

    <!-- ADD RULE MODAL -->
    <div id="os-rule-modal" class="os-modal">
        <div class="os-modal-content">
            <div class="os-modal-header">
                <h3>Add New Rule</h3>
                <span class="os-modal-close">&times;</span>
            </div>
            <div class="os-modal-body">
                <div class="os-form-group">
                    <label>Rule Type</label>
                    <select id="new_rule_type" class="os-input">
                        <option value="blacklist">Blacklist (Block)</option>
                        <option value="whitelist">Whitelist (Allow)</option>
                    </select>
                </div>
                <div class="os-form-group">
                    <label>Target Type</label>
                    <select id="new_target_type" class="os-input">
                        <option value="ip">IP Address</option>
                        <option value="phone">Phone Number</option>
                        <option value="email">Email Address</option>
                    </select>
                </div>
                <div class="os-form-group">
                    <label>Target Value</label>
                    <input type="text" id="new_target_value" class="os-input" placeholder="e.g. 192.168.1.1 or 01700000000">
                </div>
                <div class="os-form-group">
                    <label>Reason / Note</label>
                    <input type="text" id="new_reason" class="os-input" placeholder="Why are you adding this rule?">
                </div>
                <button class="os-btn os-btn-primary" id="os-submit-rule" style="width: 100%;">Save Rule</button>
            </div>
        </div>
    </div>

    <!-- PRODUCTS MODAL -->
    <div id="os-products-modal" class="os-modal">
        <div class="os-modal-content" style="max-width: 600px;">
            <div class="os-modal-header">
                <h3>Attempted Order Items</h3>
                <span class="os-modal-close">&times;</span>
            </div>
            <div class="os-modal-body" style="padding: 0;">
                <div id="os-products-list" class="os-products-list">
                    <!-- Products will be dynamically loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>
