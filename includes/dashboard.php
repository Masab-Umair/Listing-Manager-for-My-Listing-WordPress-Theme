<?php
if (!defined('ABSPATH')) exit;

// Load config file and create label mapping
function mlf_get_field_labels() {
    $config_file = plugin_dir_path(__FILE__) . '../healthcare-practionner.30-04.config.json';
    $labels = [];
    
    if (file_exists($config_file)) {
        $config = json_decode(file_get_contents($config_file), true);
        if (isset($config['fields']['used'])) {
            foreach ($config['fields']['used'] as $slug => $field) {
                if (isset($field['label'])) {
                    $labels[$slug] = $field['label'];
                }
            }
        }
    }
    
    return $labels;
}

// Helper function to make URLs clickable
function mlf_make_links_clickable($text) {
    if (empty($text)) return $text;
    
    // Pattern to match URLs
    $pattern = '/(https?:\/\/[^\s<]+)/i';
    
    return preg_replace($pattern, '<a href="$1" target="_blank" rel="noopener">$1</a>', $text);
}

class MLF_Dashboard {

    public function __construct(){
        add_shortcode('mlf_dashboard', [$this,'render']);
        add_shortcode('mlf_listings', [$this,'render']);
        add_action('wp_ajax_mlf_action', [$this,'ajax']);
        add_action('wp_ajax_nopriv_mlf_action', [$this,'ajax']);
    }

    // Main shortcode for displaying listings
    public function render($atts){
        $atts = shortcode_atts([
            'title' => 'Listing Manager',
            'show_stats' => 'yes',
            'status' => '',
            'posts_per_page' => 12,
            'columns' => 3,
        ], $atts);
        
        ob_start();
        
        $primary_color = get_option('mlf_primary_color', '#95160c');
        
        $posts = get_posts([
            'post_type'=>'job_listing',
            'numberposts'=>-1,
            'post_status'=>['publish', 'pending', 'draft']
        ]);
        
        $pending = 0;
        $publish = 0;
        $draft = 0;
        
        foreach($posts as $p){
            if($p->post_status == 'pending') $pending++;
            elseif($p->post_status == 'publish') $publish++;
            elseif($p->post_status == 'draft') $draft++;
        }
        
        ?>
        <div class="mlf-container">
            <div class="mlf-header">
                <h1><?php echo esc_html($atts['title']); ?></h1>
                <?php if($atts['show_stats'] === 'yes'): ?>
                <div class="mlf-stats">
                    <div class="mlf-stat-box">
                        <div class="count"><?php echo count($posts); ?></div>
                        <div class="label">Total</div>
                    </div>
                    <div class="mlf-stat-box">
                        <div class="count"><?php echo $pending; ?></div>
                        <div class="label">Pending</div>
                    </div>
                    <div class="mlf-stat-box">
                        <div class="count"><?php echo $publish; ?></div>
                        <div class="label">Published</div>
                    </div>
                    <div class="mlf-stat-box">
                        <div class="count"><?php echo $draft; ?></div>
                        <div class="label">Draft</div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="mlf-grid" id="mlf-cards-grid">
                <?php if(empty($posts)): ?>
                    <div class="mlf-empty">
                        <div class="mlf-empty-icon">📋</div>
                        <p>No listings found</p>
                    </div>
                <?php else: ?>
                    <?php foreach($posts as $p): 
                        $meta = get_post_meta($p->ID);
                        $email = isset($meta['job_email'][0]) ? $meta['job_email'][0] : (isset($meta['email'][0]) ? $meta['email'][0] : '');
                        $phone = isset($meta['job_phone'][0]) ? $meta['job_phone'][0] : (isset($meta['phone'][0]) ? $meta['phone'][0] : '');
                        $company = isset($meta['company'][0]) ? $meta['company'][0] : '';
                        $location = isset($meta['complete-address'][0]) ? $meta['complete-address'][0] : '';
                        $name = $p->post_title;
                        $initial = strtoupper(substr($name, 0, 1));
                        $status_class = $p->post_status == 'publish' ? 'publish' : ($p->post_status == 'pending' ? 'pending' : 'draft');
                        $status_label = $p->post_status == 'publish' ? 'Published' : ($p->post_status == 'pending' ? 'Pending' : 'Draft');
                    ?>
                    <div class="mlf-user-card" data-id="<?php echo $p->ID; ?>">
                        <div class="card-content" onclick="if (!event.target.closest('a')) mlfOpenDetail(<?php echo $p->ID; ?>)">
                            <div class="avatar"><?php echo $initial; ?></div>
                            <h3><?php echo esc_html($name); ?></h3>
                            <?php if($company): ?>
                            <p class="meta-info company"><?php echo esc_html($company); ?></p>
                            <?php endif; ?>
                            <p class="meta-info"><?php echo $email ? esc_html($email) : ''; ?></p>
                            <?php if($phone): ?>
                            <p class="meta-info phone"><?php echo esc_html($phone); ?></p>
                            <?php endif; ?>
                            <?php if($location): ?>
                            <p class="meta-info location"><?php echo esc_html($location); ?></p>
                            <?php endif; ?>
                            <span class="status-badge <?php echo $status_class; ?>"><?php echo $status_label; ?></span>
                        </div>
                        <div class="card-actions" onclick="event.stopPropagation();">
                            <?php if($p->post_status != 'publish'): ?>
                            <button class="mlf-card-btn mlf-btn-approve" onclick="mlfCardAction(<?php echo $p->ID; ?>, 'approve')">✓ Approve</button>
                            <?php endif; ?>
                            <?php if($p->post_status != 'draft'): ?>
                            <button class="mlf-card-btn mlf-btn-reject" onclick="mlfCardAction(<?php echo $p->ID; ?>, 'reject')">✗ Reject</button>
                            <?php endif; ?>
                            <button class="mlf-card-btn mlf-btn-delete" onclick="mlfCardAction(<?php echo $p->ID; ?>, 'trash')">🗑 Delete</button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Detail Modal -->
        <div class="mlf-modal" id="mlf-detail-modal">
            <div class="mlf-modal-content">
                <div class="mlf-modal-header">
                    <h2 id="mlf-modal-title">Listing Details</h2>
                    <button class="mlf-modal-close" onclick="mlfCloseModal()">&times;</button>
                </div>
                <div class="mlf-modal-body" id="mlf-modal-body">
                    <!-- Content loaded via AJAX -->
                </div>
            </div>
        </div>
        
        <?php
        
        return ob_get_clean();
    }
    
