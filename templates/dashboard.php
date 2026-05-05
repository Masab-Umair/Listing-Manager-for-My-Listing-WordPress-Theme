<?php
/**
 * Elementor Template - Main Container
 * This template can be used with Elementor's Theme Builder
 * or included via Elementor's Custom HTML widget
 */

if (!defined('ABSPATH')) exit;

// Get settings
$primary_color = get_option('mlf_primary_color', '#95160c');
$container_width = get_option('mlf_container_width', '1400px');
$container_bg_color = get_option('mlf_container_bg_color', '#f5f5f5');
$container_padding = get_option('mlf_container_padding', '20px');
$section_gap = get_option('mlf_section_gap', '30px');
$card_gap = get_option('mlf_card_gap', '20px');
$heading_font_size = get_option('mlf_heading_font_size', '28px');
$body_font_size = get_option('mlf_body_font_size', '14px');
$font_family = get_option('mlf_font_family', 'Poppins, sans-serif');

// Get posts
$posts = get_posts([
    'post_type' => 'job_listing',
    'numberposts' => -1,
    'post_status' => 'any'
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

<!-- MLF Dashboard Container - Elementor Ready -->
<div class="mlf-dashboard-wrapper" data-mlf-elementor="true">
    
    <!-- Header Section -->
    <header class="mlf-dashboard-header" data-mlf-section="header">
        <div class="mlf-header-content">
            <h1 class="mlf-dashboard-title">Listing Manager</h1>
            
            <!-- Stats Section -->
            <div class="mlf-stats-wrapper" data-mlf-section="stats">
                <div class="mlf-stat-item" data-mlf-stat="total">
                    <span class="mlf-stat-count"><?php echo count($posts); ?></span>
                    <span class="mlf-stat-label">Total</span>
                </div>
                <div class="mlf-stat-item" data-mlf-stat="pending">
                    <span class="mlf-stat-count"><?php echo $pending; ?></span>
                    <span class="mlf-stat-label">Pending</span>
                </div>
                <div class="mlf-stat-item" data-mlf-stat="published">
                    <span class="mlf-stat-count"><?php echo $publish; ?></span>
                    <span class="mlf-stat-label">Published</span>
                </div>
                <div class="mlf-stat-item" data-mlf-stat="draft">
                    <span class="mlf-stat-count"><?php echo $draft; ?></span>
                    <span class="mlf-stat-label">Draft</span>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Cards Grid Section -->
    <section class="mlf-cards-section" data-mlf-section="cards">
        <div class="mlf-cards-grid" id="mlf-cards-grid">
            <?php if(empty($posts)): ?>
                <div class="mlf-empty-state" data-mlf-element="empty">
                    <div class="mlf-empty-icon">📋</div>
                    <p>No listings found</p>
                </div>
            <?php else: ?>
                <?php foreach($posts as $p): 
                    $meta = get_post_meta($p->ID);
                    $email = isset($meta['email'][0]) ? $meta['email'][0] : '';
                    $phone = isset($meta['phone'][0]) ? $meta['phone'][0] : '';
                    $company = isset($meta['company'][0]) ? $meta['company'][0] : '';
                    $location = isset($meta['complete-address'][0]) ? $meta['complete-address'][0] : '';
                    $name = $p->post_title;
                    $initial = strtoupper(substr($name, 0, 1));
                    $status_class = $p->post_status == 'publish' ? 'publish' : ($p->post_status == 'pending' ? 'pending' : 'draft');
                    $status_label = $p->post_status == 'publish' ? 'Published' : ($p->post_status == 'pending' ? 'Pending' : 'Draft');
                ?>
                <!-- Single Card - Elementor Widget -->
                <article class="mlf-listing-card" data-mlf-card="true" data-id="<?php echo $p->ID; ?>">
                    <div class="mlf-card-content-wrapper" onclick="if (!event.target.closest('a')) mlfOpenDetail(<?php echo $p->ID; ?>)">
                        <div class="mlf-card-avatar" data-mlf-element="avatar">
                            <?php echo $initial; ?>
                        </div>
                        <div class="mlf-card-content" data-mlf-element="content">
                            <h3 class="mlf-card-title"><?php echo esc_html($name); ?></h3>
                            <?php if($company): ?>
                            <p class="mlf-card-company"><?php echo esc_html($company); ?></p>
                            <?php endif; ?>
                            <p class="mlf-card-email"><?php echo $email ? esc_html($email) : ''; ?></p>
                            <?php if($phone): ?>
                            <p class="mlf-card-phone"><?php echo esc_html($phone); ?></p>
                            <?php endif; ?>
                            <?php if($location): ?>
                            <p class="mlf-card-location"><?php echo esc_html($location); ?></p>
                            <?php endif; ?>
                            <span class="mlf-card-status status-badge <?php echo $status_class; ?>" data-mlf-element="status">
                                <?php echo $status_label; ?>
                            </span>
                        </div>
                    </div>
                    <div class="mlf-card-actions" onclick="event.stopPropagation();">
                        <?php if($p->post_status != 'publish'): ?>
                        <button class="mlf-card-btn mlf-btn-approve" onclick="mlfCardAction(<?php echo $p->ID; ?>, 'approve')">✓ Approve</button>
                        <?php endif; ?>
                        <?php if($p->post_status != 'draft'): ?>
                        <button class="mlf-card-btn mlf-btn-reject" onclick="mlfCardAction(<?php echo $p->ID; ?>, 'reject')">✗ Reject</button>
                        <?php endif; ?>
                        <button class="mlf-card-btn mlf-btn-delete" onclick="mlfCardAction(<?php echo $p->ID; ?>, 'trash')">🗑 Delete</button>
                    </div>
                </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
    
</div>

<style>
/* Elementor-Ready Styles */
.mlf-dashboard-wrapper {
    --mlf-primary: <?php echo $primary_color; ?>;
    --mlf-font: <?php echo $font_family; ?>;
    --mlf-heading-size: <?php echo $heading_font_size; ?>px;
    --mlf-body-size: <?php echo $body_font_size; ?>px;
    font-family: var(--mlf-font);
}

.mlf-dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: <?php echo $section_gap; ?>px;
    flex-wrap: wrap;
    gap: 15px;
}

.mlf-header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
    flex-wrap: wrap;
    gap: 20px;
}

