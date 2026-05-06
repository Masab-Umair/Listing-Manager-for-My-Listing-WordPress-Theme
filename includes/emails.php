<?php
if (!defined('ABSPATH')) exit;

class MLF_Emails {

    public function __construct() {
        // MyListing passes ($post_id, $listing_object) — accept both params
        add_action('mylisting/submission/save-listing-data', [$this, 'admin_email'], 10, 2);
        add_action('transition_post_status', [$this, 'status_change'], 10, 3);
    }

    // ── Decode serialized/JSON meta values ────────────────────────────────────
    private function mlf_decode_value($value, $key = '') {
        if (empty($value) && $value !== '0' && $value !== 0) {
            return $value;
        }

        if ($value === '1' || $value === 1 || $value === 'true')  return 'Yes';
        if ($value === '0' || $value === 0 || $value === 'false') return 'No';

        // Try unserialize (PHP serialized arrays)
        $decoded = null;
        if (is_string($value) && preg_match('/^a:\d+:/', $value)) {
            $decoded = @unserialize($value);
        }

        // Try JSON
        if ($decoded === null && is_string($value)) {
            $decoded = json_decode($value, true);
        }

        if ($decoded !== null && $decoded !== false && is_array($decoded)) {

            // ── Work Hours (MyListing nested format) ───────────────────────
            // MyListing stores: ['monday' => ['status' => 'enter-hours', 'hours' => [['from'=>'09:00','to'=>'17:00']]]]
            // Or: ['monday' => ['status' => 'enter-hours', 'from'=>'09:00', 'to'=>'17:00']]
            // Or: ['monday' => ['from'=>'09:00', 'to'=>'17:00']]
            $first = reset($decoded);
            if (is_array($first) && (isset($first['status']) || isset($first['from']) || isset($first['hours']))) {
                $output = [];
                foreach ($decoded as $day => $data) {
                    if (!is_array($data)) continue;

                    $status = $data['status'] ?? '';
                    $from = '';
                    $to = '';

                    switch ($status) {
                        case 'by-appointment-only':
                            $output[] = ucfirst($day) . ': By Appointment Only';
                            continue 2;

                        case 'enter-hours':
                            // MyListing stores times in a nested hours array
                            if (!empty($data['hours']) && is_array($data['hours'])) {
                                $slot = reset($data['hours']);
                                $from = $slot['from'] ?? '';
                                $to   = $slot['to']   ?? '';
                            }
                            // fallback to flat format within enter-hours
                            if (empty($from)) {
                                $from = $data['from'] ?? '';
                                $to   = $data['to']   ?? '';
                            }
                            break;

                        case 'closed':
                            $output[] = ucfirst($day) . ': Closed';
                            continue 2;

                        default:
                            // No status or unknown status - check for direct from/to fields
                            $from = $data['from'] ?? '';
                            $to   = $data['to']   ?? '';
                            break;
                    }

                    // Format the time range
                    if ($from && $to) {
                        // Convert to 12-hour format with AM/PM
                        $from_time = date('g:i A', strtotime($from));
                        $to_time = date('g:i A', strtotime($to));
                        $output[] = ucfirst($day) . ': ' . $from_time . ' - ' . $to_time;
                    } elseif ($from) {
                        $from_time = date('g:i A', strtotime($from));
                        $output[] = ucfirst($day) . ': From ' . $from_time;
                    } elseif ($status) {
                        $output[] = ucfirst($day) . ': ' . ucfirst(str_replace('-', ' ', $status));
                    } else {
                        $output[] = ucfirst($day) . ': Hours not set';
                    }
                }
                return implode("\n", $output);
            }

            // ── Social links (links field) ─────────────────────────────────
            if (
                isset($decoded[0]) &&
                is_array($decoded[0]) &&
                (isset($decoded[0]['network']) || isset($decoded[0]['key']))
            ) {
                $output = [];
                foreach ($decoded as $item) {
                    $network = $item['network'] ?? $item['key'] ?? '';
                    $url     = $item['url']     ?? '';

                    if (is_array($network)) $network = $network['value'] ?? $network['key'] ?? reset($network);
                    if (is_array($url))     $url     = $url['url'] ?? reset($url);

                    $network = trim((string) $network);
                    $url     = trim((string) $url);

                    if (empty($network) && empty($url)) continue;

                    if ($key === 'links') {
                        // Return raw format for JS renderer
                        if (!empty($url)) {
                            $output[] = strtolower($network) . ':' . $url;
                        } elseif (!empty($network)) {
                            $output[] = $network;
                        }
                    } else {
                        $url = str_replace(['"', "'"], '', $url);
                        if (!empty($url) && filter_var($url, FILTER_VALIDATE_URL)) {
                            $output[] = '<a href="' . esc_url($url) . '" target="_blank" rel="noopener noreferrer">' . esc_html(ucfirst($network)) . '</a>';
                        } elseif (!empty($network)) {
                            $output[] = esc_html(ucfirst($network));
                        }
                    }
                }
                return ($key === 'links') ? $output : implode('<br>', $output);
            }

            // ── Simple indexed array ───────────────────────────────────────
            if (array_keys($decoded) === range(0, count($decoded) - 1)) {
                $values = array_values($decoded);
                return count($values) === 1 ? $values[0] : implode(', ', $values);
            }

            // ── Fallback: JSON-encode ──────────────────────────────────────
            return json_encode($decoded);
        }

        return $value;
    }

