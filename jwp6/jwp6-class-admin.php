<?php

class JWP6_Admin {

    public function __construct() {
        global $wp_version;
        $this->previous_version = get_option(JWP6 . 'previous_version');
        if ( version_compare($wp_version, '3.5', '<') ) add_action('media_buttons', array($this, 'media_button'), 99);
        add_action('admin_menu', array($this, 'admin_menu'));
        add_filter('media_upload_tabs', array($this, 'add_media_tab'), 99999);
        add_filter('media_upload_jwp6_media', array($this, 'render_media_tab'));
        JWP6_Media::add_filters();
    }

    public function media_button() {
        $this->head_assets();
        $url = esc_url(JWP6_PLUGIN_URL . 'jwp6-media.php?TB_iframe=1');
        echo "<a href='$url' id='jwp6-media-button' class='thickbox' " .
            "title='Add a JW Player to your post'>Add a JW Player</a>";
    }

    public static function add_media_tab($tabs) {
        global $wp_version;
        $tabs["jwp6_media"] = ( version_compare($wp_version, '3.5', '>=') ) ? 'JW Player Wizard' : 'JW Player →';
        return $tabs;
    }

    public static function render_media_tab() {
        wp_redirect(JWP6_PLUGIN_URL . 'jwp6-media.php');
        exit();
    }

    public function head_assets() {
        wp_register_script('jwp6-admin.js', JWP6_PLUGIN_URL.'js/jwp6-admin.js');
        wp_enqueue_script('jwp6-admin.js');
        wp_register_style('jwp6-admin.css', JWP6_PLUGIN_URL.'css/jwp6-admin.css');
        wp_enqueue_style('jwp6-admin.css');
    }

    public function admin_menu() {
        $admin = add_menu_page(
            "JW Players Title",                    // $page_title
            "JW Players",                          // $menu_title
            "administrator",                       // $capability
            JWP6 . "menu",                         // $menu_slug
            null,
            //array($this, 'admin_pages'),            // $function
            JWP6_PLUGIN_URL . "/img/wordpress.png"  // $icon_url
        );
        add_submenu_page(
            JWP6 . "menu",
            "JW Player Configurations", 
            "Player management", 
            "administrator", 
            JWP6 . "menu", 
            array($this, 'admin_pages')
        );
        add_submenu_page(
            JWP6 . "menu",
            "JW Player Plugin", 
            "Plugin Settings", 
            "administrator", 
            JWP6 . "menu_licensing", 
            array($this, 'admin_pages')
        );
        $purged = get_option(JWP6 . 'jwp5_purged');
        if ( $this->previous_version && $this->previous_version < 6 && ! $purged) {
            add_submenu_page(
                JWP6 . "menu",
                "Import Settings from JW Player 5 Plugin", 
                "JWP5 Migration", 
                "administrator", 
                JWP6 . "menu_import", 
                array($this, 'admin_pages')
            );
        }
        $media = add_media_page(
            "JW Player Playlist Manager",    //$page_title
            "Playlists",                     //$menu_title
            "read",                          //$capability
            JWP6 . "playlists",              //$menu_slug
            array($this, 'playlist_manager') //$function
        );
        //add_action("admin_print_scripts-$admin", "add_admin_js");
        //add_action("admin_print_scripts-$media", "add_admin_js");
    }

    public function admin_pages() {
        require_once(JWP6_PLUGIN_DIR . '/jwp6-class-admin-page.php');
        require_once( JWP6_PLUGIN_DIR . '/jwp6-class-form-field.php');
        switch ($_GET["page"]) {
            case JWP6 . "menu_import" :
                require_once (JWP6_PLUGIN_DIR . '/jwp6-class-admin-page-import.php');
                $page = new JWP6_Admin_Page_Import();
                break;
            case JWP6 . "menu_licensing" :
                require_once (JWP6_PLUGIN_DIR . '/jwp6-class-admin-page-settings.php');
                $page = new JWP6_Admin_Page_Licensing();
                break;
            default:
                require_once (JWP6_PLUGIN_DIR . '/jwp6-class-admin-page-players.php');
                $page = new JWP6_Admin_Page_Players();
                break;
        }
        $page->page_slug = $_GET["page"];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $page->process_post_data($_POST);
        }
        $this->head_assets();
        $page->head_assets();
        $page->render();
    }

    public function playlist_manager() {
        wp_enqueue_script("jquery-ui-core");
        wp_enqueue_script("jquery-ui-tabs");
        wp_enqueue_script("jquery-ui-button");
        wp_enqueue_script("jquery-ui-widget");
        wp_enqueue_script("jquery-ui-mouse");
        wp_enqueue_script("jquery-ui-draggable");
        wp_enqueue_script("jquery-ui-droppable");
        wp_enqueue_script("jquery-ui-sortable");
        wp_register_style('jwp6-admin.css', JWP6_PLUGIN_URL.'css/jwp6-admin.css');
        wp_enqueue_style('jwp6-admin.css');
        require_once(JWP6_PLUGIN_DIR . '/jwp6-playlist-manager.php');
    }

}
