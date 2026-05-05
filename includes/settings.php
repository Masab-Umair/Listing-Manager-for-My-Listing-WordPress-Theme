<?php
if (!defined('ABSPATH')) exit;

class MLF_Settings {

    public function __construct(){
        add_action('admin_menu', [$this,'menu']);
        add_action('admin_init', [$this,'register']);
        add_action('wp_ajax_mlf_send_test_email', [$this,'send_test_email']);
        add_action('wp_ajax_mlf_get_listing_emails', [$this,'get_listing_emails']);
    }

    // ── Send Test Email ────────────────────────────────────────────────────────
    public function send_test_email(){
        if(!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }
        check_ajax_referer('mlf_test_email_nonce', 'nonce');

        $to = sanitize_email($_POST['email']);
        if(!is_email($to)) {
            wp_send_json_error('Invalid email address');
        }

        $subject = '[Test] Listing Manager Pro — Test Email';
        $body  = '<div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;">';
        $body .= '<h2 style="color:#95160c;">&#9989; Test Email</h2>';
        $body .= '<p>This is a test email sent from <strong>Listing Manager Pro</strong> on <strong>' . get_bloginfo('name') . '</strong>.</p>';
        $body .= '<p>If you received this, your email settings are working correctly.</p>';
        $body .= '<hr style="border:none;border-top:1px solid #eee;margin:20px 0;">';
        $body .= '<p style="color:#999;font-size:12px;">Sent: ' . date('F j, Y g:i A') . '</p>';
        $body .= '</div>';

        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_bloginfo('admin_email') . '>'
        ];

        $sent = wp_mail($to, $subject, $body, $headers);