    // ── Admin notification on new listing ─────────────────────────────────────
    // Accept second param ($listing) even though we don't use it — avoids PHP warnings
    public function admin_email($id, $listing = null) {
        $post = get_post($id);
        if (!$post) return;

        // Get customizable template or fallback to default
        $template = get_option('mlf_email_new_listing', $this->get_default_new_listing_template());
        $subject  = get_option('mlf_email_new_listing_subject', 'New Listing Submitted: {{listing_title}}');

        // Process template and subject with available placeholders
        $subject = $this->process_template($subject, $post);
        $body = $this->process_new_listing_template($template, $post);

        $to      = get_option('mlf_email_admin', get_bloginfo('admin_email'));
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_bloginfo('admin_email') . '>',
        ];

        $sent = wp_mail($to, $subject, $body, $headers);
        error_log(sprintf('MLF Emails: admin notification for post %d sent=%s to=%s', $id, $sent ? 'yes' : 'no', $to));
    }

    // ── Get default new listing email template ────────────────────────────────
    private function get_default_new_listing_template() {
        return '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">' .
               '<h2 style="color: #95160c;">🎉 New Listing Submitted</h2>' .
               '<div style="background: #f5f5f5; padding: 20px; border-radius: 8px;">' .
               '<h3 style="margin-top: 0;">{{listing_title}}</h3>' .
               '<p><strong>Status:</strong> {{listing_status}}</p>' .
               '<p><strong>Submitted:</strong> {{listing_date}}</p>' .
               '</div>' .
               '<p style="margin-top: 20px;">' .
               '<a href="{{admin_link}}" style="background: #95160c; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px;">View Full Listing in Admin</a>' .
               '</p>' .
               '</div>';
    }

    // ── Process new listing email template with placeholders ──────────────────
    private function process_new_listing_template($template, $post) {
        $meta = get_post_meta($post->ID);

        // Key fields to include in admin notification
        // job_email is the correct slug per the config JSON
        $key_fields = [
            'job_email'       => 'Email',
            'job_phone'       => 'Phone',
            'complete-address'=> 'Address',
            'credentials'     => 'Credentials',
            'certifying-body' => 'Certifying Body',
            'work_hours'      => 'Work Hours',
            'job_description' => 'Description of Healthcare Approach',
            'the-why'         => 'The Why',
            'your-focus'      => 'Focus',
            'formal-bio'      => 'Bio',
        ];

        // Build key details table
        $key_details_html = '';
        $has_fields = false;
        foreach ($key_fields as $key => $label) {
            $raw = $meta[$key][0] ?? '';
            if (!empty($raw)) {
                if (!$has_fields) {
                    $key_details_html .= '<h4 style="margin-top: 20px;">Key Details:</h4>';
                    $key_details_html .= '<table style="width: 100%; border-collapse: collapse;">';
                    $has_fields = true;
                }
                $decoded = $this->mlf_decode_value($raw, $key);
                if (is_array($decoded)) $decoded = implode(', ', $decoded);
                $key_details_html .= '<tr>';
                $key_details_html .= '<td style="padding: 8px 0; border-bottom: 1px solid #eee; width: 35%;"><strong>' . esc_html($label) . ':</strong></td>';
                $key_details_html .= '<td style="padding: 8px 0; border-bottom: 1px solid #eee;">' . nl2br(esc_html($decoded)) . '</td>';
                $key_details_html .= '</tr>';
            }
        }
        if ($has_fields) {
            $key_details_html .= '</table>';
        }

        // Replace standard placeholders
        $replacements = [
            '{{listing_title}}' => get_the_title($post->ID),
            '{{listing_id}}' => $post->ID,
            '{{listing_status}}' => ucfirst($post->post_status),
            '{{listing_date}}' => get_the_date('F j, Y g:i A', $post->ID),
            '{{admin_link}}' => admin_url('post.php?post=' . $post->ID . '&action=edit'),
            '{{key_details}}' => $key_details_html,
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    // ── User email on status change ───────────────────────────────────────────
    public function status_change($new_status, $old_status, $post) {
        // Skip if status hasn't actually changed
        if ($new_status === $old_status) return;

        // Only handle job listings
        if ($post->post_type !== 'job_listing') return;

        error_log(sprintf('MLF Emails: transition %s → %s for post %d', $old_status, $new_status, $post->ID));

        $template   = '';
        $subject    = '';
        $email_type = '';

        // Approved: any status → publish
        if ($new_status === 'publish' && $old_status !== 'publish') {
            $template   = get_option('mlf_email_approved', '<h2>Your Listing Has Been Approved</h2><p>Your listing <strong>{{listing_title}}</strong> is now live.</p>');
            $subject    = get_option('mlf_email_approved_subject', 'Your Listing Has Been Approved');
            $email_type = 'approval';
        }

        // Rejected: pending → draft  (only from pending, to avoid firing on normal drafts)
        if ($new_status === 'draft' && $old_status === 'pending') {
            $template   = get_option('mlf_email_rejected', '<h2>Listing Status Update</h2><p>Your listing <strong>{{listing_title}}</strong> requires revision and has not been approved at this time.</p>');
            $subject    = get_option('mlf_email_rejected_subject', 'Update on Your Listing Submission');
            $email_type = 'rejection';
        }

        if (!$template) return;

        $email = $this->get_listing_owner_email($post->ID);
        error_log(sprintf('MLF Emails: resolved email=%s for post %d', $email, $post->ID));

        if (empty($email) || !is_email($email)) {
            error_log(sprintf('MLF Emails: aborting — invalid email "%s" for post %d', $email, $post->ID));
            return;
        }

        $body    = $this->process_template($template, $post);
        $subject = $this->process_template($subject, $post);
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_bloginfo('admin_email') . '>',
        ];

        $sent = wp_mail($email, $subject, $body, $headers);
        error_log(sprintf('MLF Emails: %s email for post %d sent=%s to=%s', $email_type, $post->ID, $sent ? 'yes' : 'no', $email));
    }

    // ── Resolve the listing owner's email ────────────────────────────────────
    private function get_listing_owner_email($post_id) {
        // Check meta keys in priority order (job_email is the slug in the config)
        foreach (['job_email', 'email', '_email'] as $key) {
            $value = trim((string) get_post_meta($post_id, $key, true));
            if ($value !== '' && is_email($value)) {
                return $value;
            }
        }

        // Fall back to post author's email
        $post = get_post($post_id);
        if ($post && $post->post_author) {
            $author_email = get_the_author_meta('user_email', $post->post_author);
            if (is_email($author_email)) return $author_email;
        }

        return '';
    }

    // ── Replace template placeholders ─────────────────────────────────────────
    private function process_template($template, $post) {
        return str_replace(
            ['{{listing_title}}', '{{listing_id}}'],
            [$post->post_title, $post->ID],
            $template
        );
    }
}

new MLF_Emails();