<?php
/**
 *  Mage People News Update 
 *  @package Mage-EventPress
 *  @since 1.7.3 
 *  @author Shahadat Hossain <raselsha@gmail.com>
 */

    defined('ABSPATH') || exit;
    
    if (!class_exists('Mage_people_news_update')) {
        class Mage_people_news_update {
            public function __construct() {
                add_action('wp_dashboard_setup', array($this, 'add_custom_dashboard_widget'));
            }

            public function add_custom_dashboard_widget() {
                wp_add_dashboard_widget(
                    'mage_people_news_widget',          
                    'Mage-People News and Updates',     
                    array($this, 'render_dashboard_widget')  
                );
            }

            public function render_dashboard_widget() {
                $rss_url = 'https://mage-people.com/category/news/feed/';

                $response = wp_remote_get($rss_url);

                if (is_wp_error($response)) {
                    echo "<p>Unable to fetch blog posts.</p>";
                    return;
                }

                $body = wp_remote_retrieve_body($response);

                $xml = simplexml_load_string($body);

                if (!$xml) {
                    echo "<p>RSS feed not loaded.</p>";
                    return;
                }
                echo "<ul>";

                $count = 0;

                foreach ($xml->channel->item as $item) {
                    if ($count >= 5) break; // Limit to 5 posts

                    $title = esc_html($item->title);
                    $link = esc_url($item->link);
                    $pubDate = date('F j, Y', strtotime($item->pubDate));

                    echo "<li><a href='{$link}' target='_blank'>{$title}</a> <br><small>{$pubDate}</small></li>";

                    $count++;
                }

                echo "</ul>";
                $this->mp_get_release_notes();
                $this->footer_buttons();
            }
            
            public function footer_buttons() {
                ?>
                <div style="margin-top:20px; padding-top:12px; border-top:1px solid #ddd;">
                    <a href="https://mage-people.com" target="_blank" class="button button-primary" style="margin-right:8px;">
                        Website
                    </a>
                    <a href="https://mage-people.com/our-products/" target="_blank" class="button button-secondary" style="margin-right:8px;">
                        Plugins
                    </a>

                    <a href="https://mage-people.com/blog" target="_blank" class="button button-secondary" style="margin-right:8px;">
                        Blog
                    </a>

                    <a href="https://docs.mage-people.com/" target="_blank" class="button button-secondary" style="margin-right:8px;">
                        Docs
                    </a>

                    <a href="https://support.mage-people.com/portal/en/newticket" target="_blank" class="button" style="margin-right:8px;">
                        Support
                    </a>
                </div>
                <?php

            }

            public function mp_get_release_notes() {
            $readme_path = plugin_dir_path(__FILE__) . 'readme.txt';

            if (!file_exists($readme_path)) {
                return "<p>readme.txt not found.</p>";
            }

            $content = file_get_contents($readme_path);

            // Split based on Changelog section
            $parts = explode("== Changelog ==", $content);

            if (count($parts) < 2) {
                return "<p>No changelog found.</p>";
            }

            $changelog = trim($parts[1]);

            // চেঞ্জলগ শুধুমাত্র প্রথম অংশ দেখাতে চাইলে:
            $changelog_lines = explode("\n", $changelog);
            $limited = array_slice($changelog_lines, 0, 20); // first 20 lines

            return "<pre>" . esc_html(implode("\n", $limited)) . "</pre>";
        }


        }
        new Mage_people_news_update();
    }