<?php
/*
Plugin Name: Listing Manager Pro
Description: A powerful WordPress plugin that allows you to create and manage listings with ease.
Version: 4.2
Author: Masab
Author URI: https://masab.vercel.app
*/

if (!defined('ABSPATH')) exit;

define('MLF_PATH', plugin_dir_path(__FILE__));
define('MLF_URL', plugin_dir_url(__FILE__));

require_once MLF_PATH.'includes/settings.php';
require_once MLF_PATH.'includes/dashboard.php';
require_once MLF_PATH.'includes/emails.php';

// Shortcode for displaying listings
// Use [mlf_listings] or [mlf_dashboard] in any page

add_action('wp_enqueue_scripts', function () {

    wp_enqueue_style('mlf-style', MLF_URL.'assets/style.css');
    wp_enqueue_script('jquery');
    
    // Enqueue main script
    wp_enqueue_script('mlf-script', MLF_URL.'assets/mlf-script.js', ['jquery'], '1.7', true);
    
    // Pass variables to JavaScript
    wp_localize_script('mlf-script', 'mlf_vars', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('mlf_nonce')
    ]);

    // Get all settings with defaults
    $primary_color = get_option('mlf_primary_color', '#95160c');
    $font_family = get_option('mlf_font_family', 'Poppins, sans-serif');
    $heading_font_size = get_option('mlf_heading_font_size', '28px');
    $body_font_size = get_option('mlf_body_font_size', '14px');
    $heading_color = get_option('mlf_heading_color', '#333333');
    $subheading_color = get_option('mlf_subheading_color', '#666666');
    $button_bg_color = get_option('mlf_button_bg_color', '#95160c');
    $button_text_color = get_option('mlf_button_text_color', '#ffffff');
    $button_border_radius = get_option('mlf_button_border_radius', '8px');
    $button_padding = get_option('mlf_button_padding', '10px 20px');
    $card_border_radius = get_option('mlf_card_border_radius', '12px');
    $card_shadow = get_option('mlf_card_shadow', '0 2px 10px rgba(0,0,0,0.08)');
    $card_padding = get_option('mlf_card_padding', '20px');
    $card_bg_color = get_option('mlf_card_bg_color', '#ffffff');
    $card_border_color = get_option('mlf_card_border_color', '#e8e8e8');
    $container_width = get_option('mlf_container_width', '1400px');
    $container_bg_color = get_option('mlf_container_bg_color', '#f5f5f5');
    $container_padding = get_option('mlf_container_padding', '20px');
    $modal_bg_color = get_option('mlf_modal_bg_color', '#ffffff');
    $modal_overlay = get_option('mlf_modal_overlay', 'rgba(0,0,0,0.5)');
    $modal_width = get_option('mlf_modal_width', '700px');
    $card_gap = get_option('mlf_card_gap', '20px');
    $section_gap = get_option('mlf_section_gap', '30px');
    $enable_animations = get_option('mlf_enable_animations', '1');
    $custom_css = get_option('mlf_custom_css', '');
    
    // Avatar Settings
    $avatar_size = get_option('mlf_avatar_size', '60');
    $avatar_font_size = get_option('mlf_avatar_font_size', '24');
    $avatar_bg_color = get_option('mlf_avatar_bg_color', '#95160c');
    $avatar_text_color = get_option('mlf_avatar_text_color', '#ffffff');
    $avatar_margin_bottom = get_option('mlf_avatar_margin_bottom', '15');

    // Build CSS custom properties
    $css = ":root {
        --mlf-primary-color: {$primary_color};
        --mlf-font-family: {$font_family};
        --mlf-heading-font-size: {$heading_font_size}px;
        --mlf-body-font-size: {$body_font_size}px;
        --mlf-heading-color: {$heading_color};
        --mlf-subheading-color: {$subheading_color};
        --mlf-button-bg-color: {$button_bg_color};
        --mlf-button-text-color: {$button_text_color};
        --mlf-button-border-radius: {$button_border_radius};
        --mlf-button-padding: {$button_padding};
        --mlf-card-border-radius: {$card_border_radius}px;
        --mlf-card-shadow: {$card_shadow};
        --mlf-card-padding: {$card_padding};
        --mlf-card-bg-color: {$card_bg_color};
        --mlf-card-border-color: {$card_border_color};
        --mlf-container-width: {$container_width};
        --mlf-container-bg-color: {$container_bg_color};
        --mlf-container-padding: {$container_padding};
        --mlf-modal-bg-color: {$modal_bg_color};
        --mlf-modal-overlay: {$modal_overlay};
        --mlf-modal-width: {$modal_width};
        --mlf-card-gap: {$card_gap}px;
        --mlf-section-gap: {$section_gap}px;
        --mlf-avatar-size: {$avatar_size}px;
        --mlf-avatar-font-size: {$avatar_font_size}px;
        --mlf-avatar-bg-color: {$avatar_bg_color};
        --mlf-avatar-text-color: {$avatar_text_color};
        --mlf-avatar-margin-bottom: {$avatar_margin_bottom}px;
    }

    body {
        font-family: var(--mlf-font-family);
        font-size: var(--mlf-body-font-size);
    }

    .mlf-container {
        max-width: var(--mlf-container-width);
        margin: 0 auto;
        padding: var(--mlf-container-padding);
        background: var(--mlf-container-bg-color);
    }

    .mlf-header {
        margin-bottom: var(--mlf-section-gap);
    }

    .mlf-header h1 {
        font-size: var(--mlf-heading-font-size);
        color: var(--mlf-primary-color);
    }

    .mlf-grid {
        gap: var(--mlf-card-gap);
    }

    .mlf-user-card {
        background: var(--mlf-card-bg-color);
        border: 1px solid var(--mlf-card-border-color);
        border-radius: var(--mlf-card-border-radius);
        box-shadow: var(--mlf-card-shadow);
        padding: var(--mlf-card-padding);
    }

    .mlf-user-card::before {
        background: var(--mlf-primary-color);
    }

    .mlf-user-card:hover {
        border-color: var(--mlf-primary-color);
    }

    .mlf-user-card .avatar {
        width: var(--mlf-avatar-size);
        height: var(--mlf-avatar-size);
        border-radius: 50%;
        background: var(--mlf-avatar-bg-color);
        color: var(--mlf-avatar-text-color);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: var(--mlf-avatar-font-size);
        font-weight: bold;
        margin-bottom: var(--mlf-avatar-margin-bottom);
    }

    .mlf-modal-header {
        background: var(--mlf-primary-color);
    }

    .mlf-detail-section h4 {
        color: var(--mlf-primary-color);
    }

    .mlf-btn-primary,
    .mlf-btn-success {
        background: var(--mlf-button-bg-color);
        color: var(--mlf-button-text-color);
        border-radius: var(--mlf-button-border-radius);
        padding: var(--mlf-button-padding);
    }

    .mlf-btn-primary:hover,
    .mlf-btn-success:hover {
        background: var(--mlf-button-bg-color);
        opacity: 0.9;
    }

    .mlf-back-btn {
        color: var(--mlf-primary-color);
    }

    .mlf-stat-box .count {
        color: var(--mlf-primary-color);
    }

    .status-badge.publish {
        background: #d4edda;
        color: #155724;
    }

    .status-badge.pending {
        background: #fff3cd;
        color: #856404;
    }

    .status-badge.draft {
        background: #f8d7da;
        color: #721c24;
    }

    /* Modal Styles */
    .mlf-modal {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background: var(--mlf-modal-overlay);
    }

    .mlf-modal.active {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .mlf-modal-content {
        background: var(--mlf-modal-bg-color);
        max-width: var(--mlf-modal-width);
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    }

    .mlf-modal-header {
        padding: 20px 25px;
        background: var(--mlf-primary-color);
        color: #fff;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .mlf-modal-header h2 {
        margin: 0;
        color: #fff;
    }

    .mlf-modal-close {
        background: none;
        border: none;
        color: #fff;
        font-size: 28px;
        cursor: pointer;
        padding: 0;
        line-height: 1;
    }

    .mlf-modal-body {
        padding: 25px;
    }

    /* Animations */
    <?php if($enable_animations == '1'): ?>
    .mlf-user-card {
        transition: all 0.3s ease;
    }
    .mlf-user-card:hover {
        transform: translateY(-5px);
    }
    <?php endif; ?>

    /* Custom CSS */
    <?php echo $custom_css; ?>
    ";

    wp_add_inline_style('mlf-style', $css);
});