.mlf-dashboard-title {
    font-size: var(--mlf-heading-size);
    color: var(--mlf-primary);
    margin: 0;
}

.mlf-stats-wrapper {
    display: flex;
    gap: <?php echo $card_gap; ?>px;
    flex-wrap: wrap;
}

.mlf-stat-item {
    background: #fff;
    padding: 15px 25px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    text-align: center;
}

.mlf-stat-count {
    display: block;
    font-size: 24px;
    font-weight: bold;
    color: var(--mlf-primary);
}

.mlf-stat-label {
    display: block;
    font-size: 12px;
    color: #666;
    text-transform: uppercase;
}

.mlf-cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: <?php echo $card_gap; ?>px;
}

.mlf-listing-card {
    background: #fff;
    border: 1px solid #e8e8e8;
    border-radius: 12px;
    padding: 20px;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.mlf-listing-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: var(--mlf-primary);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.mlf-listing-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.12);
    border-color: var(--mlf-primary);
}

.mlf-listing-card:hover::before {
    opacity: 1;
}

.mlf-card-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: var(--mlf-primary);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    font-weight: bold;
    margin-bottom: 15px;
}

.mlf-card-title {
    margin: 0 0 8px 0;
    font-size: 18px;
    color: #333;
}

.mlf-card-email {
    margin: 0 0 12px 0;
    color: #666;
    font-size: 14px;
}

.mlf-card-status {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
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
</style>