<?php


class JWP6_Admin_Page_Import extends JWP6_Admin_Page {

    public function __construct() {
        parent::__construct();
        if ( isset($_GET['show_migration_notice']) ) {
            // Create the default player.
            $player = new JWP6_Player();
            $player->save();
            // Set a welcome message
            $this->add_message('Thank you for upgrading to JW Player 6. Please take some time to migrate your settings first.');
        }
    }

    public function render() {
        global $jwp6_admin;
        $purged = get_option(JWP6 . 'jwp5_purged');
        if ($jwp6_admin->previous_version && $jwp6_admin->previous_version < 6 && ! $purged ) {
            return $this->do_render();
        }
        return $this->do_not_render();
    }

    public function do_not_render() {
        return '';
    }

    public function do_render() {
        $this->render_page_start('Import Settings from JW Player 5 Plugin');
        $this->render_all_messages();
        ?>

        <div class="divider"></div>

        <form method="post" action="<?php echo $this->page_url(); ?>">

            <h3>Import Settings</h3>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        Import Players
                    </th>
                    <td>
                        <?php if ( ! get_option(JWP6 . 'jwp5_players_imported') ): ?>
                        <a href="<?php echo JWP6_PLUGIN_URL . "jwp6-import.php?a=players"; ?>" class="button">
                            Import all players from the JW Player 5 Plugin
                        </a>
                        <p class="description">
                            <strong>
                                Please note: If you have a player license, 
                                <a href="<?php echo admin_url('admin.php?page=' . JWP6 . 'menu_licensing'); ?>">insert 
                                it here</a> to make sure that all settings are imported correctly</strong>. 
                                Only settings compatible with your player license will be imported.
                            </strong>
                        </p>
                        <?php else: ?>
                        ✔ Done.
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        Import Playlists
                    </th>
                    <td>
                        <?php if ( ! get_option(JWP6 . 'jwp5_playlists_imported') ): ?>
                        <a href="<?php echo JWP6_PLUGIN_URL . "jwp6-import.php?a=playlists"; ?>" class="button">
                            Import all playlists from the JW Player 5 Plugin
                        </a>
                        <p class='description'>
                            Playlists for the JW Player 6 do no longer support images. All playlists for version 5
                            will be checked and the images in playlists will be purged. Playlists with only
                            images cannot be imported.
                        </p>
                        <?php else: ?>
                        ✔ Done.
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        Purge old settings
                    </th>
                    <td>
                        <a href="<?php echo JWP6_PLUGIN_URL . "jwp6-import.php?a=purge"; ?>" class="button">
                            Purge all settings from the JW Player 5 Plugin
                        </a>
                        <p class="description">
                            This will clean your database and uploads folder from the settings and files that were
                            used with the Player 5 version of this plugin. <strong>Please note it will not be possible
                            to revert to JW Player 5 after you do this!</strong>
                        </p>
                    </td>
                </tr>
            </table>

            <div class="divider"></div>

            <h3>Revert to version 5</h3>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        Revert to version 5
                    </th>
                    <td>
                        <a href="<?php echo JWP6_PLUGIN_URL . "jwp6-import.php?a=revert"; ?>" class="button" id="revertjwp5button">
                            Revert back to JW Player 5 Plugin
                        </a>
                    </td>
                </tr>
            </table>

        </form>

        <script type="text/javascript">
        jQuery(function(){
            jQuery('#revertjwp5button').bind('click', function (e) {
                var c = confirm(
                    'Please note:\n\n ' +
                    '1. Any player embed you have made with this version of the plugin will no longer work when you revert.\n' +
                    '2. This will erase all settings for version 6 of the plugin.\n'
                );
                if (!c) {
                    return false;
                }
            });
        });
        </script>
        <?php
        $this->render_page_end();
    }

}
