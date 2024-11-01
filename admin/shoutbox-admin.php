<?php
if (!defined('ABSPATH'))
    exit;

// Settings Page: Shoutbox Settings
class shoutboxsettings_Settings_Page
{
    public $nonce;

    public function __construct()
    {
        add_action('admin_menu', array($this, 'shoutbox2024_create_settings'));
        add_action('admin_menu', array($this, 'shoutbox2024_smilies_settings'));
        add_action('admin_init', array($this, 'shoutbox2024_setup_sections'));
        add_action('admin_init', array($this, 'shoutbox2024_setup_fields'));
        add_action('admin_enqueue_scripts', 'wp_enqueue_media');
        $this->nonce = wp_create_nonce('shoutbox2024_nonce');
    }

    public function shoutbox2024_create_settings()
    {
        $page_title = 'Shoutbox Settings';
        $menu_title = 'Shoutbox Settings';
        $capability = 'manage_options';
        $slug = 'shoutboxsettings';
        $callback = array($this, 'shoutbox2024_settings_content');
        $icon = 'dashicons-admin-comments';
        $position = 80;
        add_menu_page($page_title, $menu_title, $capability, $slug, $callback, $icon, $position);
    }

    public function shoutbox2024_smilies_settings()
    {
        add_media_page(
            'Shoutbox Smilies',   // Page title
            'Shoutbox Smilies',   // Menu title
            'manage_options',     // Capability required
            'shoutbox2024_smilies',   // Menu slug
            array($this, 'shoutbox2024_smilies_page') // Callback function to display page content
        );
    }

    public function shoutbox2024_smilies_page()
    {
        // Check if form is submitted
        if (isset($_POST['submit']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'shoutbox2024_nonce')) {
            // Handle form submission
            // Update or insert rows in wp_shoutbox_smilies table
            global $wpdb;
            $table_name = $wpdb->prefix . 'shoutbox_smilies';

            // Sanitize and escape user input
            $names = isset($_POST['name']) ? array_map('sanitize_text_field', wp_unslash($_POST['name'])) : [];
            $codes = isset($_POST['code']) ? array_map('sanitize_text_field', wp_unslash($_POST['code'])) : [];
            $urls = isset($_POST['url']) ? array_map('esc_url', wp_unslash($_POST['url'])) : [];
            $ids = isset($_POST['id']) ? array_map('intval', wp_unslash($_POST['id'])) : [];
            $markasdelete = isset($_POST['markasdelete']) ? array_map('intval', wp_unslash($_POST['markasdelete'])) : [];

            for ($i = 0; $i < count($names); $i++) {
                $name = $names[$i];
                $code = $codes[$i];
                $url = $urls[$i];
                $id = $ids[$i];
                $delete = $markasdelete[$i];

                if ($delete) {
                    // Delete the row
                    $wpdb->delete($table_name, array('id' => $id)); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                } else if ($id) {
                    // Update the row
                    $wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                        $table_name,
                        array(
                            'name' => $name,
                            'code' => $code,
                            'url' => $url
                        ),
                        array('id' => $id)
                    );
                } else {
                    // Insert a new row
                    $wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                        $table_name,
                        array(
                            'name' => $name,
                            'code' => $code,
                            'url' => $url
                        )
                    );
                }
            }

