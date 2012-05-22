<?php

$p_items = array();
$playlists = jwplayer_get_playlists();
$current_playlist = $playlists[0]->ID;

if (isset($_GET["p_items"])) {
  $p_items = json_decode(str_replace("\\", "", $_GET["p_items"]));
} else if (isset($_POST["playlist_items"]) && $_POST["old_playlist"] == $current_playlist) {
  $p_items = json_decode(str_replace("\\", "", $_POST["playlist_items"]));
} else {
  $p_items = explode(",", get_post_meta($current_playlist, LONGTAIL_KEY . "playlist_items", true));
}

$playlist_items = get_jw_playlist_items($p_items);
$media_items = get_jw_media_items(1, "date", "DESC", $p_items);

function get_jw_media_items($page, $column = "date", $sort = "DESC", $playlist_items = array()) {
  $args = array(
    'post_parent' => null,
    'posts_per_page' => 10,
    'paged' => $page,
    'post_status' => 'inherit',
    'post_type' => 'attachment',
    'orderby' => $column,
    'order' => $sort,
    'post__not_in' => $playlist_items
  );
  return new WP_Query($args);
}

function get_jw_playlist_items($playlist_item_ids = array()) {
  $args = array(
    'post_parent' => null,
    'post_status' => 'inherit',
    'post_type' => 'attachment',
    'post__in' => $playlist_item_ids
  );
  return new WP_Query($args);
}

function jwplayer_get_playlists() {
  $playlist = array(
    "post_type" => "jw_playlist",
    "post_status" => null,
    "post_parent" => null,
    "nopaging" => true,
  );
  return query_posts($playlist);
}

?>

