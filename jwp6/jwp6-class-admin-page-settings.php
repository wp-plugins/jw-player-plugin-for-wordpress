<?php


class JWP6_Admin_Page_Licensing extends JWP6_Admin_Page {

    public function __construct() {
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
        $player_location_field = new JWP6_Form_Field(
            'player_location',
            array(
                'validation' => array('JWP6_Plugin', 'validate_empty_or_url'),
                'error_help' => 'Please make sure that the url you provide is accessible.',
                'help_text' => 'If you want to host '.
                    "<a href='" . JWP6_Plugin::$urls['player_download'] . "'>your own player</a>, " .
                    "you can add its full location here. <br />" . 
                    "<strong>You can leave this setting blank and Longtail Video will host the " .
                    "player for you.</strong>",
            )
        );

        $uninstall_field = new JWP6_Form_Field_Toggle(
            'uninstall',
            array(
                'label' => 'Confirm uninstall',
                'text' => 'I confirm that I want to uninstall this plugin.',
                'default' => false,
                'help_text' => '<strong>Please Note</strong>: This process in irreversible. All settings will be deleted from the database.'
            )
        );

        $this->form_fields = array(
            $license_version_field, 
            $license_key_field, 
            $player_location_field
        );

        $this->license_fields = array(
            $license_version_field, 
            $license_key_field, 
        );

        $this->uninstall_fields = array(
            $uninstall_field,
        );

        $this->location_fields = array(
            $player_location_field
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

            <h3>Player Location Settings</h3>
            <table class="form-table">
                <?php foreach ($this->location_fields as $field) { $this->render_form_row($field); } ?>
            </table>

            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button-primary" value="Save Changes"  />
            </p>
        </form>

        <div class="divider"></div>

        <form method="post" action="<?php echo $this->page_url(); ?>">

            <h3>Uninstall</h3>

            <table class="form-table">
                <?php foreach ($this->uninstall_fields as $field) { $this->render_form_row($field); } ?>
            </table>

            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button-primary" value="Uninstall this plugin."  />
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
