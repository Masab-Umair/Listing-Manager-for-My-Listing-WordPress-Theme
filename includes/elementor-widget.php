<?php
/**
 * Elementor Widget for Listing Manager Pro
 * 
 * This file registers a custom Elementor widget that allows
 * users to display and edit listings directly in Elementor
 * 
 * @package Listing Manager Pro
 * @version 1.5
 */

if (!defined('ABSPATH')) exit;

// Register Elementor Widget
add_action('elementor/widgets/widgets_registered', function($widgets_manager) {
    
    class MLF_Elementor_Widget extends \Elementor\Widget_Base {
        
        public function get_name() {
            return 'mlf_listings';
        }
        
        public function get_title() {
            return __('Listing Manager', 'mlf-pro');
        }
        
        public function get_icon() {
            return 'eicon-gallery-grid'; // Updated icon for listing manager
        }
        
        public function get_categories() {
            return ['general'];
        }
        
        public function get_script_depends() {
            return ['mlf-script'];
        }
        
        public function get_style_depends() {
            return [];
        }
        
        protected function register_controls() {
            // Content Section
            $this->start_controls_section(
                'content_section',
                [
                    'label' => __('Content', 'mlf-pro'),
                    'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
                ]
            );
            
            $this->add_control(
                'mlf_title',
                [
                    'label' => __('Title', 'mlf-pro'),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'default' => __('Listing Manager', 'mlf-pro'),
                    'placeholder' => __('Enter title', 'mlf-pro'),
                ]
            );
            
            $this->add_control(
                'mlf_show_stats',
                [
                    'label' => __('Show Statistics', 'mlf-pro'),
                    'type' => \Elementor\Controls_Manager::SWITCHER,
                    'default' => 'yes',
                    'label_on' => __('Show', 'mlf-pro'),
                    'label_off' => __('Hide', 'mlf-pro'),
                ]
            );
            
            $this->add_control(
                'mlf_post_status',
                [
                    'label' => __('Filter by Status', 'mlf-pro'),
                    'type' => \Elementor\Controls_Manager::SELECT,
                    'default' => '',
                    'options' => [
                        '' => __('All', 'mlf-pro'),
                        'publish' => __('Published', 'mlf-pro'),
                        'pending' => __('Pending', 'mlf-pro'),
                        'draft' => __('Draft', 'mlf-pro'),
                    ],
                ]
            );
            
            $this->add_control(
                'mlf_posts_per_page',
                [
                    'label' => __('Number of Listings', 'mlf-pro'),
                    'type' => \Elementor\Controls_Manager::NUMBER,
                    'default' => 12,
                    'min' => 1,
                    'max' => 50,
                ]
            );
            
            $this->end_controls_section();
            
            // =====================
            // TYPOGRAPHY SECTION
            // =====================
            $this->start_controls_section(
                'typography_section',
                [
                    'label' => __('Typography', 'mlf-pro'),
                    'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                ]
            );
            
            $this->add_control(
                'mlf_font_family',
                [
                    'label' => __('Font Family', 'mlf-pro'),
                    'type' => \Elementor\Controls_Manager::FONT,
                    'default' => 'Poppins',
                ]
            );
            
            $this->add_responsive_control(
                'mlf_title_font_size',
                [
                    'label' => __('Title Font Size', 'mlf-pro'),
                    'type' => \Elementor\Controls_Manager::SLIDER,
                    'size_units' => ['px'],
                    'range' => [
                        'px' => [
                            'min' => 14,
                            'max' => 48,
                        ],
                    ],
                    'default' => [
                        'unit' => 'px',
                        'size' => 24,
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .mlf-elementor-title' => 'font-size: {{SIZE}}{{UNIT}}',
                    ],
                ]
            );
            
            $this->add_control(
                'mlf_title_font_weight',
                [
                    'label' => __('Title Font Weight', 'mlf-pro'),
                    'type' => \Elementor\Controls_Manager::SELECT,
                    'default' => '600',
                    'options' => [
                        '400' => __('Normal', 'mlf-pro'),
                        '500' => __('Medium', 'mlf-pro'),
                        '600' => __('Semi Bold', 'mlf-pro'),
                        '700' => __('Bold', 'mlf-pro'),
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .mlf-elementor-title' => 'font-weight: {{VALUE}}',
                    ],
                ]
            );
            
            $this->add_responsive_control(
                'mlf_card_title_size',
                [
                    'label' => __('Card Title Size', 'mlf-pro'),
                    'type' => \Elementor\Controls_Manager::SLIDER,
                    'size_units' => ['px'],
                    'range' => [
                        'px' => [
                            'min' => 12,
                            'max' => 32,
                        ],
                    ],
                    'default' => [
                        'unit' => 'px',
                        'size' => 16,
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .mlf-elementor-card-title' => 'font-size: {{SIZE}}{{UNIT}}',
                    ],
                ]
            );
            
            $this->add_responsive_control(
                'mlf_meta_text_size',
                [
                    'label' => __('Meta Text Size', 'mlf-pro'),
                    'type' => \Elementor\Controls_Manager::SLIDER,
                    'size_units' => ['px'],
                    'range' => [
                        'px' => [
                            'min' => 10,
                            'max' => 20,
                        ],
                    ],
                    'default' => [
                        'unit' => 'px',
                        'size' => 13,
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .mlf-elementor-card-email, {{WRAPPER}} .mlf-elementor-card-phone, {{WRAPPER}} .mlf-elementor-card-company, {{WRAPPER}} .mlf-elementor-card-location' => 'font-size: {{SIZE}}{{UNIT}}',
                    ],
                ]
            );
            
            $this->end_controls_section();
            
            // =====================
            // COLORS SECTION
            // =====================
            $this->start_controls_section(
                'colors_section',
                [
                    'label' => __('Colors', 'mlf-pro'),
                    'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                ]
            );
            
            $this->add_control(
                'mlf_primary_color',
                [
                    'label' => __('Primary Color', 'mlf-pro'),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => '#95160c',
                    'selectors' => [
                        '{{WRAPPER}} .mlf-elementor-avatar' => 'background-color: {{VALUE}}',
                        '{{WRAPPER}} .mlf-stat-count' => 'color: {{VALUE}}',
                    ],
                ]
            );
            
            $this->add_control(
                'mlf_title_color',
                [
                    'label' => __('Title Color', 'mlf-pro'),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => '#333333',
                    'selectors' => [
                        '{{WRAPPER}} .mlf-elementor-title' => 'color: {{VALUE}}',
                    ],
                ]
            );
            
            $this->add_control(
                'mlf_card_title_color',
                [
                    'label' => __('Card Title Color', 'mlf-pro'),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => '#333333',
                    'selectors' => [
                        '{{WRAPPER}} .mlf-elementor-card-title' => 'color: {{VALUE}}',
                    ],
                ]
            );
            
            $this->add_control(
                'mlf_meta_color',
                [
                    'label' => __('Meta Text Color', 'mlf-pro'),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => '#666666',
                    'selectors' => [
                        '{{WRAPPER}} .mlf-elementor-card-email, {{WRAPPER}} .mlf-elementor-card-phone, {{WRAPPER}} .mlf-elementor-card-company, {{WRAPPER}} .mlf-elementor-card-location' => 'color: {{VALUE}}',
                    ],
                ]
            );
            
            $this->add_control(
                'mlf_container_bg',
                [
                    'label' => __('Container Background', 'mlf-pro'),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => '#f5f5f5',
                    'selectors' => [
                        '{{WRAPPER}} .mlf-elementor-container' => 'background-color: {{VALUE}}',
                    ],
                ]
            );
            
            $this->add_control(
                'mlf_card_bg',
                [
                    'label' => __('Card Background', 'mlf-pro'),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => '#ffffff',
                    'selectors' => [
                        '{{WRAPPER}} .mlf-elementor-card' => 'background-color: {{VALUE}}',
                    ],
                ]
            );
            
            $this->add_control(
                'mlf_card_border_color',
                [
                    'label' => __('Card Border Color', 'mlf-pro'),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => '#e8e8e8',
                    'selectors' => [
                        '{{WRAPPER}} .mlf-elementor-card' => 'border-color: {{VALUE}}',
                    ],
                ]
            );
            
            $this->end_controls_section();
            
            // =====================
            // SPACING SECTION
            // =====================
            $this->start_controls_section(
                'spacing_section',
                [
                    'label' => __('Spacing', 'mlf-pro'),
                    'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                ]
            );
            
            $this->add_responsive_control(
                'mlf_container_padding',
                [
                    'label' => __('Container Padding', 'mlf-pro'),
                    'type' => \Elementor\Controls_Manager::DIMENSIONS,
                    'size_units' => ['px', '%'],
                    'default' => [
                        'top' => '20',
                        'right' => '20',
                        'bottom' => '20',
                        'left' => '20',
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .mlf-elementor-container' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
                    ],
                ]
            );
            
            $this->add_responsive_control(
                'mlf_container_radius',
                [
                    'label' => __('Container Border Radius', 'mlf-pro'),
                    'type' => \Elementor\Controls_Manager::DIMENSIONS,
                    'size_units' => ['px'],
                    'default' => [
                        'top' => '12',
                        'right' => '12',
                        'bottom' => '12',
                        'left' => '12',
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .mlf-elementor-container' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
                    ],
                ]
            );
            
            $this->add_responsive_control(
                'mlf_card_padding',
                [
                    'label' => __('Card Padding', 'mlf-pro'),
                    'type' => \Elementor\Controls_Manager::DIMENSIONS,
                    'size_units' => ['px'],
                    'default' => [
                        'top' => '20',
                        'right' => '20',
                        'bottom' => '20',
                        'left' => '20',
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .mlf-elementor-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
                    ],
                ]
            );
            
            $this->add_responsive_control(
                'mlf_card_radius',
                [
                    'label' => __('Card Border Radius', 'mlf-pro'),
                    'type' => \Elementor\Controls_Manager::DIMENSIONS,
                    'size_units' => ['px'],
                    'default' => [
                        'top' => '12',
                        'right' => '12',
                        'bottom' => '12',
                        'left' => '12',
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .mlf-elementor-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
                    ],
                ]
            );
            
            $this->add_responsive_control(
                'mlf_gap',
                [
                    'label' => __('Grid Gap', 'mlf-pro'),
                    'type' => \Elementor\Controls_Manager::SLIDER,
                    'size_units' => ['px'],
                    'range' => [
                        'px' => [
                            'min' => 0,
                            'max' => 50,
                        ],
                    ],
                    'default' => [
                        'unit' => 'px',
                        'size' => 20,
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .mlf-elementor-grid' => 'gap: {{SIZE}}{{UNIT}}',
                    ],
                ]
            );
            
            $this->add_responsive_control(
                'mlf_card_margin_bottom',
                [
                    'label' => __('Card Margin Bottom', 'mlf-pro'),
                    'type' => \Elementor\Controls_Manager::SLIDER,
                    'size_units' => ['px'],
                    'range' => [
                        'px' => [
                            'min' => 0,
                            'max' => 30,
                        ],
                    ],
                    'default' => [
                        'unit' => 'px',
                        'size' => 0,
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .mlf-elementor-card' => 'margin-bottom: {{SIZE}}{{UNIT}}',
                    ],
                ]
            );
            
            $this->end_controls_section();
            
            // =====================
            // AVATAR SECTION
            // =====================
            $this->start_controls_section(
                'avatar_section',
                [
                    'label' => __('Avatar', 'mlf-pro'),
                    'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                ]
            );
            
            $this->add_control(
                'mlf_avatar_bg',
                [
                    'label' => __('Background Color', 'mlf-pro'),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => '#95160c',
                    'selectors' => [
                        '{{WRAPPER}} .mlf-elementor-avatar' => 'background-color: {{VALUE}}',
                    ],
                ]
            );
            
            $this->add_control(
                'mlf_avatar_color',
                [
                    'label' => __('Text Color', 'mlf-pro'),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => '#ffffff',
                    'selectors' => [
                        '{{WRAPPER}} .mlf-elementor-avatar' => 'color: {{VALUE}}',
                    ],
                ]
            );
            
            $this->add_responsive_control(
                'mlf_avatar_size',
                [
                    'label' => __('Size', 'mlf-pro'),
                    'type' => \Elementor\Controls_Manager::SLIDER,
                    'size_units' => ['px'],
                    'range' => [
                        'px' => [
                            'min' => 30,
                            'max' => 100,
                        ],
                    ],
                    'default' => [
                        'unit' => 'px',
                        'size' => 50,
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .mlf-elementor-avatar' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}; font-size: {{SIZE}}{{UNIT}} !important;',
                    ],
                ]
            );
            
            $this->add_responsive_control(
                'mlf_avatar_margin_bottom',
                [
                    'label' => __('Margin Bottom', 'mlf-pro'),
                    'type' => \Elementor\Controls_Manager::SLIDER,
                    'size_units' => ['px'],
                    'range' => [
                        'px' => [
                            'min' => 0,
                            'max' => 30,
                        ],
                    ],
                    'default' => [
                        'unit' => 'px',
                        'size' => 12,
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .mlf-elementor-avatar' => 'margin-bottom: {{SIZE}}{{UNIT}}',
                    ],
                ]
            );
            
            $this->end_controls_section();
            
            // =====================
            // STATUS BADGE SECTION
            // =====================
            $this->start_controls_section(
                'badge_section',
                [
                    'label' => __('Status Badge', 'mlf-pro'),
                    'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                ]
            );
            
            $this->add_responsive_control(
                'mlf_badge_padding',
                [
                    'label' => __('Padding', 'mlf-pro'),
                    'type' => \Elementor\Controls_Manager::DIMENSIONS,
                    'size_units' => ['px'],
                    'default' => [
                        'top' => '4',
                        'right' => '12',
                        'bottom' => '4',
                        'left' => '12',
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .mlf-elementor-status' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
                    ],
                ]
            );
            
            $this->add_responsive_control(
                'mlf_badge_radius',
                [
                    'label' => __('Border Radius', 'mlf-pro'),
                    'type' => \Elementor\Controls_Manager::SLIDER,
                    'size_units' => ['px'],
                    'range' => [
                        'px' => [
                            'min' => 0,
                            'max' => 30,
                        ],
                    ],
                    'default' => [
                        'unit' => 'px',
                        'size' => 20,
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .mlf-elementor-status' => 'border-radius: {{SIZE}}{{UNIT}}',
                    ],
                ]
            );
            
            $this->add_responsive_control(
                'mlf_badge_font_size',
                [
                    'label' => __('Font Size', 'mlf-pro'),
                    'type' => \Elementor\Controls_Manager::SLIDER,
                    'size_units' => ['px'],
                    'range' => [
                        'px' => [
                            'min' => 8,
                            'max' => 18,
                        ],
                    ],
                    'default' => [
                        'unit' => 'px',
                        'size' => 11,
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .mlf-elementor-status' => 'font-size: {{SIZE}}{{UNIT}}',
                    ],
                ]
            );
            
            $this->add_control(
                'mlf_badge_publish_bg',
                [
                    'label' => __('Published Background', 'mlf-pro'),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => '#d1fae5',
                    'selectors' => [
                        '{{WRAPPER}} .mlf-elementor-status.status-badge.publish' => 'background-color: {{VALUE}}',
                    ],
                ]
            );
            
            $this->add_control(
                'mlf_badge_publish_color',
                [
                    'label' => __('Published Text Color', 'mlf-pro'),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => '#065f46',
                    'selectors' => [
                        '{{WRAPPER}} .mlf-elementor-status.status-badge.publish' => 'color: {{VALUE}}',
                    ],
                ]
            );
            
            $this->add_control(
                'mlf_badge_pending_bg',
                [
                    'label' => __('Pending Background', 'mlf-pro'),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => '#fef3c7',
                    'selectors' => [
                        '{{WRAPPER}} .mlf-elementor-status.status-badge.pending' => 'background-color: {{VALUE}}',
                    ],
                ]
            );
            
            $this->add_control(
                'mlf_badge_pending_color',
                [
                    'label' => __('Pending Text Color', 'mlf-pro'),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => '#92400e',
                    'selectors' => [
                        '{{WRAPPER}} .mlf-elementor-status.status-badge.pending' => 'color: {{VALUE}}',
                    ],
                ]
            );
            
            $this->add_control(
                'mlf_badge_draft_bg',
                [
                    'label' => __('Draft Background', 'mlf-pro'),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => '#f3f4f6',
                    'selectors' => [
                        '{{WRAPPER}} .mlf-elementor-status.status-badge.draft' => 'background-color: {{VALUE}}',
                    ],
                ]
            );
            
            $this->add_control(
                'mlf_badge_draft_color',
                [
                    'label' => __('Draft Text Color', 'mlf-pro'),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => '#6b7280',
                    'selectors' => [
                        '{{WRAPPER}} .mlf-elementor-status.status-badge.draft' => 'color: {{VALUE}}',
                    ],
                ]
            );
            
            $this->end_controls_section();
            
            // =====================
            // CARD HOVER SECTION
            // =====================
            $this->start_controls_section(
                'card_hover_section',
                [
                    'label' => __('Card Hover', 'mlf-pro'),
                    'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                ]
            );
            
            $this->add_control(
                'mlf_card_hover_border_color',
                [
                    'label' => __('Border Color on Hover', 'mlf-pro'),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => '#95160c',
                    'selectors' => [
                        '{{WRAPPER}} .mlf-elementor-card:hover' => 'border-color: {{VALUE}}',
                    ],
                ]
            );
            
            $this->add_group_control(
                \Elementor\Group_Control_Box_Shadow::get_type(),
                [
                    'name' => 'mlf_card_hover_shadow',
                    'label' => __('Shadow on Hover', 'mlf-pro'),
                    'selector' => '{{WRAPPER}} .mlf-elementor-card:hover',
                ]
            );
            
            $this->add_responsive_control(
                'mlf_card_hover_translate',
                [
                    'label' => __('Translate Y on Hover', 'mlf-pro'),
                    'type' => \Elementor\Controls_Manager::SLIDER,
                    'size_units' => ['px'],
                    'range' => [
                        'px' => [
                            'min' => -20,
                            'max' => 20,
                        ],
                    ],
                    'default' => [
                        'unit' => 'px',
                        'size' => -5,
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .mlf-elementor-card:hover' => 'transform: translateY({{SIZE}}{{UNIT}})',
                    ],
                ]
            );
            
            $this->end_controls_section();
            
            // =====================
            // STATS SECTION
            // =====================
            $this->start_controls_section(
                'stats_section',
                [
                    'label' => __('Statistics', 'mlf-pro'),
                    'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                ]
            );
            
            $this->add_control(
                'mlf_stats_bg',
                [
                    'label' => __('Stats Box Background', 'mlf-pro'),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => '#ffffff',
                    'selectors' => [
                        '{{WRAPPER}} .mlf-stat-item' => 'background-color: {{VALUE}}',
                    ],
                ]
            );
            
            $this->add_responsive_control(
                'mlf_stats_padding',
                [
                    'label' => __('Stats Box Padding', 'mlf-pro'),
                    'type' => \Elementor\Controls_Manager::DIMENSIONS,
                    'size_units' => ['px'],
                    'default' => [
                        'top' => '12',
                        'right' => '20',
                        'bottom' => '12',
                        'left' => '20',
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .mlf-stat-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
                    ],
                ]
            );
            
            $this->add_responsive_control(
                'mlf_stats_radius',
                [
                    'label' => __('Stats Box Radius', 'mlf-pro'),
                    'type' => \Elementor\Controls_Manager::SLIDER,
                    'size_units' => ['px'],
                    'range' => [
                        'px' => [
                            'min' => 0,
                            'max' => 20,
                        ],
                    ],
                    'default' => [
                        'unit' => 'px',
                        'size' => 8,
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .mlf-stat-item' => 'border-radius: {{SIZE}}{{UNIT}}',
                    ],
                ]
            );
            
            $this->add_responsive_control(
                'mlf_stats_count_size',
                [
                    'label' => __('Stats Count Size', 'mlf-pro'),
                    'type' => \Elementor\Controls_Manager::SLIDER,
                    'size_units' => ['px'],
                    'range' => [
                        'px' => [
                            'min' => 14,
                            'max' => 36,
                        ],
                    ],
                    'default' => [
                        'unit' => 'px',
                        'size' => 20,
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .mlf-stat-count' => 'font-size: {{SIZE}}{{UNIT}}',
                    ],
                ]
            );
            
            $this->add_responsive_control(
                'mlf_stats_label_size',
                [
                    'label' => __('Stats Label Size', 'mlf-pro'),
                    'type' => \Elementor\Controls_Manager::SLIDER,
                    'size_units' => ['px'],
                    'range' => [
                        'px' => [
                            'min' => 8,
                            'max' => 16,
                        ],
                    ],
                    'default' => [
                        'unit' => 'px',
                        'size' => 11,
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .mlf-stat-label' => 'font-size: {{SIZE}}{{UNIT}}',
                    ],
                ]
            );
            
            $this->end_controls_section();
            
            // =====================
            // GRID COLUMNS SECTION
            // =====================
            $this->start_controls_section(
                'grid_section',
                [
                    'label' => __('Grid Columns', 'mlf-pro'),
                    'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                ]
            );
            
            $this->add_responsive_control(
                'mlf_columns',
                [
                    'label' => __('Columns', 'mlf-pro'),
                    'type' => \Elementor\Controls_Manager::SELECT,
                    'default' => '3',
                    'options' => [
                        '1' => __('1 Column', 'mlf-pro'),
                        '2' => __('2 Columns', 'mlf-pro'),
                        '3' => __('3 Columns', 'mlf-pro'),
                        '4' => __('4 Columns', 'mlf-pro'),
                        '5' => __('5 Columns', 'mlf-pro'),
                        '6' => __('6 Columns', 'mlf-pro'),
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .mlf-elementor-grid' => 'grid-template-columns: repeat({{VALUE}}, 1fr)',
                    ],
                ]
            );
            
            $this->end_controls_section();
        }
        
        protected function render() {
            $settings = $this->get_settings_for_display();
            
            // Get plugin settings as fallbacks
            $primary_color = !empty($settings['mlf_primary_color']) ? $settings['mlf_primary_color'] : get_option('mlf_primary_color', '#95160c');
            $card_gap = !empty($settings['mlf_gap']['size']) ? $settings['mlf_gap']['size'] : get_option('mlf_card_gap', '20px');
            
            // Build query args
            $args = [
                'post_type' => 'job_listing',
                'numberposts' => $settings['mlf_posts_per_page'],
            ];
            
            if (!empty($settings['mlf_post_status'])) {
                $args['post_status'] = $settings['mlf_post_status'];
            }
            
            $posts = get_posts($args);
            
            // Get stats
            $all_posts = get_posts([
                'post_type' => 'job_listing',
                'numberposts' => -1,
            ]);
            
            $pending = 0;
            $publish = 0;
            $draft = 0;
            
            foreach($all_posts as $p){
                if($p->post_status == 'pending') $pending++;
                elseif($p->post_status == 'publish') $publish++;
                elseif($p->post_status == 'draft') $draft++;
            }
            
            // Get settings with defaults
            $container_bg = !empty($settings['mlf_container_bg']) ? $settings['mlf_container_bg'] : '#f5f5f5';
            $container_padding = !empty($settings['mlf_container_padding']) ? $settings['mlf_container_padding'] : ['top' => '20', 'right' => '20', 'bottom' => '20', 'left' => '20'];
            $container_radius = !empty($settings['mlf_container_radius']) ? $settings['mlf_container_radius'] : ['top' => '12', 'right' => '12', 'bottom' => '12', 'left' => '12'];
            $card_bg = !empty($settings['mlf_card_bg']) ? $settings['mlf_card_bg'] : '#ffffff';
            $card_border = !empty($settings['mlf_card_border_color']) ? $settings['mlf_card_border_color'] : '#e8e8e8';
            $card_padding = !empty($settings['mlf_card_padding']) ? $settings['mlf_card_padding'] : ['top' => '20', 'right' => '20', 'bottom' => '20', 'left' => '20'];
            $card_radius = !empty($settings['mlf_card_radius']) ? $settings['mlf_card_radius'] : ['top' => '12', 'right' => '12', 'bottom' => '12', 'left' => '12'];
            $avatar_bg = !empty($settings['mlf_avatar_bg']) ? $settings['mlf_avatar_bg'] : $primary_color;
            $avatar_color = !empty($settings['mlf_avatar_color']) ? $settings['mlf_avatar_color'] : '#ffffff';
            $avatar_size = !empty($settings['mlf_avatar_size']['size']) ? $settings['mlf_avatar_size']['size'] : 50;
            $avatar_margin = !empty($settings['mlf_avatar_margin_bottom']['size']) ? $settings['mlf_avatar_margin_bottom']['size'] : 12;
            $title_size = !empty($settings['mlf_title_font_size']['size']) ? $settings['mlf_title_font_size']['size'] : 24;
            $title_weight = !empty($settings['mlf_title_font_weight']) ? $settings['mlf_title_font_weight'] : '600';
            $title_color = !empty($settings['mlf_title_color']) ? $settings['mlf_title_color'] : '#333333';
            $card_title_size = !empty($settings['mlf_card_title_size']['size']) ? $settings['mlf_card_title_size']['size'] : 16;
            $meta_size = !empty($settings['mlf_meta_text_size']['size']) ? $settings['mlf_meta_text_size']['size'] : 13;
            $meta_color = !empty($settings['mlf_meta_color']) ? $settings['mlf_meta_color'] : '#666666';
            $columns = !empty($settings['mlf_columns']) ? $settings['mlf_columns'] : 3;
            $badge_padding = !empty($settings['mlf_badge_padding']) ? $settings['mlf_badge_padding'] : ['top' => '4', 'right' => '12', 'bottom' => '4', 'left' => '12'];
            $badge_radius = !empty($settings['mlf_badge_radius']['size']) ? $settings['mlf_badge_radius']['size'] : 20;
            $badge_size = !empty($settings['mlf_badge_font_size']['size']) ? $settings['mlf_badge_font_size']['size'] : 11;
            $badge_publish_bg = !empty($settings['mlf_badge_publish_bg']) ? $settings['mlf_badge_publish_bg'] : '#d1fae5';
            $badge_publish_color = !empty($settings['mlf_badge_publish_color']) ? $settings['mlf_badge_publish_color'] : '#065f46';
            $badge_pending_bg = !empty($settings['mlf_badge_pending_bg']) ? $settings['mlf_badge_pending_bg'] : '#fef3c7';
            $badge_pending_color = !empty($settings['mlf_badge_pending_color']) ? $settings['mlf_badge_pending_color'] : '#92400e';
            $badge_draft_bg = !empty($settings['mlf_badge_draft_bg']) ? $settings['mlf_badge_draft_bg'] : '#f3f4f6';
            $badge_draft_color = !empty($settings['mlf_badge_draft_color']) ? $settings['mlf_badge_draft_color'] : '#6b7280';
            $stats_bg = !empty($settings['mlf_stats_bg']) ? $settings['mlf_stats_bg'] : '#ffffff';
            $stats_padding = !empty($settings['mlf_stats_padding']) ? $settings['mlf_stats_padding'] : ['top' => '12', 'right' => '20', 'bottom' => '12', 'left' => '20'];
            $stats_radius = !empty($settings['mlf_stats_radius']['size']) ? $settings['mlf_stats_radius']['size'] : 8;
            $stats_count_size = !empty($settings['mlf_stats_count_size']['size']) ? $settings['mlf_stats_count_size']['size'] : 20;
            $stats_label_size = !empty($settings['mlf_stats_label_size']['size']) ? $settings['mlf_stats_label_size']['size'] : 11;
            
            ?>
            <div class="mlf-elementor-container" data-mlf-elementor="true" style="background: <?php echo $container_bg; ?>; padding: <?php echo $container_padding['top']; ?>px <?php echo $container_padding['right']; ?>px <?php echo $container_padding['bottom']; ?>px <?php echo $container_padding['left']; ?>px; border-radius: <?php echo $container_radius['top']; ?>px;">
                
                <!-- Header -->
                <div class="mlf-elementor-header" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 25px; flex-wrap: wrap; gap: 15px;">
                    <h2 class="mlf-elementor-title" style="font-size: <?php echo $title_size; ?>px; font-weight: <?php echo $title_weight; ?>; color: <?php echo $title_color; ?>; margin: 0;"><?php echo esc_html($settings['mlf_title']); ?></h2>
                    
                    <?php if($settings['mlf_show_stats'] === 'yes'): ?>
                    <div class="mlf-elementor-stats" style="display: flex; gap: 15px; flex-wrap: wrap;">
                        <div class="mlf-stat-item" style="background: <?php echo $stats_bg; ?>; padding: <?php echo $stats_padding['top']; ?>px <?php echo $stats_padding['right']; ?>px <?php echo $stats_padding['bottom']; ?>px <?php echo $stats_padding['left']; ?>px; border-radius: <?php echo $stats_radius; ?>px; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                            <span class="mlf-stat-count" style="font-size: <?php echo $stats_count_size; ?>px; font-weight: bold; color: <?php echo $primary_color; ?>;"><?php echo count($all_posts); ?></span>
                            <span class="mlf-stat-label" style="display: block; font-size: <?php echo $stats_label_size; ?>px; color: #666; margin-top: 4px;">Total</span>
                        </div>
                        <div class="mlf-stat-item" style="background: <?php echo $stats_bg; ?>; padding: <?php echo $stats_padding['top']; ?>px <?php echo $stats_padding['right']; ?>px <?php echo $stats_padding['bottom']; ?>px <?php echo $stats_padding['left']; ?>px; border-radius: <?php echo $stats_radius; ?>px; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                            <span class="mlf-stat-count" style="font-size: <?php echo $stats_count_size; ?>px; font-weight: bold; color: #f59e0b;"><?php echo $pending; ?></span>
                            <span class="mlf-stat-label" style="display: block; font-size: <?php echo $stats_label_size; ?>px; color: #666; margin-top: 4px;">Pending</span>
                        </div>
                        <div class="mlf-stat-item" style="background: <?php echo $stats_bg; ?>; padding: <?php echo $stats_padding['top']; ?>px <?php echo $stats_padding['right']; ?>px <?php echo $stats_padding['bottom']; ?>px <?php echo $stats_padding['left']; ?>px; border-radius: <?php echo $stats_radius; ?>px; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                            <span class="mlf-stat-count" style="font-size: <?php echo $stats_count_size; ?>px; font-weight: bold; color: #10b981;"><?php echo $publish; ?></span>
                            <span class="mlf-stat-label" style="display: block; font-size: <?php echo $stats_label_size; ?>px; color: #666; margin-top: 4px;">Published</span>
                        </div>
                        <div class="mlf-stat-item" style="background: <?php echo $stats_bg; ?>; padding: <?php echo $stats_padding['top']; ?>px <?php echo $stats_padding['right']; ?>px <?php echo $stats_padding['bottom']; ?>px <?php echo $stats_padding['left']; ?>px; border-radius: <?php echo $stats_radius; ?>px; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                            <span class="mlf-stat-count" style="font-size: <?php echo $stats_count_size; ?>px; font-weight: bold; color: #6b7280;"><?php echo $draft; ?></span>
                            <span class="mlf-stat-label" style="display: block; font-size: <?php echo $stats_label_size; ?>px; color: #666; margin-top: 4px;">Draft</span>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Grid -->
                <div class="mlf-elementor-grid" style="display: grid; grid-template-columns: repeat(<?php echo $columns; ?>, 1fr); gap: <?php echo $card_gap; ?>px;">
                    <?php if(empty($posts)): ?>
                        <div class="mlf-elementor-empty" style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #666;">
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
                            
                            // Badge colors based on status
                            $badge_bg = $status_class == 'publish' ? $badge_publish_bg : ($status_class == 'pending' ? $badge_pending_bg : $badge_draft_bg);
                            $badge_text_color = $status_class == 'publish' ? $badge_publish_color : ($status_class == 'pending' ? $badge_pending_color : $badge_draft_color);
                        ?>
                        <div class="mlf-elementor-card" data-id="<?php echo $p->ID; ?>" style="background: <?php echo $card_bg; ?>; border: 1px solid <?php echo $card_border; ?>; border-radius: <?php echo $card_radius['top']; ?>px; padding: <?php echo $card_padding['top']; ?>px <?php echo $card_padding['right']; ?>px <?php echo $card_padding['bottom']; ?>px <?php echo $card_padding['left']; ?>px; cursor: pointer; transition: all 0.3s ease;">
                            <div class="mlf-elementor-card-content" onclick="if (!event.target.closest('a')) mlfOpenDetail(<?php echo $p->ID; ?>)">
                                <div class="mlf-elementor-avatar" style="width: <?php echo $avatar_size; ?>px; height: <?php echo $avatar_size; ?>px; border-radius: 50%; background: <?php echo $avatar_bg; ?>; color: <?php echo $avatar_color; ?>; display: flex; align-items: center; justify-content: center; font-size: <?php echo $avatar_size * 0.36; ?>px; font-weight: 600; margin-bottom: <?php echo $avatar_margin; ?>px;"><?php echo $initial; ?></div>
                                <h3 class="mlf-elementor-card-title" style="font-size: <?php echo $card_title_size; ?>px; font-weight: 600; color: <?php echo $title_color; ?>; margin: 0 0 8px 0;"><?php echo esc_html($name); ?></h3>
                                <?php if($company): ?>
                                <p class="mlf-elementor-card-company" style="font-size: <?php echo $meta_size; ?>px; color: <?php echo $meta_color; ?>; margin: 0 0 6px 0;"><i class="fas fa-building"></i> <?php echo esc_html($company); ?></p>
                                <?php endif; ?>
                                <p class="mlf-elementor-card-email" style="font-size: <?php echo $meta_size; ?>px; color: <?php echo $meta_color; ?>; margin: 0 0 6px 0;"><?php echo $email ? esc_html($email) : ''; ?></p>
                                <?php if($phone): ?>
                                <p class="mlf-elementor-card-phone" style="font-size: <?php echo $meta_size; ?>px; color: <?php echo $meta_color; ?>; margin: 0 0 6px 0;"><?php echo esc_html($phone); ?></p>
                                <?php endif; ?>
                                <?php if($location): ?>
                                <p class="mlf-elementor-card-location" style="font-size: <?php echo $meta_size; ?>px; color: <?php echo $meta_color; ?>; margin: 0 0 12px 0;"><?php echo esc_html($location); ?></p>
                                <?php endif; ?>
                                <span class="mlf-elementor-status status-badge <?php echo $status_class; ?>" style="display: inline-block; padding: <?php echo $badge_padding['top']; ?>px <?php echo $badge_padding['right']; ?>px <?php echo $badge_padding['bottom']; ?>px <?php echo $badge_padding['left']; ?>px; border-radius: <?php echo $badge_radius; ?>px; font-size: <?php echo $badge_size; ?>px; font-weight: 500; background: <?php echo $badge_bg; ?>; color: <?php echo $badge_text_color; ?>;"><?php echo $status_label; ?></span>
                            </div>
                            <div class="mlf-elementor-card-actions" onclick="event.stopPropagation();" style="display: flex; gap: 8px; margin-top: 15px; padding-top: 15px; border-top: 1px solid #eee; flex-wrap: wrap;">
                                <?php if($p->post_status != 'publish'): ?>
                                <button class="mlf-card-btn mlf-btn-approve" onclick="mlfCardAction(<?php echo $p->ID; ?>, 'approve')" style="flex: 1; min-width: 70px; padding: 8px 12px; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; background: #28a745; color: #fff;">✓ Approve</button>
                                <?php endif; ?>
                                <?php if($p->post_status != 'draft'): ?>
                                <button class="mlf-card-btn mlf-btn-reject" onclick="mlfCardAction(<?php echo $p->ID; ?>, 'reject')" style="flex: 1; min-width: 70px; padding: 8px 12px; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; background: #ffc107; color: #333;">✗ Reject</button>
                                <?php endif; ?>
                                <button class="mlf-card-btn mlf-btn-delete" onclick="mlfCardAction(<?php echo $p->ID; ?>, 'trash')" style="flex: 1; min-width: 70px; padding: 8px 12px; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; background: #dc3545; color: #fff;">🗑 Delete</button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
            </div>
            <?php
        }
        
        protected function _content_template() {
            ?>
            <div class="mlf-elementor-container" style="background: #f5f5f5; padding: 20px; border-radius: 12px;">
                <div class="mlf-elementor-header">
                    <h2 class="mlf-elementor-title" style="font-size: 24px; font-weight: 600; color: #333; margin: 0 0 20px 0;">{{{ settings.mlf_title }}}</h2>
                    <# if(settings.mlf_show_stats === 'yes'){ #>
                    <div class="mlf-elementor-stats" style="display: flex; gap: 15px; margin-bottom: 25px;">
                        <div class="mlf-stat-item" style="background: #fff; padding: 12px 20px; border-radius: 8px; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.08);"><span class="mlf-stat-count" style="font-size: 20px; font-weight: bold; color: #95160c;">12</span><span class="mlf-stat-label" style="display: block; font-size: 11px; color: #666; margin-top: 4px;">Total</span></div>
                        <div class="mlf-stat-item" style="background: #fff; padding: 12px 20px; border-radius: 8px; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.08);"><span class="mlf-stat-count" style="font-size: 20px; font-weight: bold; color: #f59e0b;">3</span><span class="mlf-stat-label" style="display: block; font-size: 11px; color: #666; margin-top: 4px;">Pending</span></div>
                        <div class="mlf-stat-item" style="background: #fff; padding: 12px 20px; border-radius: 8px; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.08);"><span class="mlf-stat-count" style="font-size: 20px; font-weight: bold; color: #10b981;">8</span><span class="mlf-stat-label" style="display: block; font-size: 11px; color: #666; margin-top: 4px;">Published</span></div>
                        <div class="mlf-stat-item" style="background: #fff; padding: 12px 20px; border-radius: 8px; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.08);"><span class="mlf-stat-count" style="font-size: 20px; font-weight: bold; color: #6b7280;">1</span><span class="mlf-stat-label" style="display: block; font-size: 11px; color: #666; margin-top: 4px;">Draft</span></div>
                    </div>
                    <# } #>
                </div>
                <div class="mlf-elementor-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
                    <div class="mlf-elementor-card" style="background: #fff; border: 1px solid #e8e8e8; border-radius: 12px; padding: 20px; cursor: pointer; transition: all 0.3s ease;">
                        <div class="mlf-elementor-avatar" style="width: 50px; height: 50px; border-radius: 50%; background: #95160c; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: 600; margin-bottom: 12px;">J</div>
                        <h3 class="mlf-elementor-card-title" style="font-size: 16px; font-weight: 600; color: #333; margin: 0 0 8px 0;">John Developer</h3>
                        <p class="mlf-elementor-card-email" style="font-size: 13px; color: #666; margin: 0 0 12px 0;">john@example.com</p>
                        <span class="mlf-elementor-status status-badge publish" style="display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 500; background: #d1fae5; color: #065f46;">Published</span>
                    </div>
                    <div class="mlf-elementor-card" style="background: #fff; border: 1px solid #e8e8e8; border-radius: 12px; padding: 20px; cursor: pointer; transition: all 0.3s ease;">
                        <div class="mlf-elementor-avatar" style="width: 50px; height: 50px; border-radius: 50%; background: #95160c; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: 600; margin-bottom: 12px;">S</div>
                        <h3 class="mlf-elementor-card-title" style="font-size: 16px; font-weight: 600; color: #333; margin: 0 0 8px 0;">Sarah Designer</h3>
                        <p class="mlf-elementor-card-email" style="font-size: 13px; color: #666; margin: 0 0 12px 0;">sarah@example.com</p>
                        <span class="mlf-elementor-status status-badge publish" style="display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 500; background: #d1fae5; color: #065f46;">Published</span>
                    </div>
                    <div class="mlf-elementor-card" style="background: #fff; border: 1px solid #e8e8e8; border-radius: 12px; padding: 20px; cursor: pointer; transition: all 0.3s ease;">
                        <div class="mlf-elementor-avatar" style="width: 50px; height: 50px; border-radius: 50%; background: #95160c; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: 600; margin-bottom: 12px;">M</div>
                        <h3 class="mlf-elementor-card-title" style="font-size: 16px; font-weight: 600; color: #333; margin: 0 0 8px 0;">Mike Manager</h3>
                        <p class="mlf-elementor-card-email" style="font-size: 13px; color: #666; margin: 0 0 12px 0;">mike@example.com</p>
                        <span class="mlf-elementor-status status-badge pending" style="display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 500; background: #fef3c7; color: #92400e;">Pending</span>
                    </div>
                </div>
            </div>
            <?php
        }
    }
    
    // Register the widget
    $widgets_manager->register_widget_type(new MLF_Elementor_Widget());
});

// Enqueue styles for Elementor
add_action('elementor/frontend/before_register_scripts', function() {
    wp_enqueue_style('mlf-style', plugin_dir_url(__FILE__) . 'assets/style.css');
});