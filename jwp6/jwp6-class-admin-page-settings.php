<?php


class JWP6_Admin_Page_Licensing extends JWP6_Admin_Page {

    public function __construct() {
        if ( isset($_POST) && array_key_exists('purge_settings_at_deactivation', $_POST) ) {
            echo "The value of purge_settings_at_deactivation: " . $_POST['purge_settings_at_deactivation'];
        }
        parent::__construct();
        $license_version_field =  new JWP6_Form_Field_Select(
            'license_version',
            array(
                'options' => JWP6_Plugin::$license_versions,
                'default' => 'free',
                'description_is_value' => true,
                'help_text' => 'If you do not have a license key. You should always choose free.',
            )
        );
        $license_key_field = new JWP6_Form_Field(
            'license_key', 
            array(
                'validation' => function ($value) {
                    return ( preg_match('/^\S*$/', $value) ) ? $value : NULL;
                }
            )
        );

        $tracking_field = new JWP6_Form_Field_Toggle(
            'allow_anonymous_tracking',
            array(
                'label' => 'Allow anonymous tracking?',
                'text' => 'Allow LongTail Video to track plugin feature usage.',
                'help_text' => 'This will help us improve the plugin in the future. <strong>Note: Tracking is done anonymously.</strong>',
                'default' => true,
            )
        );
        
        $purge_field = new JWP6_Form_Field_Toggle(
            'purge_settings_at_deactivation',
            array(
                'label' => 'Purge settings at deactivation?',
                'text' => 'When I deactivate this plugin, I want all my settings for this plugin to be purged from the database.',
                'default' => false,
                'help_text' => '<strong>Please Note</strong>: This process in irreversible. If you ever decide to reactivate the plugin all your settings will be gone. Use with care!',
            )
        );

        $this->form_fields = array(
            $license_version_field, 
            $license_key_field,
            $tracking_field,
            $purge_field,
        );

        $this->license_fields = array(
            $license_version_field, 
            $license_key_field, 
        );

        $this->other_fields = array(
            $tracking_field,
            $purge_field,
        );

    }

    public function render() {
        $this->render_page_start('License and Location');
        $this->render_all_messages();
        ?>

        <div class="divider"></div>

        <form method="post" action="<?php echo $this->page_url(); ?>">
            <?php settings_fields(JWP6 . 'menu_licensing'); ?>

            <h3>Player License Settings</h3>
            <table class="form-table">
                <?php foreach ($this->license_fields as $field) { $this->render_form_row($field); } ?>
            </table>

            <div class="divider"></div>

            <h3>Other settings</h3>

            <table class="form-table">
                <?php foreach ($this->other_fields as $field) { $this->render_form_row($field); } ?>
            </table>

            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button-primary" value="Save Changes"  />
            </p>
        </form>

        <script type="text/javascript">
            jQuery(function () {
                var $ = jQuery;
                function check_key(e) {
                    var
                        version = $('#license_version').val();
                        key = $('#license_key').val();

                    alert('We have version ' + version + ' with key ' + key);

                }
                $('#license_version').bind('change', check_key);
            });
        </script>
        <?php
        $this->render_page_end();
    }

}
