<?php
/**
 * Plugin Name: Conditional Email Content
 * Description: This plugin adds a shortcode [conditional_email field="your-email"] to conditionally display content in Elementor email templates based on the presence of form field data.
 * Version: 1.0.0
 * Author: Sameer Kazmi
 * License: GPL2
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Enqueue style for the admin page
function my_plugin_enqueue_admin_styles() {
    // Only enqueue on the plugin settings page
    $screen = get_current_screen();
    if ($screen->id === 'toplevel_page_conditional-plugin-settings') {
        wp_enqueue_style(
            'my-plugin-admin-style', // Handle for the stylesheet
            plugin_dir_url(__FILE__) . 'assets/style.css', // Path to the CSS file
            array(), // Dependencies (leave empty if none)
            '1.0', // Version number
            'all' // Media type
        );
    }
}
add_action('admin_enqueue_scripts', 'my_plugin_enqueue_admin_styles');

// Hook to run on plugin activation
function my_plugin_activate() {
    // Set a transient to detect that the plugin was just activated
    set_transient('my_plugin_redirect_on_activation', true, 30);
}
register_activation_hook(__FILE__, 'my_plugin_activate');

// Redirect to settings page after activation
function my_plugin_redirect_after_activation() {
    // Check if the transient is set and the user is in the admin dashboard
    if (get_transient('my_plugin_redirect_on_activation')) {
        // Delete the transient to prevent repeated redirection
        delete_transient('my_plugin_redirect_on_activation');
        
        // Redirect only if the current user can manage options (i.e., has admin privileges)
        if (is_admin() && current_user_can('manage_options')) {
            wp_safe_redirect(admin_url('admin.php?page=conditional-plugin-settings'));
            exit;
        }
    }
}
add_action('admin_init', 'my_plugin_redirect_after_activation');



// Register settings page
function my_plugin_add_settings_page() {
    add_menu_page(
        'Conditional Content Settings',  // Page title
        'Content Settings',              // Menu title
        'manage_options',                // Capability
        'conditional-plugin-settings',            // Menu slug
        'my_plugin_render_settings_page' // Function to render settings page
    );
}
add_action('admin_menu', 'my_plugin_add_settings_page');

// Render the settings page with usage instructions
function my_plugin_render_settings_page() {
    ?>
    <div class="wrap">
        <h1 class="Conditional-email-title">Conditional Email Content</h1>
        <h1><?php esc_html_e('How to Use the Conditional Content Plugin', 'my-plugin-textdomain'); ?></h1>
        <p><?php esc_html_e('This plugin allows you to conditionally display content based on form field values.', 'my-plugin-textdomain'); ?></p>
        
        <h2><?php esc_html_e('Shortcode Usage:', 'my-plugin-textdomain'); ?></h2>
        <p><?php esc_html_e('To use this plugin, add the following shortcode where you want conditional content to appear:', 'my-plugin-textdomain'); ?></p>
        <code>[conditional_content field="your_field_name"]Your conditional content here[/conditional_content]</code>

        <h3><?php esc_html_e('Example:', 'my-plugin-textdomain'); ?></h3>
        <p><?php esc_html_e('If you have a form field with the name "email", you can display content conditionally like this:', 'my-plugin-textdomain'); ?></p>
        <code>[conditional_content field="email"]Thank you for submitting your email![/conditional_content]</code>

        <p><?php esc_html_e('Replace "your_field_name" with the name of the form field you want to use for the condition.', 'my-plugin-textdomain'); ?></p>
        <p class="buymeacoffee"><a href="https://www.buymeacoffee.com/samworkdevk" target="_blank"><img src="https://cdn.buymeacoffee.com/buttons/v2/default-yellow.png" alt="Buy Me A Coffee" style="height: 60px !important;width: 217px !important;" ></a></p>
    </div>
    <?php
}


// Register plugin settings
function my_plugin_register_settings() {
    register_setting('my_plugin_options_group', 'my_plugin_field_name');
    
    add_settings_section(
        'my_plugin_settings_section', 
        __('Conditional Content Settings Section', 'my-plugin-textdomain'), 
        null, 
        'conditional-plugin-settings'
    );
    
    add_settings_field(
        'my_plugin_field_name',
        __('Field Name for Conditional Content', 'my-plugin-textdomain'),
        'my_plugin_field_name_render',
        'conditional-plugin-settings',
        'my_plugin_settings_section'
    );
}
add_action('admin_init', 'my_plugin_register_settings');

function conditional_hide_content_shortcode($atts, $content = null) {
    $atts = shortcode_atts(array(
        'field' => '',
    ), $atts);

    // Retrieve the form field value using the provided field name/key
    $field_value = isset($_POST[$atts['field']]) ? sanitize_text_field($_POST[$atts['field']]) : '';

    // Check if the field has data, and display the content if it does
    if (!empty($field_value)) {
        return do_shortcode($content);
    } else {
        return '';
    }
}
add_shortcode('conditional_content', 'conditional_hide_content_shortcode');

