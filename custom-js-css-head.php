<?php
/**
 * Plugin Name: Custom JS&CSS in Head 
 * Description: Adds a custom JS&CSS meta box to the post editor and inserts the JS code into the head section.
 * Version: 0.5
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

class Custom_JS_Head {
    
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
        
        // Output field
        ?>
        <p>
            <label for="custom_js_head_field">Insert custom JavaScript that will be added to the &lt;head&gt; section of this page:</label>
        </p>
        <textarea 
            name="custom_js_head_field" 
            id="custom_js_head_field" 
            class="widefat code" 
            rows="10" 
            style="font-family: monospace;"
        ><?php echo esc_textarea($value); ?></textarea>
        <p class="description">
            Example: Schema.org markup, custom tracking code, etc. This will be added to the &lt;head&gt; section with priority 11.
        </p>
        <?php
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
        
        // Check if field is set
        if (!isset($_POST['custom_js_head_field'])) {
            return;
        }
        
        // Sanitize and save data
        $custom_js = $_POST['custom_js_head_field'];
        update_post_meta($post_id, $this->meta_key, $custom_js);
    }
    
    /**
     * Output custom JS in head section
     */
    public function output_custom_js() {
        if (is_singular()) {
            $post_id = get_the_ID();
            $custom_js = get_post_meta($post_id, $this->meta_key, true);
            
            if (!empty($custom_js)) {
                echo $custom_js;
            }
        }
    }
}

// Initialize the plugin
new Custom_JS_Head();