<div class="wrap">
  <h2><?php echo "JW Player Plugin Playlist Manager"; ?></h2>

  <script type="text/javascript">
    function createPlaylistHandler() {
      var playlistName = document.forms[0]["<?php echo LONGTAIL_KEY . "playlist_name"; ?>"];
      if (playlistName.value == "") {
        alert("Your playlist must have a valid name.");
        return false;
      }
      return true;
    }

    function deletePlaylistHandler() {
      return confirm("Are you sure wish to delete the Playlist?");
    }
  </script>

  <form action="">
    <div>
      <div style="width: 900px;">
        <p class="ml-submit">
          <label for="<?php echo LONGTAIL_KEY . "playlist_name"; ?>"><?php _e("New Playlist:"); ?></label>
          <input type="text" value="" id="<?php echo LONGTAIL_KEY . "playlist_name"; ?>" name="<?php echo LONGTAIL_KEY . "playlist_name"; ?>" />
          <input type="submit" class="button savebutton" style="" name="<?php echo LONGTAIL_KEY . "playlist_create"; ?>" id="<?php echo LONGTAIL_KEY . "playlist_create"; ?>" value="<?php esc_attr_e("Create Playlist"); ?>" onclick="return createPlaylistHandler()" />
        </p>
        <div class="ml-submit" style="padding: 0 0; float: left;">
          <div class="alignleft actions">
            <div class="hide-if-no-js">
              <label for="<?php echo LONGTAIL_KEY . "playlist_select"; ?>"><?php _e("Playlist:"); ?></label>
              <select onchange="this.form.submit()" id="<?php echo LONGTAIL_KEY . "playlist_select"; ?>" name="<?php echo LONGTAIL_KEY . "playlist_select"; ?>">
                <?php foreach ($playlists as $playlist_list) { ?>
                <option value="<?php echo $playlist_list->ID; ?>" <?php selected($playlist_list->ID, $current_playlist); ?>>
                  <?php echo $playlist_list->post_title; ?>
                </option>
                <?php } ?>
              </select>
              <input type="submit" class="button savebutton" name="save" id="save-all" value="<?php esc_attr_e( 'Save' ); ?>" />
              <input type="submit" class="button savebutton" name="delete" id="delete-all" value="<?php esc_attr_e( 'Delete' ); ?>" onclick="return deletePlaylistHandler()" />
              <input type="hidden" name="type" value="<?php echo esc_attr( $GLOBALS['type'] ); ?>" />
              <input type="hidden" name="tab" value="<?php echo esc_attr( $GLOBALS['tab'] ); ?>" />
              <input type="hidden" id="playlist_items" name="playlist_items" value='<?php echo json_encode($p_items); ?>' />
              <input type="hidden" id="old_playlist" name="old_playlist" value="<?php echo $current_playlist; ?>" />
            </div>
          </div>
        </div>
        <div style="float: right;">
          <label class="screen-reader-text" for="media-search-input">Search Media:</label>
          <input type="text" id="media-search-input" name="s" value="">
          <input type="submit" name="" id="search-submit" class="button" value="Search Media">
        </div>
        <div style="clear: both;"></div>
      </div>
      <div style="width: 900px;">
        <div style="width: 425px; float: left;">
          <table class="wp-list-table widefat fixed media" cellspacing="0">
            <thead>
              <tr>
                <th scope="col" id="playlist_icon" class="manage-column column-icon" style=""></th>
                <th scope="col" id="playlist_title" class="manage-column column-title" style=""><span>File</span></th>
                <th scope="col" id="playlist_author" class="manage-column column-author sortable desc" style="width: 20%;"><span>Author</span></th>
                <th scope="col" id="playlist_date" class="manage-column column-date sortable asc" style="width: 20%;"><span>Date</span></th>
              </tr>
            </thead>

            <tbody id="playlist_the-list">
              <?php while ($playlist_items->have_posts()) { ?>
                <?php $playlist_item = $playlist_items->next_post(); ?>
                <tr id="post-<?php echo $playlist_item->ID; ?>" class="alternate author-self status-inherit" valign="top">
                  <td class="column-icon media-icon"><a
                    href="http://localhost/wordpress/wp-admin/media.php?attachment_id=<?php echo $playlist_item->ID; ?>&amp;action=edit"
                    title="Edit “<?php echo $playlist_item->post_title; ?>”">
                    <img width="46" height="60" src="http://localhost/wordpress/wp-includes/images/crystal/video.png"
                         class="attachment-80x60" alt="<?php echo $playlist_item->post_title; ?>" title="<?php echo $playlist_item->post_title; ?>"> </a>

                  </td>
                  <td class="title column-title"><strong>
                    <a href="http://localhost/wordpress/wp-admin/media.php?attachment_id=<?php echo $playlist_item->ID; ?>&amp;action=edit"
                       title="Edit “<?php echo $playlist_item->post_title; ?>”">
                      <?php echo $playlist_item->post_title; ?></a>
                  </strong>

                    <p>
                      <?php echo get_post_mime_type($playlist_item->ID); ?> </p>

                    <div class="row-actions"><span class="edit"><a
                      href="http://localhost/wordpress/wp-admin/media.php?attachment_id=<?php echo $playlist_item->ID; ?>&amp;action=edit">Edit</a> | </span><span
                      class="delete"><a class="submitdelete" onclick="return showNotice.warn();" href="">Remove</a> | </span><span
                      class="view"><a href="http://localhost/wordpress/?attachment_id=<?php echo $playlist_item->ID; ?>" title="View “Test”" rel="permalink">View</a></span>
                    </div>
                  </td>
                  <td class="author column-author"><?php echo get_post_meta($playlist_item->ID, LONGTAIL_KEY . "creator", true); ?></td>
                  <td class="date column-date"><?php echo mysql2date( __( 'Y/m/d' ), $playlist_item->post_date); ?></td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
        <div style="padding-left: 50px;; width: 425px; float: left;">
          <table class="wp-list-table widefat fixed media" cellspacing="0">
            <thead>
              <tr>
                <th scope="col" id="icon" class="manage-column column-icon" style=""></th>
                <th scope="col" id="title" class="manage-column column-title sortable desc" style=""><a
                  href="http://localhost/wordpress/wp-admin/upload.php?orderby=title&amp;order=asc"><span>File</span><span
                  class="sorting-indicator"></span></a></th>
                <th scope="col" id="author" class="manage-column column-author sortable desc" style="width: 20%;"><a
                  href="http://localhost/wordpress/wp-admin/upload.php?orderby=author&amp;order=asc"><span>Author</span><span
                  class="sorting-indicator"></span></a></th>
                <th scope="col" id="date" class="manage-column column-date sortable asc" style="width: 20%;"><a
                  href="http://localhost/wordpress/wp-admin/upload.php?orderby=date&amp;order=desc"><span>Date</span><span
                  class="sorting-indicator"></span></a></th>
              </tr>
            </thead>

            <tbody id="the-list">
              <?php while ($media_items->have_posts()) { ?>
              <?php $media_item = $media_items->next_post(); ?>
              <tr id="post-<?php echo $media_item->ID; ?>" class="alternate author-self status-inherit" valign="top">
                <td class="column-icon media-icon"><a
                  href="http://localhost/wordpress/wp-admin/media.php?attachment_id=<?php echo $media_item->ID; ?>&amp;action=edit"
                  title="Edit “<?php echo $media_item->post_title; ?>”">
                  <img width="46" height="60" src="http://localhost/wordpress/wp-includes/images/crystal/video.png"
                       class="attachment-80x60" alt="<?php echo $media_item->post_title; ?>" title="<?php echo $media_item->post_title; ?>"> </a>

                </td>
                <td class="title column-title"><strong>
                  <a href="http://localhost/wordpress/wp-admin/media.php?attachment_id=<?php echo $media_item->ID; ?>&amp;action=edit"
                     title="Edit “<?php echo $media_item->post_title; ?>”">
                    <?php echo $media_item->post_title; ?></a>
                </strong>

                  <p>
                    <?php echo get_post_mime_type($media_item->ID); ?> </p>

                  <div class="row-actions"><span class="edit"><a
                    href="http://localhost/wordpress/wp-admin/media.php?attachment_id=<?php echo $media_item->ID; ?>&amp;action=edit">Edit</a> | </span><span
                    class="delete"><a class="submitdelete" onclick="return showNotice.warn();" href="">Remove</a> | </span><span
                    class="view"><a href="http://localhost/wordpress/?attachment_id=<?php echo $media_item->ID; ?>" title="View “Test”" rel="permalink">View</a></span>
                  </div>
                </td>
                <td class="author column-author"><?php echo get_post_meta($media_item->ID, LONGTAIL_KEY . "creator", true); ?></td>
                <td class="date column-date"><?php echo mysql2date( __( 'Y/m/d' ), $media_item->post_date); ?></td>
              </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
        <div style="clear: both;"></div>
      </div>
    </div>
  </form>
</div>