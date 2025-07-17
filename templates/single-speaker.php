<?php
get_header();
the_post();
do_action('mep_before_events_speaker_wrapper');

// Get speaker meta data
$speaker_phone = get_post_meta(get_the_ID(), 'mep_speaker_phone', true);
$speaker_email = get_post_meta(get_the_ID(), 'mep_speaker_email', true);
$speaker_website = get_post_meta(get_the_ID(), 'mep_speaker_website', true);
$speaker_designation = get_post_meta(get_the_ID(), 'mep_speaker_designation', true);
$speaker_company = get_post_meta(get_the_ID(), 'mep_speaker_company', true);
?>
<div class="mep_events_speaker_wraper mep_corporate_speaker_layout">
    <!-- Hero Section -->
    <div class="mep_speaker_hero_section">
        <div class="mep_speaker_hero_content">
            <div class="mep_speaker_avatar_section">
                <div class="mep_speaker_thumbnail_corporate">
                    <?php if (has_post_thumbnail()) : ?>
                        <?php the_post_thumbnail('full', array('class' => 'mep_speaker_avatar')); ?>
                    <?php else : ?>
                        <div class="mep_speaker_placeholder">
                            <i class="fas fa-user"></i>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="mep_speaker_info_section">
                <div class="mep_speaker_name_corporate">
                    <h1><?php the_title(); ?></h1>
                    <?php if ($speaker_designation) : ?>
                        <p class="mep_speaker_designation"><?php echo esc_html($speaker_designation); ?></p>
                    <?php endif; ?>
                    <?php if ($speaker_company) : ?>
                        <p class="mep_speaker_company"><?php echo esc_html($speaker_company); ?></p>
                    <?php endif; ?>
                </div>
                
                <!-- Contact Information -->
                <div class="mep_speaker_contact_info">
                    <?php if ($speaker_phone) : ?>
                        <div class="mep_contact_item">
                            <i class="fas fa-phone"></i>
                            <span class="mep_contact_label">Mobile/Phone:</span>
                            <span class="mep_contact_value"><?php echo esc_html($speaker_phone); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($speaker_email) : ?>
                        <div class="mep_contact_item">
                            <i class="fas fa-envelope"></i>
                            <span class="mep_contact_label">Email:</span>
                            <span class="mep_contact_value"><?php echo esc_html($speaker_email); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($speaker_website) : ?>
                        <div class="mep_contact_item">
                            <i class="fas fa-globe"></i>
                            <span class="mep_contact_label">Website:</span>
                            <span class="mep_contact_value"><?php echo esc_html($speaker_website); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Social Share -->
                <?php 
                $current_url = get_permalink();
                $speaker_title = get_the_title();
                $share_text = sprintf(__('Check out %s - Speaker Profile', 'mage-eventpress'), $speaker_title);
                $encoded_url = urlencode($current_url);
                $encoded_text = urlencode($share_text);
                ?>
                <div class="mep_speaker_social_share">
                    <h4>Social Share:</h4>
                    <div class="mep_social_buttons">
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $encoded_url; ?>" class="mep_social_btn mep_facebook" title="Share on Facebook" target="_blank" rel="noopener">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://twitter.com/intent/tweet?url=<?php echo $encoded_url; ?>&text=<?php echo $encoded_text; ?>" class="mep_social_btn mep_twitter" title="Share on Twitter" target="_blank" rel="noopener">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo $encoded_url; ?>" class="mep_social_btn mep_linkedin" title="Share on LinkedIn" target="_blank" rel="noopener">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                        <a href="https://wa.me/?text=<?php echo $encoded_text; ?>%20<?php echo $encoded_url; ?>" class="mep_social_btn mep_whatsapp" title="Share on WhatsApp" target="_blank" rel="noopener">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Speaker Bio Section -->
    <?php if (get_the_content()) : ?>
    <div class="mep_speaker_bio_section">
        <div class="mep_speaker_bio_content">
            <?php the_content(); ?>
        </div>
    </div>
    <?php endif; ?>
    <div class='mep_event_list'>
        <div class="mep_cat-details">
            <h3><?php esc_html_e('All Events Of ', 'mage-eventpress');
                the_title(); ?></h3>
        </div>
        <div class='mage_grid_box'>
            <?php
            $paged              = get_query_var("paged") ? get_query_var("paged") : 1;
            $args = array(
                'post_type'         => array('mep_events'),
                'paged'             => $paged,
                'orderby'           => 'meta_value',
                'meta_key'          => 'event_start_datetime',
                'meta_query' => array(
                    array(
                        'key'       => 'mep_event_speakers_list',
                        'value'     => get_the_id(),
                        'compare'   => 'LIKE'
                    )
                )
            );

            $loop = new WP_Query($args);            
            while ($loop->have_posts()) {
                $loop->the_post();
                do_action('mep_event_list_shortcode', get_the_id(), 'three_column', 'grid');
            }
            wp_reset_postdata();
            mep_event_pagination($loop->max_num_pages);
            ?>
        </div>
    </div>
</div>
</div>

<script>
jQuery(document).ready(function($) {
    // Social share button click handlers
    $('.mep_social_btn').on('click', function(e) {
        var url = $(this).attr('href');
        var platform = '';
        
        if ($(this).hasClass('mep_facebook')) {
            platform = 'Facebook';
        } else if ($(this).hasClass('mep_twitter')) {
            platform = 'Twitter';
        } else if ($(this).hasClass('mep_linkedin')) {
            platform = 'LinkedIn';
        } else if ($(this).hasClass('mep_whatsapp')) {
            platform = 'WhatsApp';
            // WhatsApp works better as direct link on mobile
            return true;
        }
        
        // Open in popup for desktop social networks
        if (platform !== 'WhatsApp') {
            e.preventDefault();
            var popup = window.open(url, platform + 'Share', 'width=600,height=400,scrollbars=yes,resizable=yes');
            
            // Focus the popup window
            if (popup) {
                popup.focus();
            }
            
            return false;
        }
    });
    
    // Add hover effects
    $('.mep_social_btn').hover(
        function() {
            $(this).css('transform', 'translateY(-2px)');
        },
        function() {
            $(this).css('transform', 'translateY(0)');
        }
    );
});
</script>

<?php
do_action('mep_after_events_speaker_wrapper');
get_footer();