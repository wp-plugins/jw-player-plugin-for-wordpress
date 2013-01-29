<?php

class JWP6_Player {

    /*
    Please note.

    $this->config is a nested array.
    array(
        'logo' => array(
            'hide' => true,
        ),
    );

    $this->defaults is NOT nested (Same as JWP6_Plugin::$player_options).
    array (
        'logo__hide' => true,
    );

    */

    protected $name;

    protected $config = array();

    private $defaults = array(
        'width' => array('default' => 480),
        'height' => array('default' => 270),
        'description' => array('default' => ''),
        // The rest of the values will be added from JWP6_Plugin::$player_options;
    );

    private $translate_string_values = array(
        'true' => true,
        'false' => false,
        'NULL' => null,
    );

    public function __construct($name = 'default', $config = false) {
        // complete defaults.
        foreach(JWP6_Plugin::$player_options as $option => $settings) {
            if ( array_key_exists('default', $settings) ) {
                $this->defaults[$option] = array('default' => $settings['default']);
            }
        }
        if ( $this->validate_player_name($name) ) {
            $this->name = $name;
            if ( $this->name == 'default' ) {
                $this->set('description', 'Default and fallback player (unremovable).');
            }
            $saved_config = get_option(JWP6 . "player_config_" . $this->name);
            if ( $saved_config ) {
                $this->config = $saved_config;
            }
        } else {
            throw new Exception("Please provide a valid player name", 1);
        }
        if ( $config && is_array($config) ) {
            $this->config = $config;
        }
    }

    public static function validate_player_name($name) {
        if ( preg_match('/^[a-z0-9_-]*$/i', $name) ) {
            return $name;
        }
        return NULL;
    }

    private function _validate_param_value($param, $value) {
        // TODO: More elaborate validation
        if ( array_key_exists($param, $this->defaults) ) {
            return true;
        }
        return false;
    }

    public function save() {
        $players = get_option(JWP6 . 'players');
        if ( ! $players || ! in_array($this->name, $players) ) {
            $players[] = $this->name;
            update_option(JWP6 . 'players', $players);
        }
        update_option(JWP6 . 'player_config_' . $this->name, $this->config);
    }

    // Check and see if this player has been saved to the option table or not.
    public function is_existing() {
        $player_config = get_option(JWP6 . 'player_config_' . $this->name);
        if ( $player_config ) {
            return true;
        }
        return false;
    }

    public function purge() {
        if ( 'default' != $this->name ) {
            delete_option(JWP6 . 'player_config_' . $this->name);
            $players = get_option(JWP6 . 'players');
            if (($key = array_search($this->name, $players)) !== false) {
                unset($players[$key]);
            }
            update_option(JWP6 . 'players', $players);
        }
    }

    public function admin_url($page, $action = 'edit') {
        $params = array( 'player_id' => $this->name );
        if ( 'copy' == $action || 'delete' == $action ) {
            $params['action'] = $action;
        }
        return $page->page_url($params);
    }

    public function get_defaults() {
        return $this->defaults;
    }

    public function get_name() {
        return $this->name;
    }

    public function get_config() {
        return $this->config;
    }

    // Properties
    public function get($param) {
        if ( strpos($param, '__') ) {
            $parts = split('__', $param);
            $last_part = end($parts);
            $a = $this->config;
            foreach ($parts as $part) {
                if ( array_key_exists($part, $a) ) {
                    if ( $last_part == $part ) {
                        return $a[$part];
                    } else {
                        $a = $a[$part];
                    }
                } else {
                    break;
                }
            }
        } else {
            if ( array_key_exists($param, $this->config) ) {
                return $this->config[$param];
            }
        }
        $default = $this->defaults[$param]['default'];
        $this->set($param, $default);
        return $default;
    }

    public function set($param, $value = NULL) {
        $value = ( array_key_exists(strval($value), $this->translate_string_values) ) ? $this->translate_string_values[$value] : $value;
        if ( $this->_validate_param_value($param, $value) ) {
            if ( strpos($param, '__') ) {
                $parts = split('__', $param);
                $last_part = end($parts);
                $a = &$this->config;
                foreach ($parts as $part) {
                    if ( $part == $last_part ) {
                        $a[$part] = $value;
                    } else {
                        if ( !array_key_exists($part, $a) ) {
                            $a[$part] = array();
                        }
                        $a = &$a[$part];
                    }
                }
            } else {
                $this->config[$param] = $value;
            }
            return true;
        }
        return false;
    }

