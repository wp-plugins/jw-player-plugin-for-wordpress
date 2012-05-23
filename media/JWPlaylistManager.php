<?php

$p_items = array();
$playlists = jwplayer_get_playlists();
$form_action_url = admin_url("upload.php?page=jwplayer-playlists");

$new_playlist_id = -1;
if (isset($_POST[LONGTAIL_KEY . "playlist_create"]) || isset($_POST["save"])) {
  $post_title = $_POST[LONGTAIL_KEY . "playlist_name"];
  $new_playlist = array();
  $new_playlist["post_title"] = $post_title;
  $new_playlist["post_type"] = "jw_playlist";
  $new_playlist["post_status"] = null;
  $new_playlist["post_parent"] = null;
  if (isset($_POST["save"])) {
    $new_playlist_id = isset($_POST[LONGTAIL_KEY . "playlist_select"]) ? $_POST[LONGTAIL_KEY . "playlist_select"] : $playlists[0]->ID;
  } else {
    $new_playlist_id = wp_insert_post($new_playlist);
    $playlists = jwplayer_get_playlists();
  }
  $current_playlist = $new_playlist_id;
} else if (isset($_POST["delete"])) {
  wp_delete_post($_POST[LONGTAIL_KEY . "playlist_select"]);
  $playlists = jwplayer_get_playlists();
  $current_playlist = $playlists[0]->ID;
}

if (!isset($current_playlist)) {
  if (isset($_POST[LONGTAIL_KEY . "playlist_select"])) {
    $current_playlist = $_POST[LONGTAIL_KEY . "playlist_select"];
  } else if (isset($_GET["playlist"])) {
    $current_playlist = $_GET["playlist"];
  } else if (!empty($playlists)) {
    $current_playlist = $playlists[0]->ID;
  } else {
    $current_playlist = -1;
  }
}

if (isset($_GET["p_items"])) {
  $p_items = json_decode(str_replace("\\", "", $_GET["p_items"]));
} else if (isset($_POST["playlist_items"]) && $_POST["old_playlist"] == $current_playlist) {
  $p_items = json_decode(str_replace("\\", "", $_POST["playlist_items"]));
} else {
  $p_items = explode(",", get_post_meta($current_playlist, LONGTAIL_KEY . "playlist_items", true));
}

update_post_meta($new_playlist_id, LONGTAIL_KEY . "playlist_items", implode(",", $p_items));

$playlist_items = get_jw_playlist_items($p_items);
$paged = isset($_GET['paged']) ? $_GET['paged'] : 1;
$media_items = get_jw_media_items($paged, "date", "DESC", $p_items);
if ($paged > 1 && !$media_items->have_posts()) {
  $paged = 1;
  $media_items = get_jw_media_items($paged, "date", "DESC", $p_items);
}
$total = ceil($media_items->found_posts / 10);

