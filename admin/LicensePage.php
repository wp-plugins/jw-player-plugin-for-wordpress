<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

?>

<div class="wrap">
  <h2><?php echo "JW Player Licensing"; ?></h2>
  <form name="<?php echo LONGTAIL_KEY . "form"; ?>" method="post" action="admin.php?page=jwplayer-update">
    <div>
      <p><span><?php _e("By default, this plugin uses the latest non-commercial version of the JW Player.  Use of the player, skins and plugins is free for non-commercial use.  If you operate a commercial site (i.e., sells products, runs ads, or is owned by a company), you are required to purchase a license for the products you use.", 'jw-player-plugin-for-wordpress'); ?></span></p>
      <p><span><?php _e("Purchasing a license will remove the JW Player watermark and allow you to set your own watermark if desired.  In addition, you will be able to use commercial-only plugins, such as advertising plugins.", 'jw-player-plugin-for-wordpress'); ?></span></p>
      <a href="<?php echo "http://www.longtailvideo.com/order/" . JW_PLAYER_GA_VARS; ?>" class="button-primary" target="_blank"><?php _e("Purchase a License", 'jw-player-plugin-for-wordpress'); ?></a>
      <br/>
      <br/>
      <p><span><?php _e("Once you have purchased a license for the commercial player, you can upload it here.", 'jw-player-plugin-for-wordpress'); ?></span></p>
      <input type="submit" class="button-secondary action" name="Update" value="<?php _e("Upload Commercial Player", 'jw-player-plugin-for-wordpress'); ?>" />
    </div>
  </form>
</div>
