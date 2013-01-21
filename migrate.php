<?php

class JWP6_Migrate {
    public static function upgrade_notice() {
        $jwp6_hideupgradeto6notice = get_option(JWP6 . 'hideupgradeto6notice');
        if ( ! $jwp6_hideupgradeto6notice ) {
            add_option( JWP6 . 'hideupgradeto6notice', 'no', '', true);
            $jwp6_hideupgradeto6notice = 'no';
        }
        if ( 'no' == $jwp6_hideupgradeto6notice ) {
            if ( 
                ( isset($_GET['page']) && 0 == strpos($_GET['page'], 'jwplayer') && 'jwplayer-update' != $_GET['page'] )
                ||
                'plugins.php' == basename($_SERVER['SCRIPT_FILENAME']) 
            ) {
            ?>
            <div class="fade updated">
                <p>
                    Please note. You can now upgrade to the new JW Player version 6.
                    <a href="<?php echo admin_url('admin.php?page=jwplayer-update'); ?>">Activate it now</a>
                    or
                    <a href="<?php echo admin_url('admin.php?page=jwplayer-update&' . JWP6 . 'hide_migration_notice=1'); ?>">hide this message</a>.
                </p>
            </div>
            <?php
            }
        }
    }

    public static function migrate_section() {
        ?>
        <form name="<?php echo LONGTAIL_KEY . "form"; ?>" method="post" action="<?php echo admin_url('admin.php?page=jwplayer-update&noheader=true'); ?>">
            <div class="stuffbox">
                <h3 class="hndle"><span>Upgrade to JW Player 6</span></h3>
                <div class="inside">
                    <p style="margin: 15px;">
                        <?php printf(__("You can now upgrade to JW Player 6, because")); ?>
                        TODO: Text
                    </p>
                    <p style="margin: 15px;">
                        <?php printf(__("A migration to JW Player 6 is reversible.")); ?>
                        TODO: Text
                    </p>
                    <p style="margin: 15px;">
                        <strong>
                            Make sure you 
                            <a target="_blank" href="http://www.longtailvideo.com/support/jw-player/28834/migrating-from-jw5-to-jw6/">read our migratation guide</a>
                            before you hit the button.
                        </strong>
                    </p>
                    <p style="margin: 15px;">
                        <input type="hidden" name="noheader" value="true" />
                        <input class="button" type="submit" name="migrate_to_jwp6" value="<?php _e("Upgrade to JW Player 6", 'jw-player-plugin-for-wordpress'); ?>" />
                    </p>
                </div>
            </div>
        </form>
        <?php
    }

    public static function hide_migration_notice() {
        update_option(JWP6 . 'hideupgradeto6notice', 'yes');
        ?>
        <div class="updated fade">
            <p>
                <strong>The migration notice will no longer be showed.</strong>
                You can always upgrade to JW Player Version 6 on this page!
            </p>
        </div>
        <?php
    }

    public static function migrate() {
        update_option(JWP6 . 'plugin_version', 6);
        add_option(JWP6 . 'previous_version', 5, '', true);
        delete_option(JWP6 . 'hideupgradeto6notice');
        wp_redirect(admin_url('admin.php?page=' . JWP6 . 'menu_import&show_migration_notice=true'));
    }
}

add_action('admin_notices', array('JWP6_Migrate', 'upgrade_notice'));