    public function ajax(){
        check_ajax_referer('mlf_nonce', 'nonce');
        $id = intval($_POST['id']);
        $type = sanitize_text_field($_POST['type']);
        
        $post = get_post($id);
        if(!$post) {
            wp_send_json_error('Post not found');
        }
        
        if($type=='approve'){
            wp_update_post(['ID'=>$id,'post_status'=>'publish']);
        }
        
        if($type=='reject'){
            wp_update_post(['ID'=>$id,'post_status'=>'draft']);
        }
        
        if($type=='trash'){
            wp_trash_post($id);
        }
        
        wp_send_json_success();
    }
    
    public function get_detail(){
        $id = intval($_POST['id']);
        $post = get_post($id);
        
        if(!$post) {
            wp_send_json_error('Post not found');
        }
        
        $meta = get_post_meta($id);
        $meta_array = [];
        $mlf_hidden_keys = ['_track_stats','_job_edited','_user_package_id','_package_id'];
        
        // Helper function to decode serialized PHP data
        function mlf_decode_value($value, $key = '') {
            if (empty($value) && $value !== '0' && $value !== 0) {
                return $value;
            }
            
            // Convert 0/1 to Yes/No
            if ($value === '1' || $value === 1 || $value === 'true') {
                return 'Yes';
            }
            if ($value === '0' || $value === 0 || $value === 'false') {
                return 'No';
            }
            
            // Check if it looks like serialized PHP array (a:...)
            if (is_string($value) && preg_match('/^a:\d+:/', $value)) {
                $decoded = @unserialize($value);
                if ($decoded !== false) {
                    // Check if it's a simple indexed array (select field values)
                    $is_indexed = true;
                    $keys = array_keys($decoded);
                    for ($i = 0; $i < count($keys); $i++) {
                        if ($keys[$i] !== $i) {
                            $is_indexed = false;
                            break;
                        }
                    }
                    
                    // If it's a simple indexed array with string values, return the values
                    if ($is_indexed && !empty($decoded)) {
                        $values = array_values($decoded);
                        if (count($values) === 1) {
                            return $values[0];
                        }
                        return implode(", ", $values);
                    }
                    
                    // Format the decoded array into readable text (work hours, etc.)
$output = [];
foreach ($decoded as $day => $data) {

    // Skip the timezone entry — it's a string, not a day array
    if ($day === 'timezone') {
        continue;
    }

    if (is_array($data) && isset($data['status'])) {
        $status = $data['status'];
        $from   = isset($data['from']) ? trim((string) $data['from']) : '';
        $to     = isset($data['to'])   ? trim((string) $data['to'])   : '';

        $status_text = '';
        switch ($status) {
            case 'by-appointment-only':
                $status_text = 'By Appointment Only';
                break;

            // MyListing uses both 'enter-hours' and 'open' for time ranges
            case 'enter-hours':
            case 'open':
                if ($from !== '' && $to !== '') {
                    $status_text = $from . ' – ' . $to;
                } elseif ($from !== '') {
                    $status_text = 'From ' . $from;
                } elseif ($to !== '') {
                    $status_text = 'Until ' . $to;
                } else {
                    $status_text = 'Hours not set';
                }
                break;

            case 'closed':
                $status_text = 'Closed';
                break;

            default:
                // Handles any other statuses gracefully
                $status_text = ucfirst(str_replace('-', ' ', $status));
                if ($from !== '' && $to !== '') {
                    $status_text .= ' (' . $from . ' – ' . $to . ')';
                }
        }

        $day_label = ucfirst($day);
        $output[] = "<strong>{$day_label}:</strong> {$status_text}";

    } else {
        // Non-array entry (shouldn't happen after timezone skip, but safe fallback)
        $output[] = ucfirst($day) . ': ' . (is_array($data) ? json_encode($data) : $data);
    }
}
return implode('<br>', $output);
                }
            }
            
            return $value;
        }
        
        foreach($meta as $k => $v) {
            if(!in_array($k, ['_edit_lock', '_edit_last'])) {
                $value = is_array($v) ? $v[0] : $v;
                // Decode serialized PHP data
                $meta_array[$k] = mlf_decode_value($value);
            }
        }
        
        $status_class = $post->post_status == 'publish' ? 'publish' : ($post->post_status == 'pending' ? 'pending' : 'draft');
        $status_label = $post->post_status == 'publish' ? 'Published' : ($post->post_status == 'pending' ? 'Pending' : 'Draft');
        
        // Get field labels from config
        $field_labels = mlf_get_field_labels();
        
        wp_send_json_success([
            'id' => $post->ID,
            'title' => $post->post_title,
            'status' => $post->post_status,
            'status_class' => $status_class,
            'status_label' => $status_label,
            'date' => get_the_date('F j, Y', $post),
            'meta' => $meta_array,
            'labels' => $field_labels,
            'post_status' => $post->post_status
        ]);
    }
}