    private function _tracking_code($id) {
        global $wp;
        $host = "http://i.n.jwpltx.com/v1/wordpress/ping.gif";
        $tracking_url = JWP6_Plugin::$ping_image;
        $tracking_url.= "?e=features&s=" . urlencode(add_query_arg($wp->query_string, '', home_url($wp->request)));
        $tracking_url.= "&" . http_build_query($this->get_config());
        $javascript = '';
        if ($id == 0) {
            $javascript .= "

                function jwp6AddLoadEvent(func) {
                    var oldonload = window.onload;
                    if (typeof window.onload != 'function') {
                        window.onload = func;
                    } else {
                        window.onload = function() {
                            if (oldonload) {
                                oldonload();
                            }
                            func();
                        }
                    }
                }
            ";
        }
        $javascript .= "
            function ping{$id}() {
                var ping = new Image();
                ping.src = '{$tracking_url}';
            }

            jwp6AddLoadEvent(ping{$id});
        ";
        return $javascript;
    }

    private function _add_if_default_value($param, $value) {
        if (
            array_key_exists($param, JWP6_Plugin::$player_options)
            &&
            array_key_exists("discard_if_default", JWP6_Plugin::$player_options[$param]) 
            && 
            JWP6_Plugin::$player_options[$param]["discard_if_default"]
        ) {
            return false;
        }
        return true;
    }

    private function _add_embedcode_params($params, $parents = array()) {
        $embedcode = "";
        $po = JWP6_Plugin::$player_options;
        $last_param = end(array_keys($params));
        foreach ($params as $param => $value) {
            $new_parents = $parents;
            $new_parents[] = $param;
            if ( is_array($value) ) {
                $embedcode .= str_repeat("\t", count($parents));
                $embedcode .= "'{$param}': {\n";
                $embedcode .= $this->_add_embedcode_params($value, $new_parents);
                $embedcode .= ( $last_param == $param && count($parents) ) ? "}\n" : "},\n";
            } else {
                $check_param = ( count($parents) ) ? implode('__', $new_parents) : $param;
                if ( JWP6_Plugin::option_available($check_param) && $this->_add_if_default_value($check_param, $value) ) {
                    $stringval = null;
                    // See if it's a toggle.
                    if ( is_bool($value) ) {
                        if ( array_key_exists($check_param, $po) && array_key_exists('stringval', $po[$check_param])
                        ) {
                            $stringval = ( true == $value ) ? $po[$check_param]['stringval'] : null;
                        }
                        else {
                            $stringval = ( true == $value ) ? 'true' : 'false';
                        }
                    }
                    // an integer
                    else if ( is_int( $value ) ) {
                        $stringval = $value;
                    }
                    else {
                        $stringval = "'" . str_replace("'", "\'", $value) . "'";
                    }
                    // Print the value.
                    if ( ! is_null($stringval) ) {
                        $embedcode .= str_repeat("\t", count($parents));
                        $embedcode .= "'{$param}': {$stringval}";
                        $embedcode .= ( $last_param == $param && count($parents) ) ? "\n" : ",\n";
                    }
                }
            }
        }
        return $embedcode;
    }

    public function embedcode($id, $file = null, $playlist=null, $image = null, $download = null, $config = null) {
        // overwrite existing config with additional config from shortcode.
        if ( ! is_null($config) ) {
            foreach ($config as $param => $value) {
                $this->set($param, $value);
            }
        }
        unset($this->config['description']);
        $image = ( is_null($image) ) ? JWP6_Plugin::default_image_url() : $image;
        $embedcode = "
            <div class='jwplayer' id='jwplayer-{$id}'></div>
            <script type='text/javascript'>
        ";
        if ( get_option(JWP6 . 'allow_anonymous_tracking') ) { 
            $embedcode .= $this->_tracking_code($id);
        }
        $embedcode .= "
            jwplayer('jwplayer-{$id}').setup({
        ";
        $embedcode .= $this->_add_embedcode_params($this->config);
        if ( is_null($playlist) ) {
            $embedcode .= "'image': '{$image}',\n";
        }
        if ( ! is_null($file) && is_null($playlist) ) {
            $embedcode .= "'file': '{$file}'\n";
        }
        if ( ! is_null($playlist) ) {
            $embedcode .= "'playlist': '{$playlist}'\n";
        }
        $embedcode .= "
                });
            </script>
        ";
        return $embedcode;
    }

}

