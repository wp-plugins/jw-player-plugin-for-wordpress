<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

if(isset($_POST["Non_commercial"])) {
  player_download();
} else if(isset($_POST["Commercial"])) {
  player_upload();
}

function player_download() {
  $player_handle = fopen(LongTailFramework::getPlayerPath(), "w");
  $yt_handle = fopen(str_replace("player.swf", "yt.swf", LongTailFramework::getPlayerPath()), "w");
  $player = wp_remote_retrieve_body(wp_remote_get("http://player.longtailvideo.com/player.swf"));
  $yt = wp_remote_retrieve_body(wp_remote_get("http://player.longtailvideo.comp/yt.swf"));
  fwrite($player_handle, $player);
  fwrite($yt_handle, $yt);
  fclose($player_handle);
  fclose($yt_handle);
}

function player_upload() {
  if ($_FILES["file"]["type"] == "application/x-shockwave-flash") {
    move_uploaded_file($_FILES["file"]["tmp_name"], LongTailFramework::getPlayerPath());
  }
}

function default_state() { ?>
  <form name="<?php echo LONGTAIL_KEY . "form"; ?>" method="post" action="" enctype="multipart/form-data">
    <table class="form-table">
      <tr>
        <td colspan="2">
          <p>
            <span class="description"><?php echo "For commercial use purchase a license & upload your latest commercial player."; ?></span>
          </p>
          <p>
            <label for="file"><?php echo "JW Player:"; ?></label>
            <input id="file" type="file" name="file" />
            <input class="button-secondary" type="submit" name="Commercial" value="Upload" />
          </p>
        </td>
      </tr>
      <tr>
        <td colspan="2">
          <p>
            <span class="description"><?php echo "For non-commercial, click here to check for and install the latest JW Player."; ?></span>
          </p>
          <p>
            <input class="button-secondary" type="submit" name="Non_commercial" value="Install JW Player" />
          </p>
        </td>
      </tr>
    </table>
  </form>
<?php }

function download_state() {
  $atts = array(
    "file" => "http://content.longtailvideo.com/videos/bunny.flv",
    "image" => "http://content.longtailvideo.com/videos/bunny.jpg"
  );
  $swf = LongTailFramework::generateSWFObject($atts); ?>
  <form name="<?php echo LONGTAIL_KEY . "form"; ?>" method="post" action="admin.php?page=jwplayer">
    <table class="form-table">
      <tr>
        <td colspan="2">
          <p>
            <span class="description"><?php echo "Non-commercial JW Player download complete."; ?></span>
          </p>
          <p>
            <script type="text/javascript">
              var player;
              function playerReady(object) {
                player = document.getElementById(object.id);
                document.getElementById("version").value = player.getConfig().version;
              }
            </script>
            <?php echo $swf->generateEmbedScript(); ?>
          </p>
          <p>
            <span class="description"><?php echo "Please verify that the player is working correctly."; ?></span>
          </p>
          <p>
            <input class="button-secondary" type="submit" name="Verify" value="Verify" />
            <input id="version" class="hidden" type="text" name="Version" />
          </p>
        </td>
      </tr>
    </table>
  </form>
<?php }

function upload_state() {
  $atts = array(
    "file" => "http://content.longtailvideo.com/videos/bunny.flv",
    "image" => "http://content.longtailvideo.com/videos/bunny.jpg"
  );
  $swf = LongTailFramework::generateSWFObject($atts); ?>
  <form name="<?php echo LONGTAIL_KEY . "form"; ?>" method="post" action="admin.php?page=jwplayer">
    <table class="form-table">
      <tr>
        <td colspan="2">
          <p>
            <span class="description"><?php echo "Commercial JW Player upload complete."; ?></span>
          </p>
          <p>
            <script type="text/javascript">
              var player;
              function playerReady(object) {
                player = document.getElementById(object.id);
                document.getElementById("version").value = player.getConfig().version;
              }
            </script>
            <?php echo $swf->generateEmbedScript(); ?>
          </p>
          <p>
            <span class="description"><?php echo "Please verify that the player is working correctly."; ?></span>
          </p>
          <p>
            <input class="button-secondary" type="submit" name="Verify" value="Verify" />
            <input id="version" class="hidden" type="text" name="Version" />
          </p>
        </td>
      </tr>
    </table>
  </form>
<?php }

?>

<div class="wrap">
  <h2><?php echo "JW Player Install & Update"; ?></h2>
</div>

<?php if (isset($_POST["Non_commercial"])) { 
  download_state();
} else if (isset($_POST["Commercial"])) {
  upload_state();
} else {
  default_state();
} ?>
