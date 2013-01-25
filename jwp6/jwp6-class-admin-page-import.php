<?php


class JWP6_Admin_Page_Import extends JWP6_Admin_Page {

    public function __construct() {
        parent::__construct();
        $this->imported_players = get_option(JWP6 . 'imported_jwp5_players');
        $this->imported_playlists = get_option(JWP6 . 'imported_jwp5_playlists');
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
        $this->render_page_start('Migration from JW Player 5 Plugin');
        $this->render_all_messages();

        if ( $this->imported_players ):
        ?>
        <div class="divider"></div>
        <div class="tool-box">

            <h3>Imported JW Player 5 Players</h3>

            <p>The following players were imported from the JW Player 5 plugin.</p>

            <ul>
                <?php foreach ($this->imported_players as $old => $new) {
                    $player_edit_url = admin_url('admin.php?page=' . JWP6 . 'menu&player_id=' . $new);
                    echo "<li>{$old} → <a href='{$player_edit_url}' title='Click to edit this player'>{$new}</a></li>";
                } ?>
            </ul>

            <?php if ( ! JWP6_Plugin::player_license_key() ): ?>
            <p class="description">
                <strong>Please note:</strong>
                You need to <a href="<?php echo admin_url('admin.php?page=' . JWP6 . 'menu_license'); ?>"
                title="Click to enter your player license">enter
                your player license key</a> to enable advanced / license specific player options and settings
                (e.g. skins, rightclick, logo, etc).
            </p>
            <?php endif; ?>
        </div>
        <?php
        endif;

        if ( $this->imported_playlists ):
        ?>
        <div class="divider"></div>
        <div class="tool-box">

            <h3>Imported JW Player 5 Playlists</h3>

            <p>The following playlists were imported from the JW Player 5 plugin.</p>

            <ul>
                <?php
                $not_all_imported = false;
                foreach ($this->imported_playlists as $info) {
                    if ( $info['has_missing_items'] ) $not_all_imported = true;
                    echo "<li>";
                    echo "{$info['name']} → ";
                    if ($info['nr_of_new_items']) {
                        $nr = ($info['nr_of_old_items'] ==  $info['nr_of_new_items'] ) ? 
                            'all': "{$info['nr_of_new_items']} of {$info['nr_of_old_items']}";
                        echo "Imported {$nr} videos.";
                    } else {
                        echo "This playlist was not imported (no videos?).";
                    }
                    echo "</li>";

                }
                ?>
            </ul>

            <?php if ( $not_all_imported ): ?>
            <p class="description">
                <strong>Please note:</strong>
                The JW Player 6 no longer supports images as content of a playlists.
                Playlists that only contained images (e.g. for a slideshow) have not been
                been imported.
            </p>
            <?php endif; ?>
        </div>

        <?php endif; ?>

        <div class="divider"></div>

        <form method="post" action="<?php echo $this->page_url(); ?>">

            <?php /*
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
            */ ?>

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