            // Check for errors
            if ($wpdb->last_error) {
                echo "<div class='error'><p>Error saving data</p></div>";
            } else {
                echo "<div class='updated'><p>Data saved</p></div>";
            }
        }

        // Get the data from wp_shoutbox_smilies table
        global $wpdb;
        $smilies = $wpdb->get_results("SELECT * FROM {$wpdb->base_prefix}shoutbox_smilies", ARRAY_A); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching

        // Display the admin page content
        ?>
        <div class="wrap">
            <h2>Shoutbox Smilies</h2>
            <!-- HTML form for adding rows and saving changes -->
            <form method="post" action="" id="smiliesform">
                <input type="hidden" name="nonce" value="<?php echo esc_attr($this->nonce); ?>">
                <!-- If no rows, show first empty 1 to not break everything! -->
                <?php if (count($smilies) == 0): ?>
                    <h3>No smilies found, add some below!</h3>
                    <p>Smilies are used to convert text into images. For example, typing <code>:)</code> will display an image of a
                        smiley face. (once added to your media library and selected below)</p>
                    <p>An example has been set below, you just need to add your own image to get started!</p>
                    <p>Click the <strong>Select</strong> button to choose an image from your media library.</p>
                    <table id="smilies_table">
                        <tr>
                            <th>Name</th>
                            <th>Code</th>
                            <th>Image</th>
                        </tr>
                        <tr>
                            <td><input type="text" name="name[]" value="My first smilie"></td>
                            <td><input type="text" name="code[]" value=":myfirstsmilie:"></td>
                            <td>
                                <input type="hidden" name="id[]" value="">
                                <input type="hidden" name="url[]" value="">
                                <input type="hidden" name="markasdelete[]" value="0">
                                <div id="preview"
                                    style="margin-right:10px;border:1px solid #e2e4e7;background-color:#fafafa;display:inline-block;width: 100px;height:100px;background-image:url();background-size:contain;background-repeat:no-repeat;background-position:center;">
                                </div>
                                <input style="width: 30%;" class="button shoutboxsettings-media" id="media_button"
                                    name="media_button" type="button" value="Select" />
                            </td>
                        </tr>
                    </table>
                <?php endif; ?>
                <!-- Foreach smilie -->

                <?php if (count($smilies) != 0): ?>

                    <table id="smilies_table">
                        <tr>
                            <th>Name</th>
                            <th>Code</th>
                            <th>Image</th>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($smilies as $smilie): ?>

                        <tr class="smilie-row-<?php echo esc_attr($smilie['id']); ?>">
                            <td><input type="text" name="name[]" value="<?php echo esc_attr($smilie['name']); ?>"></td>

                            <td><input type="text" name="code[]" value="<?php echo esc_attr($smilie['code']); ?>"></td>

                            <td>
                                <input type="hidden" name="id[]" value="<?php echo esc_attr($smilie['id']); ?>">

                                <input type="hidden" name="url[]" value="<?php echo esc_url($smilie['url']); ?>">

                                <input type="hidden" name="markasdelete[]" value="0">

                                <div id="preview<?php echo esc_attr($smilie['id']); ?>"
                                    style="margin-right:10px;border:1px solid #e2e4e7;background-color:#fafafa;display:inline-block;width: 100px;height:100px;background-image:url(<?php echo esc_url($smilie['url']); ?>);background-size:contain;background-repeat:no-repeat;background-position:center;">
                                </div>
                                <input style="width: 24%;" class="button shoutboxsettings-media"
                                    id="media_button<?php echo esc_attr($smilie['id']); ?>"
                                    name="media_button<?php echo esc_attr($smilie['id']); ?>" type="button" value="Select" />
                                <input style="width: 24%;" class="button remove-media"
                                    id="media_buttonremove<?php echo esc_attr($smilie['id']); ?>"
                                    name="media_buttonremove<?php echo esc_attr($smilie['id']); ?>" type="button" value="Delete" />
                            </td>
                        </tr>

                    <?php endforeach; ?>
                </table>

                <input class="button button-primary" id="smiliessubmit" type="submit" name="submit" value="Save Changes">
                <button class="button" type="button" id="add_row">Add Row</button>
                <p id="action-helper" class="alert" style="display:none;color:red;">Remember to save your changes!</p>
            </form>

            <!-- Buy me a coffee link -->
            <div style="margin-top: 20px;">
                <p>If you like this plugin, please consider <a href="https://buymeacoffee.com/garethjohnstone"
                        target="_blank">buying me a coffee ☕</a></p>
            </div>

            <script>
                jQuery(document).ready(function ($) {
                    // Add media selector to the input fields
                    $('.shoutboxsettings-media').click(function () {
                        var button = $(this);
                        var id = button.attr('id').replace('media_button', '');
                        var send_attachment_bkp = wp.media.editor.send.attachment;
                        wp.media.editor.send.attachment = function (props, attachment) {
                            $('#preview' + id).css('background-image', 'url(' + attachment.url + ')');
                            $('#preview' + id).css('background-size', 'cover');
                            $('#preview' + id).css('background-repeat', 'no-repeat');
                            $('#preview' + id).css('background-position', 'center');
                            $('#preview' + id).css('background-color', '#fafafa');
                            $('#preview' + id).css('border', '1px solid #e2e4e7');
                            $('#preview' + id).css('width', '100px');
                            $('#preview' + id).css('height', '100px');
                            $('input[name="url[]"][value=""]').val(attachment.url);
                            wp.media.editor.send.attachment = send_attachment_bkp;
                        };
                        wp.media.editor.open(button);
                        return false;
                    });


                    // Remove media selector from the input fields
                    $('.remove-media').click(function () {
                        // send post to delete row in database
                        var button = $(this);
                        var id = button.attr('id').replace('media_buttonremove', '');
                        if (id) {
                            // Mark row for deletion
                            $('.smilie-row-' + id + ' input[name="markasdelete[]"]').val('1');
                            // Set row opacity to 0.2
                            $('.smilie-row-' + id).css('opacity', '0.2');
                            // Set action helper to display inline;
                            $('#action-helper').css('display', 'inline');
                        }
                    });

                    // Add a new row to the table
                    $('#add_row').click(function () {
                        // Insert empty row into the #smilies_table emoji table with blank values
                        $('#smilies_table').append('<tr><td><input type="text" name="name[]" value=""></td><td><input type="text" name="code[]" value=""></td><td><input type="hidden" name="id[]" value=""><input type="hidden" name="url[]" value=""><input type="hidden" name="markasdelete[]" value="0"><div id="preview" style="margin-right:10px;border:1px solid #e2e4e7;background-color:#fafafa;display:inline-block;width: 100px;height:100px;background-image:url();background-size:cover;background-repeat:no-repeat;background-position:center;"></div><input style="width: 19%;" class="button shoutboxsettings-media" id="media_button" name="media_button" type="button" value="Select" /><input style="width: 19%;" class="button remove-media" id="media_buttonremove" name="media_buttonremove" type="button" value="Clear" /></td></tr>');

                        $('#smiliessubmit').trigger('click');
                    });
                });
            </script>
            <?php
    }


    public function shoutbox2024_settings_content()
    { ?>
            <div class="wrap">
                <h1>Shoutbox Settings</h1>
                <?php settings_errors(); ?>
                <form method="POST" action="options.php">
                    <?php
                    settings_fields('shoutboxsettings');
                    do_settings_sections('shoutboxsettings');
                    submit_button();
                    ?>
                </form>
            </div>
            <?php
    }

    public function shoutbox2024_setup_sections()
    {
        add_settings_section('shoutboxsettings_section', 'Configure your shoutbox', array(), 'shoutboxsettings');
    }

    public function shoutbox2024_setup_fields()
    {
        $fields = array(
            array(
                'value' => '10',
                'label' => 'Refresh Interval',
                'id' => 'shoutbox2024_refresh-interval',
                'type' => 'text',
                'section' => 'shoutboxsettings_section',
                'desc' => 'How often will the shoutbox check for new messages.'
            ),
            array(
                'label' => 'Default Font Colour',
                'id' => 'shoutbox2024_default-font-colour',
                'type' => 'color',
                'section' => 'shoutboxsettings_section',
                'desc' => 'Default font colour for user preference.'
            ),
            array(
                'label' => 'Locale',
                'id' => 'shoutbox2024_locale',
                'type' => 'select',
                'section' => 'shoutboxsettings_section',
                'options' => array(
                    'en-UK' => 'UK (en-UK)',
                    'en-US' => 'US (en-US)'
                ),
                'desc' => 'This is for the date and time format on the shout messages.'
            ),
            array(
                'label' => 'Shoutbox New Message highlight Colour',
                'id' => 'shoutbox2024_new-message-colour',
                'type' => 'color',
                'section' => 'shoutboxsettings_section',
                'desc' => 'Background highlight colour for new messages.'
            ),
            array(
                'label' => 'Shoutbox PM Background Colour',
                'id' => 'shoutbox2024_pm-background-colour',
                'type' => 'color',
                'section' => 'shoutboxsettings_section',
                'desc' => 'Background colour for PM messages.'
            ),
            array(
                'label' => 'Shoutbox Cleanup',
                'id' => 'shoutbox2024_cleanup',
                'type' => 'number',
                'section' => 'shoutboxsettings_section',
                'desc' => 'How many days to keep shoutbox messages for.'
            ),
            array(
                'label' => 'Shoutbox Height',
                'id' => 'shoutbox2024_height',
                'type' => 'number',
                'section' => 'shoutboxsettings_section',
                'desc' => 'Height of the shoutbox in pixels.'
            ),
            array(
                'label' => 'Buy me a coffee',
                'id' => 'shoutbox2024_coffee',
                'type' => 'coffee',
                'section' => 'shoutboxsettings_section',
                'desc' => 'If you like this plugin, please consider buying me a coffee ☕'
            )
        );
        foreach ($fields as $field) {
            add_settings_field($field['id'], $field['label'], array($this, 'shoutbox2024_field_callback'), 'shoutboxsettings', $field['section'], $field);
            register_setting('shoutboxsettings', $field['id']);
        }
    }

    public function shoutbox2024_field_callback($field)
    {
        $value = get_option($field['id']);

        switch ($field['type']) {
            case 'media':
                $field_url = '';
                if ($value) {
                    if ($field['returnvalue'] == 'url') {
                        $field_url = $value;
                    } else {
                        $field_url = wp_get_attachment_url($value);
                    }
                }
                printf(
                    '<input style="display:none;" id="%s" name="%s" type="text" value="%s"  data-return="%s"><div id="preview%s" style="margin-right:10px;border:1px solid #e2e4e7;background-color:#fafafa;display:inline-block;width: 100px;height:100px;background-image:url(%s);background-size:cover;background-repeat:no-repeat;background-position:center;"></div><input style="width: 19%%;margin-right:5px;" class="button shoutboxsettings-media" id="%s_button" name="%s_button" type="button" value="Select" /><input style="width: 19%%;" class="button remove-media" id="%s_buttonremove" name="%s_buttonremove" type="button" value="Clear" />',
                    esc_attr($field['id']),
                    esc_attr($field['id']),
                    esc_attr($value),
                    esc_html($field['returnvalue']),
                    esc_attr($field['id']),
                    esc_html($field_url),
                    esc_attr($field['id']),
                    esc_attr($field['id']),
                    esc_attr($field['id']),
                    esc_attr($field['id'])
                );
                break;
            case 'select':
            case 'multiselect':
                if (!empty($field['options']) && is_array($field['options'])) {
                    $attr = '';
                    $options = '';
                    foreach ($field['options'] as $key => $label) {
                        $options .= sprintf(
                            '<option value="%s" %s>%s</option>',
                            esc_attr($key),
                            selected($value, $key, false),
                            esc_html($label)
                        );
                    }
                    if ($field['type'] === 'multiselect') {
                        $attr = ' multiple="multiple" ';
                    }
                    printf(
                        '<select name="%1$s" id="%1$s" %2$s>%3$s</select>',
                        esc_attr($field['id']),
                        esc_attr($attr),
                        wp_kses(
                            $options,
                            array(
                                'option' => array(
                                    'value' => array()
                                )
                            ),
                        )
                    );
                }
                break;

            case 'coffee':
                printf(
                    '<a href="https://buymeacoffee.com/garethjohnstone" target="_blank">Buy me a coffee ☕</a>'
                );
                break;
            default:
                printf(
                    '<input name="%1$s" id="%1$s" type="%2$s" value="%3$s" />',
                    esc_attr($field['id']),
                    esc_html($field['type']),
                    esc_html($value)
                );
        }
        if (isset($field['desc'])) {
            if ($desc = $field['desc']) {
                printf('<p class="description">%s </p>', esc_html($desc));
            }
        }
    }

}
new shoutboxsettings_Settings_Page();

// Set default setitngs!
if (get_option('shoutbox2024_refresh-interval') === false) {
    update_option('shoutbox2024_refresh-interval', '10');
}

if (get_option('shoutbox2024_default-font-colour') === false) {
    update_option('shoutbox2024_default-font-colour', '#EFEFEF');
}

if (get_option('shoutbox2024_locale') === false) {
    update_option('shoutbox2024_locale', 'en-UK');
}

if (get_option('shoutbox2024_cleanup') === false) {
    update_option('shoutbox2024_cleanup', '30');
}

if (get_option('shoutbox2024_pm-background-colour') === false) {
    update_option('shoutbox2024_pm-background-colour', '#2f4f4f');
}

if (get_option('shoutbox2024_new-message-colour') === false) {
    update_option('shoutbox2024_new-message-colour', '#2b3035');
}

if (get_option('shoutbox2024_height') === false) {
    update_option('shoutbox2024_height', '170');
}
