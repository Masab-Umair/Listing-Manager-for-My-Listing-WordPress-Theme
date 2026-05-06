<?php
if (!defined('ABSPATH')) exit;

class MLF_Emails {

    public function __construct(){

        add_action('mylisting/submission/save-listing-data', [$this,'admin_email'],10,2);
        add_action('transition_post_status', [$this,'status_change'],10,3);
    }

    // Helper function to decode serialized PHP data
    function mlf_decode_value($value) {
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
            }
        }
        
        return $value;
    }
    
    // ADMIN NOTIFICATION - Enhanced with full form data
    public function admin_email($id){

        $post = get_post($id);
        $meta = get_post_meta($id);
        
        // Build HTML email with form data
        $html = '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">';
        $html .= '<h2 style="color: #95160c;">🎉 New Listing Submitted</h2>';
        $html .= '<div style="background: #f5f5f5; padding: 20px; border-radius: 8px;">';
        $html .= '<h3 style="margin-top: 0;">' . get_the_title($id) . '</h3>';
        $html .= '<p><strong>Status:</strong> ' . ucfirst($post->post_status) . '</p>';
        $html .= '<p><strong>Submitted:</strong> ' . get_the_date('F j, Y g:i A', $id) . '</p>';
        $html .= '</div>';
        
        // Add key form fields
        $key_fields = [
            'email' => 'Email',
            'phone' => 'Phone',
            'complete-address' => 'Address',
            'credentials' => 'Credentials',
            'certifying-body' => 'Certifying Body',
            'my-style-of-practice' => 'Style of Practice',
            'basic-information' => 'Basic Information',
            'the-why' => 'The Why',
            'your-focus' => 'Focus',
            'formal-bio' => 'Bio'
        ];
        
        $has_fields = false;
        foreach($key_fields as $key => $label) {
            if(!empty($meta[$key][0])) {
                if(!$has_fields) {
                    $html .= '<h4 style="margin-top: 20px;">Key Details:</h4>';
                    $html .= '<table style="width: 100%; border-collapse: collapse;">';
                    $has_fields = true;
                }
                $decoded_value = $this->mlf_decode_value($meta[$key][0]);
                $html .= '<tr><td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>' . $label . ':</strong></td>';
                $html .= '<td style="padding: 8px 0; border-bottom: 1px solid #eee;">' . esc_html($decoded_value) . '</td></tr>';
            }
        }
        
        if($has_fields) {
            $html .= '</table>';
        }
        
        $html .= '<p style="margin-top: 20px;"><a href="' . admin_url('post.php?post=' . $id . '&action=edit') . '" style="background: #95160c; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px;">View Full Listing</a></p>';
        $html .= '</div>';
        
        $to = get_option('mlf_email_admin', 'esther@myndmyself.com');
        $subject = 'New Listing: ' . get_the_title($id);
        
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_bloginfo('admin_email') . '>'
        ];
        
        wp_mail($to, $subject, $html, $headers);
    }

    // STATUS CHANGE EMAILS
    public function status_change($new_status, $old_status, $post){

        if($new_status === $old_status) {
            error_log(sprintf('MLF Emails: skipped post %d, status unchanged (%s).', $post->ID, $old_status));
            return;
        }

        if($post->post_type !== 'job_listing') {
            return;
        }

        error_log(sprintf('MLF Emails: STATUS CHANGE: %s → %s for post %d', $old_status, $new_status, $post->ID));

        $template = '';
        $subject = '';
        $email_type = '';

        if ($new_status === 'publish' && $old_status !== 'publish') {
            $template = get_option('mlf_email_approved',
                '<h2>Your Listing Has Been Approved</h2><p>Your listing <strong>{{listing_title}}</strong> has been approved and is now live.</p>'
            );
            $subject = get_option('mlf_email_approved_subject', 'Listing Approved');
            $email_type = 'approval';
        }

        if ($new_status === 'draft' && $old_status !== 'draft') {
            $template = get_option('mlf_email_rejected',
                '<h2>Listing Status Update</h2><p>Your listing <strong>{{listing_title}}</strong> requires revision and has not been approved at this time.</p>'
            );
            $subject = get_option('mlf_email_rejected_subject', 'Listing Rejected');
            $email_type = 'rejection';
        }

        if (!$template) {
            error_log(sprintf('MLF Emails: ignored post %d transition from %s to %s.', $post->ID, $old_status, $new_status));
            return;
        }

        $email = $this->get_listing_owner_email($post->ID);
        error_log(sprintf('MLF Emails: EMAIL: %s for post %d', $email, $post->ID));

        if (empty($email) || !is_email($email)) {
            error_log(sprintf('MLF Emails: aborting delivery for post %d, invalid email: %s', $post->ID, $email));
            return;
        }

        $body = $this->process_email_template($template, $post);
        $subject = $this->process_email_template($subject, $post);
        $headers = ['Content-Type: text/html; charset=UTF-8'];

        wp_mail($email, $subject, $body, $headers);
        error_log(sprintf('MLF Emails: sent %s email for post %d to %s.', $email_type ?: 'status change', $post->ID, $email));
    }

    private function get_listing_owner_email($post_id) {
        // Prefer the job-specific submitter email field, then fall back to the generic listing email.
        $keys = ['user_email', 'email', '_email'];

        foreach($keys as $key) {
            $value = trim((string) get_post_meta($post_id, $key, true));
            if ($value !== '') {
                return $value;
            }
        }

        $post = get_post($post_id);
        if ($post && $post->post_author) {
            return get_the_author_meta('user_email', $post->post_author);
        }

        return '';
    }

    private function process_email_template($template, $post) {
        $replacements = [
            '{{listing_title}}' => $post->post_title,
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }
}

add_action('transition_post_status', 'mlf_force_reject_to_draft', 9, 3);

function mlf_force_reject_to_draft($new_status, $old_status, $post) {

    if ($post->post_type !== 'job_listing') {
        return;
    }

    if ($old_status === 'pending' && $new_status === 'draft') {

        remove_action('transition_post_status', 'mlf_force_reject_to_draft', 9);

        wp_update_post([
            'ID' => $post->ID,
            'post_status' => 'draft'
        ]);
    }
}

new MLF_Emails();