new MLF_Dashboard();

// Add separate handler for getting detail
add_action('wp_ajax_mlf_get_detail', function(){
    $id = intval($_POST['id']);
    $post = get_post($id);
    
    if(!$post) {
        wp_send_json_error('Post not found');
    }
    
    $meta = get_post_meta($id);
    $meta_array = [];
    $mlf_hidden_keys = ['_track_stats','_job_edited','_user_package_id','_package_id'];
    
    // Helper function to decode serialized PHP data
    function mlf_decode_value($value, $key = '') {
        if (empty($value) && $value !== '0' && $value !== 0) {
            return $value;
        }
        
        // Convert 0/1 to Yes/No
        if ($value === '1' || $value === 1 || $value === 'true') {
            return 'Yes';
        }
        if ($value === '0' || $value === 0 || $value === 'false') {
            return 'No';
        }
        
        // Handle different data types
        $decoded = null;
        if (is_array($value)) {
            $decoded = $value;
        } elseif (is_string($value) && preg_match('/^a:\d+:/', $value)) {
            $decoded = @unserialize($value);
        } elseif (is_string($value)) {
            $decoded = json_decode($value, true);
        }
        if ($decoded !== null && $decoded !== false && is_array($decoded)) {
            // Check if it's an array with network/url structure (Social Networks field)
            if (
                !empty($decoded) &&
                isset($decoded[0]) &&
                is_array($decoded[0]) &&
                (isset($decoded[0]['network']) || isset($decoded[0]['key']))
            ) {
                // For 'links' field, return raw data so JavaScript can render properly
                // For other contexts (like emails), return HTML
                if ($key === 'links') {
                    $output = [];
                    foreach ($decoded as $item) {
                        $network = $item['network'] ?? $item['key'] ?? '';
                        $url     = $item['url'] ?? '';

                        // Handle nested array values
                        if (is_array($network)) {
                            $network = $network['value'] ?? $network['key'] ?? reset($network);
                        }
                        if (is_array($url)) {
                            $url = $url['url'] ?? reset($url);
                        }

                        // Convert to string and clean
                        $network = trim((string) $network);
                        $url     = trim((string) $url);

                        // Skip empty entries
                        if (empty($network) && empty($url)) {
                            continue;
                        }

                        // Return raw format for JavaScript to process
                        if (!empty($url)) {
                            $output[] = strtolower($network) . ':' . $url;
                        } else if (!empty($network)) {
                            $output[] = $network;
                        }
                    }
                    return $output;
                } else {
                    // For non-links fields, return HTML as before
                    $output = [];

                    foreach ($decoded as $item) {
                        // Handle both 'network' and 'key' field names
                        $network = $item['network'] ?? $item['key'] ?? '';
                        $url     = $item['url'] ?? '';

                        // Handle nested array values
                        if (is_array($network)) {
                            $network = $network['value'] ?? $network['key'] ?? reset($network);
                        }
                        if (is_array($url)) {
                            $url = $url['url'] ?? reset($url);
                        }

                        // Convert to string and clean
                        $network = trim((string) $network);
                        $url     = trim((string) $url);

                        // Skip empty entries
                        if (empty($network) && empty($url)) {
                            continue;
                        }

                        // Normalize names
                        $labels = [
                            'instagram' => 'Instagram',
                            'facebook'  => 'Facebook',
                            'linkedin'  => 'LinkedIn',
                            'youtube'   => 'YouTube',
                            'twitter'   => 'Twitter',
                            'x'         => 'X',
                        ];

                        $key_lower = strtolower($network);
                        $network = $labels[$key_lower] ?? ucfirst($network);

                        // --- IMPORTANT FIX: clean URL ---
                        $url = str_replace(['"', "'"], '', $url);

                        // Generate clickable links
                        if (!empty($url) && filter_var($url, FILTER_VALIDATE_URL)) {
                            $output[] = '<a href="' . esc_url($url) . '" target="_blank" rel="noopener noreferrer">'
                                      . esc_html($network)
                                      . '</a>';
                        } elseif (!empty($network)) {
                            $output[] = esc_html($network);
                        }
                    }

                    // Return CLEAN HTML (not array)
                    return implode('<br>', $output);
                }
            }

            
            // Check if it's a simple indexed array (select field values)
            $is_indexed = true;
            $keys = array_keys($decoded);
            for ($i = 0; $i < count($keys); $i++) {
                if ($keys[$i] !== $i) {
                    $is_indexed = false;
                    break;
                }
            }
            
            // If it's a simple indexed array with string values, return the values
            if ($is_indexed && !empty($decoded)) {
                $values = array_values($decoded);
                if (count($values) === 1) {
                    return $values[0];
                }
                return implode(", ", $values);
            }
            
            // Format the decoded array into readable text (work hours, etc.)
            $output = [];
            foreach ($decoded as $day => $data) {
                if (is_array($data) && isset($data['status'])) {
                    $status = $data['status'];
                    $from = isset($data['from']) ? $data['from'] : '';
                    $to   = isset($data['to'])   ? $data['to']   : '';
                    
                    // Make status human readable
                    $status_text = '';
                    switch ($status) {
                        case 'by-appointment-only':
                            $status_text = 'By Appointment Only';
                            break;
                        case 'enter-hours':
                            if ($from && $to) {
                                $status_text = $from . ' – ' . $to;
                            } elseif ($from) {
                                $status_text = 'From ' . $from;
                            } else {
                                $status_text = 'Hours not set';
                            }
                            break;
                        case 'closed':
                            $status_text = 'Closed';
                            break;
                        default:
                            $status_text = ucfirst(str_replace('-', ' ', $status));
                    }
                    
                    $output[] = "$day: $status_text";
                } else {
                    $output[] = "$day: " . (is_array($data) ? json_encode($data) : $data);
                }
            }
            return implode("\n", $output);
        }
        
        return $value;
    }
    
    foreach($meta as $k => $v) {
        // Skip placeholder fields and admin fields
        $hide = ['__track_stats', '_job_edited', '_user_package_id', '_package_id'];
        if(!in_array($k, ['_edit_lock', '_edit_last']) && !in_array($k,$hide) && strpos($k, 'placeholder') === false && strpos($k, 'save-your-work') === false) {
            $value = is_array($v) ? $v[0] : $v;
            // Skip empty values
            if(!empty($value) && $value !== '') {
                // Decode serialized PHP data
                $meta_array[$k] = mlf_decode_value($value);
            }
        }
    }
    
    // Organize fields into sections
    $sections = [
        'Contact Information' => ['email', 'job_email', 'phone', 'job_phone', 'complete-address', 'links'],
        'Professional Details' => ['credentials', 'certifying-body', 'my-liability-insurance-provider-is', 'health-expert-panel', 'advertising', 'my-style-of-practice'],
        'Healthcare Focus' => ['healthcare-issues-and-approaches', 'placholder-for-accessibility', 'other', 'your-focus', 'idea'],
        'About Your Practice' => ['basic-information', 'the-why', 'your-collaborations', 'your-influences', 'year', 'formal-bio', 'a-little-more-about-me'],
        'Experience & Recognition' => ['awards-and-honours', 'peer-references', 'recent-patient-testimonials'],
        'Availability' => ['form-heading', 'i-offer-the-following-types-of-sessions', 'initial-appointment', 'follow-up-appointments', '3rd-party-insurance', 'online-booking', 'confidentiality', 'waiting-list', 'offerings', 'additional-services', 'services'],
        'Connections' => ['connections', 'associations'],
        'Compliance' => ['crimimal-records-check', 'criminal-records-check-received', 'approval-dateinitials', 'date-of-interview'],
        'Media' => ['podcast-titles-done-with-this-practitioner-for-mynd-myself-admin']
    ];
    
    $organized_meta = [];
    foreach($sections as $section => $keys) {
        $section_data = [];
        foreach($keys as $key) {
            if(isset($meta_array[$key]) && !empty($meta_array[$key])) {
                $section_data[$key] = $meta_array[$key];
            }
        }
        if(!empty($section_data)) {
            $organized_meta[$section] = $section_data;
        }
    }
    
    // Add any remaining fields not in our predefined sections
    $handled_keys = [];
    foreach($sections as $keys) {
        $handled_keys = array_merge($handled_keys, $keys);
    }
    $remaining = [];
    foreach($meta_array as $k => $v) {
        if(!in_array($k, $handled_keys) && !in_array($k, ['_edit_lock', '_edit_last']) && strpos($k, 'placeholder') === false && strpos($k, 'save-your-work') === false) {
            $remaining[$k] = $v;
        }
    }
    if(!empty($remaining)) {
        $organized_meta['Other Information'] = $remaining;
    }
    
    $status_class = $post->post_status == 'publish' ? 'publish' : ($post->post_status == 'pending' ? 'pending' : 'draft');
    $status_label = $post->post_status == 'publish' ? 'Published' : ($post->post_status == 'pending' ? 'Pending' : 'Draft');
    
    wp_send_json_success([
        'id' => $post->ID,
        'title' => $post->post_title,
        'status' => $post->post_status,
        'status_class' => $status_class,
        'status_label' => $status_label,
        'date' => get_the_date('F j, Y', $post),
        'meta' => $meta_array,
        'sections' => $organized_meta,
        'post_status' => $post->post_status
    ]);
});

// Add handler for saving edited data
add_action('wp_ajax_mlf_save_edit', function(){
    $id = intval($_POST['id']);
    
    if(!$id) {
        wp_send_json_error('Invalid ID');
    }
    
    $post = get_post($id);
    if(!$post) {
        wp_send_json_error('Post not found');
    }
    
    // Update post title if provided
    if(isset($_POST['job_title']) && !empty($_POST['job_title'])) {
        wp_update_post([
            'ID' => $id,
            'post_title' => sanitize_text_field($_POST['job_title'])
        ]);
    }
    
    // Update all meta fields
    foreach($_POST as $key => $value) {
        if($key === 'id' || $key === 'action') continue;
        
        // Skip internal WordPress fields
        if(strpos($key, '_') === 0) continue;
        
        update_post_meta($id, $key, sanitize_text_field($value));
    }
    
    wp_send_json_success('Data saved successfully');
});
