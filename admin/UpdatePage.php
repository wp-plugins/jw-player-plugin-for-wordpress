<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

define(DOWNLOAD_ERROR, "Download failed.");
define(WRITE_ERROR, "Write failed");
define(ZIP_ERROR, "Zip classes missing");
define(SUCCESS, "Success");

?>

<div class="wrap">

<?php

if (isset($_POST["Non_commercial"]) || isset($_POST["Install"])) {
  download_state();
} else if (isset($_POST["Commercial"])) {
  upload_state();
} else {
  default_state();
}

function player_download() {
  
  $player_package = download_url("http://www.longtailvideo.com/wp/jwplayer.zip");
  if (is_wp_error($player_package)) {
    return DOWNLOAD_ERROR;
  }

  if (!class_exists("ZipArchive")) {
    return ZIP_ERROR;
  }

  $zip = new ZipArchive();  
  if ($zip->open($player_package)) {
    $contents = "";
    $fp = $zip->getStream("mediaplayer-5.2/player.swf");
    if (!$fp) return WRITE_ERROR;
    while(!feof($fp)) {
      $contents .= fread($fp, 2);
    }
    fclose($fp);
    $result = file_put_contents(LongTailFramework::getPrimaryPlayerPath(), $contents);
    if (!$result) {
      return WRITE_ERROR;
    }
    chmod(LongTailFramework::getPrimaryPlayerPath(), 0777);
    $contents = "";
    $fp = $zip->getStream("mediaplayer-5.2/yt.swf");
    if (!$fp) return WRITE_ERROR;
    while (!feof($fp)) {
      $contents .= fread($fp, 2);
    }
    fclose($fp);
    $result = file_put_contents(str_replace("player.swf", "yt.swf", LongTailFramework::getPrimaryPlayerPath()), $contents);
    if (!$result) {
      return WRITE_ERROR;
    }
    chmod(str_replace("player.swf", "yt.swf", LongTailFramework::getPrimaryPlayerPath()), 0777);
    $zip->close();
  }

  unlink($player_package);
  return SUCCESS;
}

function player_upload() {
  if ($_FILES["file"]["type"] == "application/x-shockwave-flash") {
    move_uploaded_file($_FILES["file"]["tmp_name"], LongTailFramework::getTempPlayerPath());
    return true;
  }
  return false;
}

function default_state() { ?>
  <h2><?php echo "JW Player Upgrade"; ?></h2>
  <p/> <?php
  upload_section();
  download_section();
}

function download_state() { ?>
  <h2><?php echo "JW Player Install"; ?></h2>
  <p/>
  <?php
  $result = player_download();
  if ($result == SUCCESS) { ?>
  <div id="info" class="fade updated">
    <p><strong><span id="player_version"><?php echo "Successfully downloaded and installed the latest player version, JW Player "; ?></span></strong></p>
    <p><?php echo "If you have a specific version of the JW Player you wish to install (eg. licensed version), then you can install it using the <a href='admin.php?page=jwplayer-update'>upgrade page</a>."; ?></p>
  </div>
  <form name="<?php echo LONGTAIL_KEY . "form"; ?>" method="post" action="">
    <table class="form-table">
      <tr>
        <td colspan="2">
          <?php embed_demo_player(true); ?>
        </td>
      </tr>
    </table>
  </form>
  <?php } else if ($result == DOWNLOAD_ERROR) {
    error_message("Not able to download JW Player.  Please check your internet connection. <br/> If you already have the JW Player then you can install it using the <a href='admin.php?page=jwplayer-update'>upgrade page</a>.");
  } else if ($result == WRITE_ERROR) {
    error_message("Not able to install JW Player.  Please grant write access to the jw-player-plugin-for-wordpress/player directory and visit the <a href='admin.php?page=jwplayer-update'>upgrade page</a>.");
  } else if ($result == ZIP_ERROR) {
    error_message("The necessary zip classes are missing.  Please upload the player manually instead using the <a href='admin.php?page=jwplayer-update'>upgrade page</a>.");
  }
}

function upload_state() { ?>
  <h2><?php echo "JW Player Install"; ?></h2>
  <p/>
  <?php if (player_upload()) { ?>
  <div id="info" class="fade updated" style="display: none;">
    <p><strong><span id="player_version"><?php echo "Successfully installed your player, JW Player "; ?></span></strong></p>
  </div>
  <div id="error" class="error fade" style="display: none;">
    <p><strong><?php echo "JW Player was not detected."; ?></strong></p>
  </div>
  <form name="<?php echo LONGTAIL_KEY . "form"; ?>" method="post" action="">
    <table class="form-table">
      <tr>
        <td colspan="2">
          <?php embed_demo_player(); ?>
        </td>
      </tr>
    </table>
  </form>
  <?php } else {
    error_message("Not able to install JWPlayer.  Please grant write access to the jw-player-plugin-for-wordpress/player directory.");
    default_state();
  }
}

function error_message($message) { ?>
  <div id="error" class="error fade">
    <p><strong><?php echo $message; ?></strong></p>
  </div> <?php
}

function embed_demo_player($download = false) {
  $atts = array(
    "file" => "http://content.longtailvideo.com/videos/bunny.flv",
    "image" => "http://content.longtailvideo.com/videos/bunny.jpg",
    "id" => "jwplayer-1"
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
          document.getElementById("error").style.display = "none";
          document.getElementById("info").style.display = "block";
        }
        document.getElementById("player_version").innerHTML = document.getElementById("player_version").innerHTML + player.getConfig().version;
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
            <h3 class="hndle"><span><?php echo "Manually Upgrade"; ?></span></h3>
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
                      <span><?php echo "Upload your own player.swf. Use this to upgrade to the licensed version or to install a specific version of the player.  To obtain a licensed player, please purchase a license from <a href=\"https://www.longtailvideo.com/order/" . JW_PLAYER_GA_VARS . "\" target=_blank>LongTail Video</a>."; ?></span>
                    </p>
                    <p>
                      <label for="file"><?php echo "Install JW Player:"; ?></label>
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
            <h3 class="hndle"><span><?php echo "Automatically Upgrade"; ?></span></h3>
            <div class="inside" style="margin: 10px;">
              <table class="form-table">
                <tr>
                  <td colspan="2">
                    <p>
                      <span><?php echo "Automatically download the latest Non-commercial version of the JW Player to your web server."; ?></span>
                    </p>
                    <p>
                      <input class="button-secondary" type="submit" name="Non_commercial" value="Install Latest JW Player" />
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