        if($sent) {
            wp_send_json_success('Test email sent successfully to ' . esc_html($to));
        } else {
            wp_send_json_error('wp_mail() returned false. Check your server mail configuration.');
        }
    }

    // ── Get Listing Emails ─────────────────────────────────────────────────────
    public function get_listing_emails(){
        if(!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }
        check_ajax_referer('mlf_listing_emails_nonce', 'nonce');

        $listings = get_posts([
            'post_type'   => 'job_listing',
            'numberposts' => -1,
            'post_status' => ['publish', 'pending', 'draft']
        ]);

        $emails = [];
        foreach($listings as $listing) {
            $email = get_post_meta($listing->ID, 'email', true);
            if($email && is_email($email) && !in_array($email, $emails)) {
                $emails[] = [
                    'email' => $email,
                    'label' => esc_html($listing->post_title) . ' (' . esc_html($email) . ')'
                ];
            }
        }

        wp_send_json_success($emails);
    }

    public function menu(){
        add_menu_page('Listing Manager','Listing Manager','manage_options','mlf-settings',[$this,'page'],'dashicons-list-view',30);
    }

    public function register(){
        // Email Settings
        register_setting('mlf_group','mlf_email_admin');
        register_setting('mlf_group','mlf_email_approved');
        register_setting('mlf_group','mlf_email_approved_subject');
        register_setting('mlf_group','mlf_email_rejected');
        register_setting('mlf_group','mlf_email_rejected_subject');
        
        // Color Settings
        register_setting('mlf_group','mlf_primary_color');
        register_setting('mlf_group','mlf_heading_color');
        register_setting('mlf_group','mlf_subheading_color');
        
        // Font Settings
        register_setting('mlf_group','mlf_font_family');
        register_setting('mlf_group','mlf_heading_font_size');
        register_setting('mlf_group','mlf_body_font_size');
        
        // Button Settings
        register_setting('mlf_group','mlf_button_bg_color');
        register_setting('mlf_group','mlf_button_text_color');
        register_setting('mlf_group','mlf_button_border_radius');
        register_setting('mlf_group','mlf_button_padding');
        
        // Card Settings
        register_setting('mlf_group','mlf_card_border_radius');
        register_setting('mlf_group','mlf_card_shadow');
        register_setting('mlf_group','mlf_card_padding');
        register_setting('mlf_group','mlf_card_bg_color');
        register_setting('mlf_group','mlf_card_border_color');
        
        // Avatar Settings
        register_setting('mlf_group','mlf_avatar_size');
        register_setting('mlf_group','mlf_avatar_font_size');
        register_setting('mlf_group','mlf_avatar_bg_color');
        register_setting('mlf_group','mlf_avatar_text_color');
        register_setting('mlf_group','mlf_avatar_margin_bottom');
        
        // Container Settings
        register_setting('mlf_group','mlf_container_width');
        register_setting('mlf_group','mlf_container_bg_color');
        register_setting('mlf_group','mlf_container_padding');
        
        // Modal Settings
        register_setting('mlf_group','mlf_modal_bg_color');
        register_setting('mlf_group','mlf_modal_overlay');
        register_setting('mlf_group','mlf_modal_width');
        
        // Spacing Settings
        register_setting('mlf_group','mlf_card_gap');
        register_setting('mlf_group','mlf_section_gap');
        
        // Advanced Settings
        register_setting('mlf_group','mlf_custom_css');
        register_setting('mlf_group','mlf_enable_animations');
    }

    public function page(){
        ?>
        <div class="wrap">
        <h1>Listing Manager Settings</h1>
        
        <style>
        .mlf-settings-wrap {
            max-width: 900px;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .mlf-settings-section {
            margin-bottom: 40px;
            padding-bottom: 30px;
            border-bottom: 1px solid #eee;
        }
        .mlf-settings-section:last-child {
            border-bottom: none;
        }
        .mlf-settings-section h2 {
            color: #95160c;
            font-size: 20px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #95160c;
            display: inline-block;
        }
        .mlf-form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        .mlf-form-group {
            margin-bottom: 15px;
        }
        .mlf-form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
        }
        .mlf-form-group input[type="text"],
        .mlf-form-group input[type="number"],
        .mlf-form-group input[type="color"],
        .mlf-form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        .mlf-form-group input[type="color"] {
            height: 45px;
            padding: 5px;
            cursor: pointer;
        }
        .mlf-form-group small {
            display: block;
            color: #666;
            margin-top: 5px;
            font-size: 12px;
        }
        .mlf-color-preview {
            display: inline-block;
            width: 30px;
            height: 30px;
            border-radius: 5px;
            vertical-align: middle;
            margin-left: 10px;
            border: 1px solid #ddd;
        }
        </style>
        
        <form method="post" action="options.php" class="mlf-settings-wrap">
        <?php settings_fields('mlf_group'); ?>

        <!-- Email Settings -->
        <div class="mlf-settings-section">
        <h2>📧 Email Settings</h2>
        
        <?php
        // Load listing emails for the dropdown
        $mlf_listing_posts = get_posts([
            'post_type'   => 'job_listing',
            'numberposts' => -1,
            'post_status' => ['publish', 'pending', 'draft']
        ]);
        $mlf_listing_emails = [];
        foreach($mlf_listing_posts as $mlp) {
            $mlp_email = get_post_meta($mlp->ID, 'email', true);
            if($mlp_email && is_email($mlp_email) && !in_array($mlp_email, array_column($mlf_listing_emails, 'email'))) {
                $mlf_listing_emails[] = ['email' => $mlp_email, 'label' => $mlp->post_title . ' (' . $mlp_email . ')'];
            }
        }
        ?>
        
        <div class="mlf-form-row">
        <div class="mlf-form-group">
            <label>Admin Notification Email</label>
            <input type="email" name="mlf_email_admin" id="mlf_email_admin" value="<?php echo esc_attr(get_option('mlf_email_admin','esther@myndmyself.com')); ?>" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:5px;font-size:14px;">
            <small>Email address to receive new listing notifications</small>
        </div>
        <?php if(!empty($mlf_listing_emails)): ?>
        <div class="mlf-form-group">
            <label>Or Select from Existing Listings</label>
            <select id="mlf_email_admin_picker" onchange="document.getElementById('mlf_email_admin').value = this.value; this.value = '';" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:5px;font-size:14px;cursor:pointer;">
                <option value="">— Pick an email from your listings —</option>
                <?php foreach($mlf_listing_emails as $mlp_item): ?>
                <option value="<?php echo esc_attr($mlp_item['email']); ?>"><?php echo esc_html($mlp_item['label']); ?></option>
                <?php endforeach; ?>
            </select>
            <small>Choose a practitioner's email to use as the admin notification address</small>
        </div>
        <?php else: ?>
        <div class="mlf-form-group">
            <label>Select from Listings</label>
            <p style="color:#999;font-size:13px;padding:10px;background:#f9f9f9;border-radius:5px;">No listing emails found. Add listings with email addresses to use this feature.</p>
        </div>
        <?php endif; ?>
        </div>
        
        <div class="mlf-form-group" style="margin-bottom:25px;padding:20px;background:#f9f9f9;border-radius:8px;border:1px solid #e8e8e8;">
            <label style="font-size:15px;">🧪 Send Test Email</label>
            <p style="color:#666;font-size:13px;margin:5px 0 12px;">Send a test email to verify your mail configuration is working correctly.</p>
            <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                <input type="email" id="mlf_test_email_addr" placeholder="Enter email to test (leave blank to use admin email above)" style="flex:1;min-width:250px;padding:10px;border:1px solid #ddd;border-radius:5px;font-size:14px;">
                <button type="button" id="mlf_send_test_email" onclick="mlfSendTestEmail()" style="background:#95160c;color:#fff;border:none;padding:10px 20px;border-radius:5px;cursor:pointer;font-size:14px;font-weight:600;white-space:nowrap;">📨 Send Test Email</button>
            </div>
            <div id="mlf_test_email_result" style="margin-top:10px;display:none;"></div>
        </div>
        
        <div class="mlf-form-group">
            <label>Approved Email Subject</label>
            <input type="text" name="mlf_email_approved_subject" value="<?php echo esc_attr(get_option('mlf_email_approved_subject','Listing Approved')); ?>" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:5px;font-size:14px;">
            <small>Available placeholder: <code>{{listing_title}}</code></small>
        </div>

        <div class="mlf-form-group">
            <label>Approved Email Template (HTML)</label>
            <?php wp_editor(get_option('mlf_email_approved','<h2>Approved</h2><p>Your listing "{{listing_title}}" has been approved!</p>'),'mlf_email_approved',['textarea_name'=>'mlf_email_approved','textarea_rows'=>5]); ?>
        </div>

        <div class="mlf-form-group">
            <label>Rejected Email Subject</label>
            <input type="text" name="mlf_email_rejected_subject" value="<?php echo esc_attr(get_option('mlf_email_rejected_subject','Listing Rejected')); ?>" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:5px;font-size:14px;">
            <small>Available placeholder: <code>{{listing_title}}</code></small>
        </div>

        <div class="mlf-form-group">
            <label>Rejected Email Template (HTML)</label>
            <?php wp_editor(get_option('mlf_email_rejected','<h2>Rejected</h2><p>Your listing "{{listing_title}}" has been rejected.</p>'),'mlf_email_rejected',['textarea_name'=>'mlf_email_rejected','textarea_rows'=>5]); ?>
        </div>
        </div>

        <!-- Color Settings -->
        <div class="mlf-settings-section">
        <h2>🎨 Color Settings</h2>
        
        <div class="mlf-form-row">
        <div class="mlf-form-group">
            <label>Primary Color</label>
            <div style="display:flex;align-items:center;gap:10px;">
            <input type="color" name="mlf_primary_color" value="<?php echo esc_attr(get_option('mlf_primary_color','#95160c')); ?>">
            <span class="mlf-color-preview" style="background:<?php echo get_option('mlf_primary_color','#95160c'); ?>"></span>
            </div>
            <small>Main brand color for buttons, links, accents</small>
        </div>
        
        <div class="mlf-form-group">
            <label>Heading Color</label>
            <div style="display:flex;align-items:center;gap:10px;">
            <input type="color" name="mlf_heading_color" value="<?php echo esc_attr(get_option('mlf_heading_color','#333333')); ?>">
            <span class="mlf-color-preview" style="background:<?php echo get_option('mlf_heading_color','#333333'); ?>"></span>
            </div>
            <small>Color for headings and titles</small>
        </div>
        
        <div class="mlf-form-group">
            <label>Subheading Color</label>
            <div style="display:flex;align-items:center;gap:10px;">
            <input type="color" name="mlf_subheading_color" value="<?php echo esc_attr(get_option('mlf_subheading_color','#666666')); ?>">
            <span class="mlf-color-preview" style="background:<?php echo get_option('mlf_subheading_color','#666666'); ?>"></span>
            </div>
            <small>Color for secondary text</small>
        </div>
        </div>
        </div>

        <!-- Font Settings -->
        <div class="mlf-settings-section">
        <h2>🔤 Font Settings</h2>
        
        <div class="mlf-form-row">
        <div class="mlf-form-group">
            <label>Font Family</label>
            <select name="mlf_font_family">
            <option value="Poppins, sans-serif" <?php selected(get_option('mlf_font_family'),'Poppins, sans-serif'); ?>>Poppins</option>
            <option value="Roboto, sans-serif" <?php selected(get_option('mlf_font_family'),'Roboto, sans-serif'); ?>>Roboto</option>
            <option value="Open Sans, sans-serif" <?php selected(get_option('mlf_font_family'),'Open Sans, sans-serif'); ?>>Open Sans</option>
            <option value="Lato, sans-serif" <?php selected(get_option('mlf_font_family'),'Lato, sans-serif'); ?>>Lato</option>
            <option value="Montserrat, sans-serif" <?php selected(get_option('mlf_font_family'),'Montserrat, sans-serif'); ?>>Montserrat</option>
            <option value="Raleway, sans-serif" <?php selected(get_option('mlf_font_family'),'Raleway, sans-serif'); ?>>Raleway</option>
            <option value="Nunito, sans-serif" <?php selected(get_option('mlf_font_family'),'Nunito, sans-serif'); ?>>Nunito</option>
            <option value="system-ui, sans-serif" <?php selected(get_option('mlf_font_family'),'system-ui, sans-serif'); ?>>System Default</option>
            </select>
            <small>Select font for the dashboard</small>
        </div>
        
        <div class="mlf-form-group">
            <label>Heading Font Size (px)</label>
            <input type="number" name="mlf_heading_font_size" value="<?php echo esc_attr(get_option('mlf_heading_font_size','28')); ?>" min="14" max="48">
            <small>Main heading size in pixels</small>
        </div>
        
        <div class="mlf-form-group">
            <label>Body Font Size (px)</label>
            <input type="number" name="mlf_body_font_size" value="<?php echo esc_attr(get_option('mlf_body_font_size','14')); ?>" min="10" max="24">
            <small>Body text size in pixels</small>
        </div>
        </div>
        </div>

        <!-- Button Settings -->
        <div class="mlf-settings-section">
        <h2>🔘 Button Settings</h2>
        
        <div class="mlf-form-row">
        <div class="mlf-form-group">
            <label>Button Background Color</label>
            <div style="display:flex;align-items:center;gap:10px;">
            <input type="color" name="mlf_button_bg_color" value="<?php echo esc_attr(get_option('mlf_button_bg_color','#95160c')); ?>">
            <span class="mlf-color-preview" style="background:<?php echo get_option('mlf_button_bg_color','#95160c'); ?>"></span>
            </div>
            <small>Background color for buttons</small>
        </div>
        
        <div class="mlf-form-group">
            <label>Button Text Color</label>
            <div style="display:flex;align-items:center;gap:10px;">
            <input type="color" name="mlf_button_text_color" value="<?php echo esc_attr(get_option('mlf_button_text_color','#ffffff')); ?>">
            <span class="mlf-color-preview" style="background:<?php echo get_option('mlf_button_text_color','#ffffff'); ?>"></span>
            </div>
            <small>Text color for buttons</small>
        </div>
        
        <div class="mlf-form-group">
            <label>Button Border Radius (px)</label>
            <input type="number" name="mlf_button_border_radius" value="<?php echo esc_attr(get_option('mlf_button_border_radius','8')); ?>" min="0" max="30">
            <small>Border radius for buttons</small>
        </div>
        
        <div class="mlf-form-group">
            <label>Button Padding (px)</label>
            <input type="text" name="mlf_button_padding" value="<?php echo esc_attr(get_option('mlf_button_padding','10px 20px')); ?>">
            <small>Padding format: "top right bottom left" or "vertical horizontal"</small>
        </div>
        </div>
        </div>

        <!-- Card Settings -->
        <div class="mlf-settings-section">
        <h2>📇 Card Settings</h2>
        
        <div class="mlf-form-row">
        <div class="mlf-form-group">
            <label>Card Border Radius (px)</label>
            <input type="number" name="mlf_card_border_radius" value="<?php echo esc_attr(get_option('mlf_card_border_radius','12')); ?>" min="0" max="30">
            <small>Border radius for user cards</small>
        </div>
        
        <div class="mlf-form-group">
            <label>Card Shadow</label>
            <select name="mlf_card_shadow">
            <option value="0 2px 10px rgba(0,0,0,0.08)" <?php selected(get_option('mlf_card_shadow'),'0 2px 10px rgba(0,0,0,0.08)'); ?>>Light</option>
            <option value="0 4px 20px rgba(0,0,0,0.12)" <?php selected(get_option('mlf_card_shadow'),'0 4px 20px rgba(0,0,0,0.12)'); ?>>Medium</option>
            <option value="0 8px 30px rgba(0,0,0,0.15)" <?php selected(get_option('mlf_card_shadow'),'0 8px 30px rgba(0,0,0,0.15)'); ?>>Heavy</option>
            <option value="none" <?php selected(get_option('mlf_card_shadow'),'none'); ?>>None</option>
            </select>
            <small>Shadow effect for cards</small>
        </div>
        
        <div class="mlf-form-group">
            <label>Card Padding (px)</label>
            <input type="text" name="mlf_card_padding" value="<?php echo esc_attr(get_option('mlf_card_padding','20px')); ?>">
            <small>Padding inside cards</small>
        </div>
        
        <div class="mlf-form-group">
            <label>Card Background Color</label>
            <div style="display:flex;align-items:center;gap:10px;">
            <input type="color" name="mlf_card_bg_color" value="<?php echo esc_attr(get_option('mlf_card_bg_color','#ffffff')); ?>">
            <span class="mlf-color-preview" style="background:<?php echo get_option('mlf_card_bg_color','#ffffff'); ?>"></span>
            </div>
            <small>Background color for cards</small>
        </div>
        
        <div class="mlf-form-group">
            <label>Card Border Color</label>
            <div style="display:flex;align-items:center;gap:10px;">
            <input type="color" name="mlf_card_border_color" value="<?php echo esc_attr(get_option('mlf_card_border_color','#e8e8e8')); ?>">
            <span class="mlf-color-preview" style="background:<?php echo get_option('mlf_card_border_color','#e8e8e8'); ?>"></span>
            </div>
            <small>Border color for cards</small>
        </div>
        </div>
        </div>

        <!-- Avatar Settings -->
        <div class="mlf-settings-section">
        <h2>👤 Avatar Settings</h2>
        
        <div class="mlf-form-row">
        <div class="mlf-form-group">
            <label>Avatar Size (px)</label>
            <input type="number" name="mlf_avatar_size" value="<?php echo esc_attr(get_option('mlf_avatar_size','60')); ?>" min="30" max="120">
            <small>Size of the avatar circle in pixels</small>
        </div>
        
        <div class="mlf-form-group">
            <label>Avatar Font Size (px)</label>
            <input type="number" name="mlf_avatar_font_size" value="<?php echo esc_attr(get_option('mlf_avatar_font_size','24')); ?>" min="12" max="48">
            <small>Font size for the initial letter</small>
        </div>
        
        <div class="mlf-form-group">
            <label>Avatar Margin Bottom (px)</label>
            <input type="number" name="mlf_avatar_margin_bottom" value="<?php echo esc_attr(get_option('mlf_avatar_margin_bottom','15')); ?>" min="0" max="40">
            <small>Space below the avatar</small>
        </div>
        
        <div class="mlf-form-group">
            <label>Avatar Background Color</label>
            <div style="display:flex;align-items:center;gap:10px;">
            <input type="color" name="mlf_avatar_bg_color" value="<?php echo esc_attr(get_option('mlf_avatar_bg_color','#95160c')); ?>">
            <span class="mlf-color-preview" style="background:<?php echo get_option('mlf_avatar_bg_color','#95160c'); ?>"></span>
            </div>
            <small>Background color for avatar</small>
        </div>
        
        <div class="mlf-form-group">
            <label>Avatar Text Color</label>
            <div style="display:flex;align-items:center;gap:10px;">
            <input type="color" name="mlf_avatar_text_color" value="<?php echo esc_attr(get_option('mlf_avatar_text_color','#ffffff')); ?>">
            <span class="mlf-color-preview" style="background:<?php echo get_option('mlf_avatar_text_color','#ffffff'); ?>"></span>
            </div>
            <small>Text color for avatar initial</small>
        </div>
        </div>
        </div>

        <!-- Container Settings -->
        <div class="mlf-settings-section">
        <h2>📦 Container Settings</h2>
        
        <div class="mlf-form-row">
        <div class="mlf-form-group">
            <label>Container Width (px)</label>
            <input type="number" name="mlf_container_width" value="<?php echo esc_attr(get_option('mlf_container_width','1400')); ?>" min="800" max="2000">
            <small>Maximum width of the main container</small>
        </div>
        
        <div class="mlf-form-group">
            <label>Container Background Color</label>
            <div style="display:flex;align-items:center;gap:10px;">
            <input type="color" name="mlf_container_bg_color" value="<?php echo esc_attr(get_option('mlf_container_bg_color','#f5f5f5')); ?>">
            <span class="mlf-color-preview" style="background:<?php echo get_option('mlf_container_bg_color','#f5f5f5'); ?>"></span>
            </div>
            <small>Background color for the container</small>
        </div>
        
        <div class="mlf-form-group">
            <label>Container Padding (px)</label>
            <input type="text" name="mlf_container_padding" value="<?php echo esc_attr(get_option('mlf_container_padding','20px')); ?>">
            <small>Padding around the container</small>
        </div>
        </div>
        </div>

        <!-- Modal Settings -->
        <div class="mlf-settings-section">
        <h2>🪟 Modal Settings</h2>
        
        <div class="mlf-form-row">
        <div class="mlf-form-group">
            <label>Modal Background Color</label>
            <div style="display:flex;align-items:center;gap:10px;">
            <input type="color" name="mlf_modal_bg_color" value="<?php echo esc_attr(get_option('mlf_modal_bg_color','#ffffff')); ?>">
            <span class="mlf-color-preview" style="background:<?php echo get_option('mlf_modal_bg_color','#ffffff'); ?>"></span>
            </div>
            <small>Background color for modal</small>
        </div>
        
        <div class="mlf-form-group">
            <label>Modal Overlay Color</label>
            <div style="display:flex;align-items:center;gap:10px;">
            <input type="color" name="mlf_modal_overlay" value="<?php echo esc_attr(get_option('mlf_modal_overlay','#000000')); ?>">
            <span class="mlf-color-preview" style="background:<?php echo get_option('mlf_modal_overlay','#000000'); ?>"></span>
            </div>
            <small>Overlay background (use rgba for transparency)</small>
        </div>
        
        <div class="mlf-form-group">
            <label>Modal Width (px)</label>
            <input type="number" name="mlf_modal_width" value="<?php echo esc_attr(get_option('mlf_modal_width','700')); ?>" min="400" max="1200">
            <small>Maximum width of modal popup</small>
        </div>
        </div>
        </div>

        <!-- Spacing Settings -->
        <div class="mlf-settings-section">
        <h2>📏 Spacing Settings</h2>
        
        <div class="mlf-form-row">
        <div class="mlf-form-group">
            <label>Card Gap (px)</label>
            <input type="number" name="mlf_card_gap" value="<?php echo esc_attr(get_option('mlf_card_gap','20')); ?>" min="0" max="50">
            <small>Space between cards in the grid</small>
        </div>
        
        <div class="mlf-form-group">
            <label>Section Gap (px)</label>
            <input type="number" name="mlf_section_gap" value="<?php echo esc_attr(get_option('mlf_section_gap','30')); ?>" min="0" max="60">
            <small>Space between sections</small>
        </div>
        </div>
        </div>

        <!-- Advanced Settings -->
        <div class="mlf-settings-section">
        <h2>⚡ Advanced Settings</h2>
        
        <div class="mlf-form-row">
        <div class="mlf-form-group">
            <label>Enable Animations</label>
            <select name="mlf_enable_animations">
            <option value="1" <?php selected(get_option('mlf_enable_animations','1'),'1'); ?>>Yes</option>
            <option value="0" <?php selected(get_option('mlf_enable_animations','1'),'0'); ?>>No</option>
            </select>
            <small>Enable hover animations on cards</small>
        </div>
        
        <div class="mlf-form-group">
            <label>Custom CSS</label>
            <textarea name="mlf_custom_css" rows="4" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:5px;font-family:monospace;font-size:13px;" placeholder=".my-custom-class { }"><?php echo esc_textarea(get_option('mlf_custom_css','')); ?></textarea>
            <small>Add custom CSS (will be applied to frontend)</small>
        </div>
        </div>
        </div>

        <?php submit_button('Save All Settings'); ?>
        </form>
        </div>
        
        <script>
        function mlfSendTestEmail() {
            var emailInput  = document.getElementById('mlf_test_email_addr');
            var adminInput  = document.getElementById('mlf_email_admin');
            var resultDiv   = document.getElementById('mlf_test_email_result');
            var btn         = document.getElementById('mlf_send_test_email');

            var email = (emailInput && emailInput.value.trim()) ? emailInput.value.trim() : (adminInput ? adminInput.value.trim() : '');

            if (!email) {
                alert('Please enter a valid email address or fill in the Admin Notification Email field above.');
                return;
            }

            btn.disabled = true;
            btn.textContent = 'Sending…';
            resultDiv.style.display = 'none';

            var data = new FormData();
            data.append('action', 'mlf_send_test_email');
            data.append('nonce', '<?php echo wp_create_nonce('mlf_test_email_nonce'); ?>');
            data.append('email', email);

            fetch(ajaxurl, { method: 'POST', body: data })
                .then(function(r) { return r.json(); })
                .then(function(response) {
                    resultDiv.style.display = 'block';
                    if (response.success) {
                        resultDiv.style.background = '#d4edda';
                        resultDiv.style.color = '#155724';
                        resultDiv.style.padding = '10px 15px';
                        resultDiv.style.borderRadius = '5px';
                        resultDiv.style.border = '1px solid #c3e6cb';
                        resultDiv.innerHTML = '&#10003; ' + response.data;
                    } else {
                        resultDiv.style.background = '#f8d7da';
                        resultDiv.style.color = '#721c24';
                        resultDiv.style.padding = '10px 15px';
                        resultDiv.style.borderRadius = '5px';
                        resultDiv.style.border = '1px solid #f5c6cb';
                        resultDiv.innerHTML = '&#10007; ' + (response.data || 'Failed to send test email.');
                    }
                    btn.disabled = false;
                    btn.textContent = '📨 Send Test Email';
                })
                .catch(function() {
                    resultDiv.style.display = 'block';
                    resultDiv.style.background = '#f8d7da';
                    resultDiv.style.color = '#721c24';
                    resultDiv.style.padding = '10px 15px';
                    resultDiv.style.borderRadius = '5px';
                    resultDiv.innerHTML = '&#10007; Request failed. Please try again.';
                    btn.disabled = false;
                    btn.textContent = '📨 Send Test Email';
                });
        }
        </script>
        <?php
    }
}

new MLF_Settings();
