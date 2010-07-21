<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

?>

<div class="wrap">
  <h2><?php echo "JW Player Install & Update"; ?></h2>
  <p/>

<?php

if (isset($_POST["Non_commercial"])) {
  download_state();
} else if (isset($_POST["Commercial"])) {
  upload_state();
} else {
  default_state();
}

function yt_download() {
  $yt_handle = fopen(str_replace("player.swf", "yt.swf", LongTailFramework::getPlayerPath()), "w");
  $yt = wp_remote_retrieve_body(wp_remote_get("http://player.longtailvideo.com/yt.swf"));
  fwrite($yt_handle, $yt);
  fclose($yt_handle);
}

function player_download() {
  $player_handle = fopen(LongTailFramework::getPlayerPath(), "w");  
  $player = wp_remote_retrieve_body(wp_remote_get("http://player.longtailvideo.com/player.swf"));
  fwrite($player_handle, $player);
  fclose($player_handle);
  yt_download();
}

function player_upload() {
  if ($_FILES["file"]["type"] == "application/x-shockwave-flash") {
    move_uploaded_file($_FILES["file"]["tmp_name"], LongTailFramework::getTempPlayerPath());
    return true;
  }
  return false;
}

function default_state() {
  upload_section();
  download_section();
}

function download_state() {
  player_download(); ?>
  <form name="<?php echo LONGTAIL_KEY . "form"; ?>" method="post" action="">
    <table class="form-table">
      <tr>
        <td colspan="2">
          <p>
            <span class="description"><?php echo "Non-commercial JW Player download complete."; ?></span>
          </p>
          <?php embed_demo_player(true); ?>
        </td>
      </tr>
    </table>
  </form>
<?php }

function upload_state() {
  if (player_upload()) { ?>
  <div id="info" class="fade updated" style="display: none;">
    <p><strong><?php echo "JW Player detected."; ?></strong></p>
  </div>
  <div id="error" class="error fade" style="display: none;">
    <p><strong><?php echo "JW Player was not detected."; ?></strong></p>
  </div>
  <form name="<?php echo LONGTAIL_KEY . "form"; ?>" method="post" action="">
    <table class="form-table">
      <tr>
        <td colspan="2">
          <p>
            <span id="upload_message" class="description"><?php echo "SWF Upload complete.  Checking for JW Player..."; ?></span>
          </p>
          <?php embed_demo_player(); ?>
        </td>
      </tr>
    </table>
  </form>
  <?php } else {
    error_message("Invalid file type uploaded.");
    default_state();
  }
}

function embed_demo_player($download = false) {
  $atts = array(
    "file" => "http://content.longtailvideo.com/videos/bunny.flv",
    "image" => "http://content.longtailvideo.com/videos/bunny.jpg"
  );
  $swf = $download ? LongTailFramework::generateSWFObject($atts) : LongTailFramework::generateTempSWFObject($atts); ?>
  <script type="text/javascript">
    var player, t;

    jQuery(document).ready(function() {
      t = setTimeout(playerNotReady, 2000);
    });

    function playerNotReady() {
      var data = {
        action: "verify_player",
        version: null,
        type: <?php echo (int) $download; ?>
      };
      document.getElementById("version").value = null;
      document.getElementById("type").value = <?php echo (int) $download; ?>;
      jQuery.post(ajaxurl, data, function(response) {
        var download = <?php echo (int) $download; ?>;
        if (!download) {
          document.getElementById("error").style.display = "block";
        }
      });
    }

    function playerReady(object) {
      player = document.getElementById(object.id);
      var data = {
        action: "verify_player",
        version: player.getConfig().version,
        type: <?php echo (int) $download; ?>
      };
      clearTimeout(t);
      document.getElementById("version").value = player.getConfig().version;
      document.getElementById("type").value = <?php echo (int) $download; ?>;
      jQuery.post(ajaxurl, data, function(response) {
        var download = <?php echo (int) $download; ?>;
        if (!download) {
            document.getElementById("info").style.display = "block";
        }
      });
    }
  </script>
  <?php echo $swf->generateEmbedScript(); ?>
  <input id="type" class="hidden" type="text" name="Type" />
  <input id="version" class="hidden" type="text" name="Version" />
<?php }

function upload_section() { ?>
  <form name="<?php echo LONGTAIL_KEY . "form"; ?>" method="post" action="" enctype="multipart/form-data" onsubmit="return fileValidation();">
    <div id="poststuff">
      <div id="post-body">
        <div id="post-body-content">
          <div class="stuffbox">
            <h3 class="hndle"><span><?php echo "Upload Commercial Player"; ?></span></h3>
            <div class="inside" style="margin: 10px;">
              <script type="text/javascript">
                function fileValidation() {
                  var file = document.getElementById("file").value;
                  var extension = file.substring(file.length - 4, file.length);
                  if (extension === ".swf") {
                    return true;
                  } else {
                    alert("File must be a SWF.")
                    return false;
                  }
                }
              </script>
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
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </form>
<?php }

function download_section() { ?>
  <form name="<?php echo LONGTAIL_KEY . "form"; ?>" method="post" action="">
    <div id="poststuff">
      <div id="post-body">
        <div id="post-body-content">
          <div class="stuffbox">
            <h3 class="hndle"><span><?php echo "Download Non-commercial Player"; ?></span></h3>
            <div class="inside" style="margin: 10px;">
              <table class="form-table">
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
            </div>
          </div>
        </div>
      </div>
    </div>
  </form>
<?php } ?>

</div>