$page_links = paginate_links( array(
  'base' => add_query_arg( 'paged', '%#%' ),
  'format' => '',
  'prev_text' => __('&laquo;'),
  'next_text' => __('&raquo;'),
  'total' => $total,
  'current' => $paged,
  'add_args' => array('playlist' => $current_playlist)
));

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
  $items = new WP_Query($args);
  $ordered_items = array();
  foreach ($playlist_item_ids as $playlist_item_id) {
    while ($items->have_posts()) {
      $item = $items->next_post();
      if ($item->ID == $playlist_item_id) {
        $ordered_items[$playlist_item_id] = $item;
      }
    }
  }
  return $ordered_items;
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
    jQuery(document).ready(function() {
      jQuery("#playlist_the-list").sortable({
        items: "tr:not(#no-posts)",
        axis: "y",
        sort: function() {
          jQuery(this).removeClass("ui-state-default");
        },
        stop: function(e, ui) {
          updatePlaylist();
        }
      }).droppable({
          activeClass: "ui-state-default",
          hoverClass: "ui-state-hover",
          accept: ":not(.ui-sortable-helper)",
          drop: function(event, ui) {
            jQuery(this).find(".placeholder").remove();
            jQuery("<tr id='" + ui.draggable[0].id + "' class='alternate author-self status-inherit'></tr>").html(ui.draggable.html()).appendTo(this);
            jQuery(ui.draggable).remove();
            jQuery("#no-posts").remove();
            updatePlaylist();
          }
        });
      jQuery("#the-list tr").draggable({
        helper: "clone"
      });
    });

    function updatePlaylist() {
      var desc = false;
      var item_list = document.getElementById("playlist_items");
      var p_items = new Array();
      var old_p_items =  eval('(' + item_list.value + ')');
      if (old_p_items[0] == "") {old_p_items = new Array();}
      var all = jQuery('#playlist_the-list').sortable('toArray'), len = all.length;
      jQuery.each(all, function(i, id) {
        var order = desc ? (len - i) : (1 + i);
        jQuery('#' + id + ' .menu_order input').val(order);
        p_items.push(id.replace("post-", ""));
      });
      update_page_numbers(p_items, old_p_items);
      document.getElementById("playlist_items").value = dump(p_items);
    }

    function update_page_numbers(p_items, old_p_items) {
      var pages = jQuery(".page-numbers");
      var j = 0;
      for (j = 0; j < pages.length; j++) {
        var page = pages[j];
        if (page.href) {
          page.href = page.href.replace(encodeURI("&p_items=" + dump(old_p_items)), "");
          page.href = page.href + encodeURI("&p_items=" + dump(p_items));
        }
      }
    }

    function dump (object, depth) {
      if (object == null) {
        return 'null';
      } else if (typeof(object) != 'object') {
        if (typeof(object) == 'string'){
          return"\""+object+"\"";
        }
        return object;
      }
      var type = typeOf(object);
      (depth == undefined) ? depth = 1 : depth++;
      var result = (type == "array") ? "[" : "{";
      var loopRan = false;
      if (type == "array") {
        for (var i = 0; i < object.length; i++) {
          loopRan = true;
          result += dump(object[i], depth)+", ";
        }
      } else {
        for (var j in object) {
          loopRan = true;
          if (type == "object") { result += "\""+j+"\": "};
          result += dump(object[j], depth)+", ";
        }
      }
      if (loopRan) {
        result = result.substring(0, result.length-1-depth);
      }
      result  += (type == "array") ? "]" : "}";
      return result;
    }

    function typeOf(value) {
      var s = typeof value;
      if (s === 'object') {
        if (value) {
          if (value instanceof Array) {
            s = 'array';
          }
        } else {
          s = 'null';
        }
      }
      return s;
    }

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

    function deletePlaylistItem(object) {
      jQuery(object).parents("tr").appendTo("#the-list");
      jQuery("#the-list tr").draggable({
        helper: "clone"
      });
      updatePlaylist();
    }

  </script>

  <form action="<?php echo $form_action_url; ?>" method="post">
    <div>
      <div style="width: 1000px;">
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
      <div style="width: 1000px; padding-top: 10px;">
        <div style="width: 475px; float: left;">
          <table class="wp-list-table widefat fixed media" cellspacing="0">
            <thead>
              <tr>
                <th scope="col" id="playlist_icon" class="manage-column column-icon" style=""></th>
                <th scope="col" id="playlist_title" class="manage-column column-title" style=""><span>File</span></th>
                <th scope="col" id="playlist_author" class="manage-column column-author sortable desc" style="width: 20%; padding: 7px 7px 8px;"><span>Author</span></th>
                <th scope="col" id="playlist_date" class="manage-column column-date sortable asc" style="width: 20%; padding: 7px 7px 8px;"><span>Date</span></th>
              </tr>
            </thead>

            <tbody id="playlist_the-list">
              <?php foreach ($playlist_items as $key => $playlist_item) { ?>
                <tr id="post-<?php echo $playlist_item->ID; ?>" class="alternate author-self status-inherit" valign="top" style="width: 475px;">
                  <td class="column-icon media-icon"><a
                    href="http://localhost/wordpress/wp-admin/media.php?attachment_id=<?php echo $playlist_item->ID; ?>&amp;action=edit"
                    title="Edit “<?php echo $playlist_item->post_title; ?>”">
                    <img width="24" height="32" src="http://localhost/wordpress/wp-includes/images/crystal/video.png"
                         class="attachment-80x60" alt="<?php echo $playlist_item->post_title; ?>" title="<?php echo $playlist_item->post_title; ?>"> </a>

                  </td>
                  <td class="title column-title"><strong>
                    <a href="http://localhost/wordpress/wp-admin/media.php?attachment_id=<?php echo $playlist_item->ID; ?>&amp;action=edit"
                       title="Edit “<?php echo $playlist_item->post_title; ?>”">
                      <?php echo $playlist_item->post_title; ?></a>
                  </strong>
                    <div class="row-actions"><span class="edit"><a
                      href="http://localhost/wordpress/wp-admin/media.php?attachment_id=<?php echo $playlist_item->ID; ?>&amp;action=edit">Edit</a> | </span><span
                      class="delete"><a class="submitdelete" style="cursor: pointer;" onclick="deletePlaylistItem(this);">Remove</a> | </span><span
                      class="view"><a href="http://localhost/wordpress/?attachment_id=<?php echo $playlist_item->ID; ?>" title="View “Test”" rel="permalink">View</a></span>
                    </div>
                  </td>
                  <td class="author column-author"><?php echo get_post_meta($playlist_item->ID, LONGTAIL_KEY . "creator", true); ?></td>
                  <td class="date column-date"><?php echo mysql2date( __( 'Y/m/d' ), $playlist_item->post_date); ?></td>
                </tr>
              <?php } ?>
              <?php if (empty($playlist_items) && !empty($playlists)) { ?>
                <tr id="no-posts" class="alternate author-self status-inherit">
                  <td colspan="4" style="text-align: center; height: 50px;">The playlist does not have any items.</td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
        <div style="padding-left: 50px; width: 475px; float: left;">
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
              <tr id="post-<?php echo $media_item->ID; ?>" class="alternate author-self status-inherit" valign="top" style="width: 475px;">
                <td class="column-icon media-icon"><a
                  href="http://localhost/wordpress/wp-admin/media.php?attachment_id=<?php echo $media_item->ID; ?>&amp;action=edit"
                  title="Edit “<?php echo $media_item->post_title; ?>”">
                  <img width="24" height="32" src="http://localhost/wordpress/wp-includes/images/crystal/video.png"
                       class="attachment-80x60" alt="<?php echo $media_item->post_title; ?>" title="<?php echo $media_item->post_title; ?>"> </a>

                </td>
                <td class="title column-title"><strong>
                  <a href="http://localhost/wordpress/wp-admin/media.php?attachment_id=<?php echo $media_item->ID; ?>&amp;action=edit"
                     title="Edit “<?php echo $media_item->post_title; ?>”">
                    <?php echo $media_item->post_title; ?></a>
                </strong>
                  <div class="row-actions"><span class="edit"><a
                    href="http://localhost/wordpress/wp-admin/media.php?attachment_id=<?php echo $media_item->ID; ?>&amp;action=edit">Edit</a> | </span><span
                    class="view"><a href="http://localhost/wordpress/?attachment_id=<?php echo $media_item->ID; ?>" title="View “Test”" rel="permalink">View</a></span>
                  </div>
                </td>
                <td class="author column-author"><?php echo get_post_meta($media_item->ID, LONGTAIL_KEY . "creator", true); ?></td>
                <td class="date column-date"><?php echo mysql2date( __( 'Y/m/d' ), $media_item->post_date); ?></td>
              </tr>
              <?php } ?>
            </tbody>
          </table>
          <?php if ($page_links) { ?>
              <div class="tablenav">
                <div class='tablenav-pages'>
                  <span style="font-size: 13px;"><?php _e("Available Media:"); ?></span>
                  <?php echo $page_links; ?>
                </div>
              </div>
          <?php }?>
        </div>
        <div style="clear: both;"></div>
      </div>
    </div>
  </form>
</div>