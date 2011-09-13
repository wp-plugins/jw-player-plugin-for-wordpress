<?php

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

if (isset($_POST['Uninstall'])) {
  uninstall();
}

if (isset($_POST["category_config"])) {
  update_option(LONGTAIL_KEY . "category_mode", $_POST["category_config"]);
  update_option(LONGTAIL_KEY . "search_mode", $_POST["search_config"]);
  update_option(LONGTAIL_KEY . "tag_mode", $_POST["tag_config"]);
  update_option(LONGTAIL_KEY . "home_mode", $_POST["home_config"]);
}

function uninstall() {
  global $wpdb;

  $meta_query = "DELETE FROM $wpdb->postmeta WHERE meta_key LIKE '" . LONGTAIL_KEY . "%';";
  $post_query = "DELETE FROM $wpdb->posts WHERE post_type = 'jw_playlist';";

  $wpdb->query($meta_query);
  $wpdb->query($post_query);

  delete_option(LONGTAIL_KEY . "default");
  delete_option(LONGTAIL_KEY . "ootb");

  feedback_message(__('Tables and settings deleted, deactivate the plugin now'));
}

function feedback_message ($message, $timeout = 0) { ?>
  <div class="fade updated" id="message" onclick="this.parentNode.removeChild (this)">
    <p><strong><?php echo $message ?></strong></p>
  </div> <?php
}

?>
 
<div class="wrap">
  <h2><?php echo "JW Player Settings"; ?></h2>
  <form name="<?php echo LONGTAIL_KEY . "form" ?>" method="post" action="">
    <div id="poststuff">
      <div id="post-body">
        <div id="post-body-content">
          <div class="stuffbox">
            <h3 class="hndle"><span>Shortcode Settings</span></h3>
            <div class="inside" style="margin: 15px;">
              <span>Configure the source of the content for each page type.</span>
              <table class="form-table">
                <tr valign="top">
                  <th>Category Pages:</th>
                  <td>
                    <label for="category_excerpt">Excerpt</label>
                    <input id="category_excerpt" type="radio" value="excerpt" name="category_config" onclick="form.submit();" <?php checked("excerpt", get_option(LONGTAIL_KEY . "category_mode")); ?> />
                    <label for="category_content">Content</label>
                    <input id="category_content" type="radio" value="content" name="category_config" onclick="form.submit();" <?php checked("content", get_option(LONGTAIL_KEY . "category_mode")); ?> />
                    <label for="category_disable">Disable</label>
                    <input id="category_disable" type="radio" value="disable" name="category_config" onclick="form.submit();" <?php checked("disable", get_option(LONGTAIL_KEY . "category_mode")); ?> />
                    <span class="description">Configure JW Player shortcode behavior on category pages.</span>
                  </td>
                </tr>
                <tr>
                  <th>Search Pages:</th>
                  <td>
                    <label for="search_excerpt">Excerpt</label>
                    <input id="search_excerpt" type="radio" value="excerpt" name="search_config" onclick="form.submit();" <?php checked("excerpt", get_option(LONGTAIL_KEY . "search_mode")); ?> />
                    <label for="search_content">Content</label>
                    <input id="search_content" type="radio" value="content" name="search_config" onclick="form.submit();" <?php checked("content", get_option(LONGTAIL_KEY . "search_mode")); ?> />
                    <label for="search_disable">Disable</label>
                    <input id="search_disable" type="radio" value="disable" name="search_config" onclick="form.submit();" <?php checked("disable", get_option(LONGTAIL_KEY . "search_mode")); ?> />
                    <span class="description">Confgiure JW Player shortcode behavior on search result pages.</span>
                  </td>
                </tr>
                <tr>
                  <th>Tag Pages:</th>
                  <td>
                    <label for="tag_excerpt">Excerpt</label>
                    <input id="tag_excerpt" type="radio" value="excerpt" name="tag_config" onclick="form.submit();" <?php checked("excerpt", get_option(LONGTAIL_KEY . "tag_mode")); ?> />
                    <label for="tag_content">Content</label>
                    <input id="tag_content" type="radio" value="content" name="tag_config" onclick="form.submit();" <?php checked("content", get_option(LONGTAIL_KEY . "tag_mode")); ?> />
                    <label for="tag_disable">Disable</label>
                    <input id="tag_disable" type="radio" value="disable" name="tag_config" onclick="form.submit();" <?php checked("disable", get_option(LONGTAIL_KEY . "tag_mode")); ?> />
                    <span class="description">Confgiure JW Player shortcode behavior on tag pages.</span>
                  </td>
                </tr>
                <tr>
                  <th>Home Page:</th>
                  <td>
                    <label for="home_excerpt">Excerpt</label>
                    <input id="home_excerpt" type="radio" value="excerpt" name="home_config" onclick="form.submit();" <?php checked("excerpt", get_option(LONGTAIL_KEY . "home_mode")); ?> />
                    <label for="home_content">Content</label>
                    <input id="home_content" type="radio" value="content" name="home_config" onclick="form.submit();" <?php checked("content", get_option(LONGTAIL_KEY . "home_mode")); ?> />
                    <label for="home_disable">Disable</label>
                    <input id="home_disable" type="radio" value="disable" name="home_config" onclick="form.submit();" <?php checked("disable", get_option(LONGTAIL_KEY . "home_mode")); ?> />
                    <span class="description">Confgiure JW Player shortcode behavior on the home page.</span>
                  </td>
                </tr>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div id="poststuff">
      <div id="post-body">
        <div id="post-body-content">
          <div class="stuffbox">
            <h3 class="hndle"><span>JW Player Plugin for WordPress Uninstall</span></h3>
            <div class="inside" style="margin: 15px;">
              <table>
                <tr valign="top">
                  <td>
                    <div>
                      <p><?php _e('To fully remove the plugin, click the Uninstall button.  Deactivating without uninstalling will not remove the data created by the plugin.') ;?></p>
                    </div>
                    <p><span style="color: red; "><strong><?php _e('WARNING:') ;?></strong><br />
                    <?php _e('This cannot be undone.  Since this is deleting data from your database, it is recommended that you create a backup.') ;?></span></p>
                    <div align="left">
                      <input type="submit" name="Uninstall" class="button-secondary delete" value="<?php _e('Uninstall plugin') ?>" onclick="return confirm('<?php _e('You are about to Uninstall this plugin from WordPress.\nThis action is not reversible.\n\nChoose [Cancel] to Stop, [OK] to Uninstall.\n'); ?>');"/>
                    </div>
                  </td>
                </tr>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </form>
</div>