<?php
/**
 * Plugin Name: Custom JS&CSS in Head 
 * Description: Adds a custom JS&CSS meta box to the post editor and inserts the JS code into the head section.
 * Version: 1.2
 * Author: seojacky
 * Author URI: https://github.com/seojacky/
 * Plugin URI: https://github.com/seojacky/custom-js-css-head/
 * GitHub Plugin URI: https://github.com/seojacky/custom-js-css-head
 * Text Domain: custom-js-css-head
 * Domain Path: /languages
 * License: GPL2
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class Custom_JS_Head_Multilingual {
    
    // Meta key to store the custom JS
    private $meta_key = '_custom_js_head';
    
    /**
     * Constructor
     */
    public function __construct() {
        // Add meta box to post edit screen
        add_action('add_meta_boxes', array($this, 'add_meta_box'));
        
        // Save meta box data
        add_action('save_post', array($this, 'save_meta_box'), 10, 2);
        
        // Add custom JS to head
        add_action('wp_head', array($this, 'output_custom_js'), 11);
        
        // Add WPGlobus compatibility if active
        if ($this->is_wpglobus_active()) {
            // Add support for multilingual meta fields
            add_filter('wpglobus_multilingual_meta_keys', array($this, 'add_multilingual_meta_key'));
        }
        
        // Enqueue admin scripts
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }
    
    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        // Only load on post edit screens
        if ($hook != 'post.php' && $hook != 'post-new.php') {
            return;
        }
        
        // Register and enqueue custom script
        wp_enqueue_script(
            'custom-js-head-multilingual', 
            plugin_dir_url(__FILE__) . 'js/custom-js-head.js', 
            array('jquery'), 
            '1.0.0', 
            true
        );
        
        // Create directory if not exists
        if (!file_exists(plugin_dir_path(__FILE__) . 'js')) {
            mkdir(plugin_dir_path(__FILE__) . 'js', 0755, true);
        }
        
        // Create JS file if not exists
        $js_file = plugin_dir_path(__FILE__) . 'js/custom-js-head.js';
        if (!file_exists($js_file)) {
            file_put_contents($js_file, $this->get_admin_js());
        }
    }
    
    /**
     * Get admin JS content
     * 
     * @return string JavaScript code
     */
    private function get_admin_js() {
        return "
jQuery(document).ready(function($) {
    // Handle tab clicks for custom JS head metabox
    $(document).on('click', '.custom-js-head-tabs a', function(e) {
        e.preventDefault();
        
        var targetLang = $(this).data('language');
        
        // Hide all tab contents
        $('.custom-js-head-content').hide();
        
        // Show the selected tab content
        $('#custom-js-head-content-' + targetLang).show();
        
        // Update active tab
        $('.custom-js-head-tabs a').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');
        
        // Store the active tab in local storage
        if (window.localStorage) {
            localStorage.setItem('custom_js_head_active_tab', targetLang);
        }
        
        return false;
    });
    
    // Initialize tabs - select the active tab from local storage or URL
    function initCustomJsHeadTabs() {
        // Check if we have a language in the URL hash
        var hashLang = '';
        if (window.location.hash) {
            var matches = window.location.hash.match(/custom-js-tab-(\\w+)/);
            if (matches && matches.length > 1) {
                hashLang = matches[1];
            }
        }
        
        // Check if we have a stored active tab
        var storedLang = '';
        if (window.localStorage) {
            storedLang = localStorage.getItem('custom_js_head_active_tab');
        }
        
        // Determine which tab to activate (priority: hash > stored > first tab)
        var activeLang = hashLang || storedLang || $('.custom-js-head-tabs a').first().data('language');
        
        // Trigger click on the appropriate tab
        $('.custom-js-head-tabs a[data-language=\"' + activeLang + '\"]').trigger('click');
    }
    
    // Initialize tabs
    initCustomJsHeadTabs();
    
    // Handle language switcher in WPGlobus admin bar
    $(document).on('click', '.wpglobus-selector-link', function() {
        // Get the target language
        var targetLang = $(this).attr('href').match(/language=(\\w+)/)[1];
        
        // Switch the custom JS head tab if it exists
        if ($('.custom-js-head-tabs a[data-language=\"' + targetLang + '\"]').length) {
            setTimeout(function() {
                $('.custom-js-head-tabs a[data-language=\"' + targetLang + '\"]').trigger('click');
            }, 100);
        }
    });
});
        ";
    }
    
    /**
     * Check if WPGlobus is active
     * 
     * @return bool
     */
    private function is_wpglobus_active() {
        return class_exists('WPGlobus') && class_exists('WPGlobus_Core');
    }
    
    /**
     * Add our meta key to the list of multilingual meta keys
     * 
     * @param array $meta_keys The current multilingual meta keys
     * @return array The updated multilingual meta keys
     */
    public function add_multilingual_meta_key($meta_keys) {
        $meta_keys[] = $this->meta_key;
        return $meta_keys;
    }
    
    /**
     * Add meta box to post edit screen
     */
    public function add_meta_box() {
        $post_types = get_post_types(array('public' => true));
        
        foreach ($post_types as $post_type) {
            add_meta_box(
                'custom_js_head_box',
                'Custom JS for Head Section',
                array($this, 'render_meta_box'),
                $post_type,
                'normal',
                'high'
            );
        }
    }
    
    /**
     * Render meta box content
     * 
     * @param WP_Post $post The post object
     */
    public function render_meta_box($post) {
        // Add nonce for security
        wp_nonce_field('custom_js_head_box', 'custom_js_head_nonce');
        
        // Get saved value
        $value = get_post_meta($post->ID, $this->meta_key, true);
        
        // If WPGlobus is active, add language tabs
        if ($this->is_wpglobus_active()) {
            // Get available languages
            $languages = WPGlobus::Config()->enabled_languages;
            $default_language = WPGlobus::Config()->default_language;
            
            // Output language tabs with improved styling
            ?>
            <style>
                .custom-js-head-tabs {
                    margin-bottom: 10px;
                    border-bottom: 1px solid #ccc;
                }
                .custom-js-head-tabs .nav-tab {
                    margin-left: 0;
                    margin-right: 5px;
                    font-size: 14px;
                    padding: 5px 10px;
                }
                .custom-js-head-tabs .nav-tab-active {
                    background: #fff;
                    border-bottom: 1px solid #fff;
                }
                .custom-js-head-content {
                    display: none;
                }
                .wpglobus-icon {
                    display: inline-block;
                    width: 16px;
                    height: 11px;
                    margin-right: 5px;
                    vertical-align: middle;
                    background-repeat: no-repeat;
                }
                <?php foreach ($languages as $language): ?>
                .wpglobus-icon-<?php echo esc_attr($language); ?> {
                    background-image: url(<?php echo esc_url(WPGlobus::Config()->flags_url . WPGlobus::Config()->flag[$language]); ?>);
                }
                <?php endforeach; ?>
            </style>
            
            <div class="custom-js-head-tabs nav-tab-wrapper">
                <?php foreach ($languages as $language): ?>
                    <a href="#custom-js-tab-<?php echo esc_attr($language); ?>" 
                       data-language="<?php echo esc_attr($language); ?>" 
                       class="nav-tab">
                        <span class="wpglobus-icon wpglobus-icon-<?php echo esc_attr($language); ?>"></span>
                        <?php echo esc_html(WPGlobus::Config()->en_language_name[$language]); ?>
                    </a>
                <?php endforeach; ?>
            </div>
            
            <?php foreach ($languages as $language): ?>
                <div id="custom-js-head-content-<?php echo esc_attr($language); ?>" class="custom-js-head-content">
                    <textarea 
                        name="custom_js_head_field[<?php echo esc_attr($language); ?>]" 
                        id="custom_js_head_field_<?php echo esc_attr($language); ?>" 
                        class="widefat code" 
                        rows="15" 
                        style="font-family: monospace;"
                    ><?php echo esc_textarea(WPGlobus_Core::text_filter($value, $language)); ?></textarea>
                </div>
            <?php endforeach; ?>
            
            <p class="description">
                Example: Schema.org markup, custom tracking code, etc. This will be added to the &lt;head&gt; section with priority 11.
            </p>
            <?php
        } else {
            // Output field without language tabs
            ?>
            <p>
                <label for="custom_js_head_field">Insert custom JavaScript that will be added to the &lt;head&gt; section of this page:</label>
            </p>
            <textarea 
                name="custom_js_head_field" 
                id="custom_js_head_field" 
                class="widefat code" 
                rows="15" 
                style="font-family: monospace;"
            ><?php echo esc_textarea($value); ?></textarea>
            <p class="description">
                Example: Schema.org markup, custom tracking code, etc. This will be added to the &lt;head&gt; section with priority 11.
            </p>
            <?php
        }
    }
    
    /**
     * Save meta box data
     * 
     * @param int $post_id The post ID
     * @param WP_Post $post The post object
     */
    public function save_meta_box($post_id, $post) {
        // Check if nonce is set
        if (!isset($_POST['custom_js_head_nonce'])) {
            return;
        }
        
        // Verify nonce
        if (!wp_verify_nonce($_POST['custom_js_head_nonce'], 'custom_js_head_box')) {
            return;
        }
        
        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Check user permissions
        if ('page' === $post->post_type) {
            if (!current_user_can('edit_page', $post_id)) {
                return;
            }
        } else {
            if (!current_user_can('edit_post', $post_id)) {
                return;
            }
        }
        
        // If WPGlobus is active, save multilingual content
        if ($this->is_wpglobus_active()) {
            if (isset($_POST['custom_js_head_field']) && is_array($_POST['custom_js_head_field'])) {
                $custom_js_fields = $_POST['custom_js_head_field'];
                $custom_js_multilingual = '';
                
                foreach ($custom_js_fields as $language => $content) {
                    if (!empty($content)) {
                        $custom_js_multilingual .= '{:' . $language . '}' . $content . '{:}';
                    }
                }
                
                // Save the combined multilingual content
                update_post_meta($post_id, $this->meta_key, $custom_js_multilingual);
            }
        } else {
            // Standard save for non-WPGlobus sites
            if (isset($_POST['custom_js_head_field'])) {
                $custom_js = is_array($_POST['custom_js_head_field']) ? 
                    reset($_POST['custom_js_head_field']) : $_POST['custom_js_head_field'];
                update_post_meta($post_id, $this->meta_key, $custom_js);
            }
        }
    }
    
    /**
     * Output custom JS in head section
     */
    public function output_custom_js() {
        if (is_singular()) {
            $post_id = get_the_ID();
            $custom_js = get_post_meta($post_id, $this->meta_key, true);
            
            if (!empty($custom_js)) {
                // If WPGlobus is active, get content for current language
                if ($this->is_wpglobus_active()) {
                    $current_language = WPGlobus::Config()->language;
                    $custom_js = WPGlobus_Core::text_filter($custom_js, $current_language);
                    
                    // Process multilingual content in JSON fields recursively if needed
                    $custom_js = $this->process_multilingual_json($custom_js, $current_language);
                }
                
                if (!empty($custom_js)) {
                    echo $custom_js;
                }
            }
        }
    }
    
    /**
     * Process multilingual content in JSON-LD
     * 
     * @param string $json_string The JSON string
     * @param string $current_language The current language
     * @return string The processed JSON string
     */
    private function process_multilingual_json($json_string, $current_language) {
        // Try to detect if it's a JSON-LD script
        if (strpos($json_string, '<script type="application/ld+json">') !== false) {
            // Extract JSON from script tags
            preg_match('/<script type="application\/ld\+json">(.*?)<\/script>/s', $json_string, $matches);
            
            if (isset($matches[1])) {
                $json_content = $matches[1];
                $is_valid_json = false;
                
                // Try to decode JSON
                $json_data = json_decode($json_content, true);
                
                if (json_last_error() === JSON_ERROR_NONE) {
                    $is_valid_json = true;
                    
                    // Process JSON data recursively
                    $this->process_multilingual_json_data($json_data, $current_language);
                    
                    // Encode back to JSON
                    $processed_json = json_encode($json_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                    
                    // Replace original JSON in the script tag
                    $json_string = str_replace($json_content, $processed_json, $json_string);
                }
            }
        }
        
        return $json_string;
    }
    
    /**
     * Process multilingual content in JSON data
     * 
     * @param array &$data The JSON data by reference
     * @param string $current_language The current language
     */
    private function process_multilingual_json_data(&$data, $current_language) {
        if (is_array($data)) {
            foreach ($data as $key => &$value) {
                if (is_array($value)) {
                    $this->process_multilingual_json_data($value, $current_language);
                } elseif (is_string($value) && strpos($value, '{:') !== false) {
                    $value = WPGlobus_Core::text_filter($value, $current_language);
                }
            }
        }
    }
}

// Initialize the plugin
new Custom_JS_Head_Multilingual();