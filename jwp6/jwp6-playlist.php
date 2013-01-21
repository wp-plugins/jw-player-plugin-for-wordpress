<?php
/**
 * @file The script to dynamically generate the playlist XML given a playlist
 * id.
 */

$playlist_id = $_GET['id'];

$title = 'JW Player 6 Playlist';

$themediafiles = array();
$limit = '';

$playlist_id = intval($_GET['id']);
$playlist = get_post($playlist_id);
if ($playlist) {
  $playlist_items = explode(",", get_post_meta($playlist_id, JWP6. "playlist_items", true));
}

header("content-type:text/xml;charset=utf-8");

// <rss version="2.0" xmlns:media="http://search.yahoo.com/mrss/" > 
//   <channel> 
//     <title>Example RSS Playlist</title> 
//     <item> 
//       <title>Sintel</title>
//       <description>Sintel is a fantasy computer generated short movie. It's the third 
//          release from the Blender Open Movie Project.</description>
//       <media:thumbnail url="http://example.com/thumbs/sintel.jpg" />
//       <media:content url="http://example.com/videos/sintel.mp4" /> 
//     </item>
//     etc...
//   </channel> 
// </rss>

echo "\n"."<rss version='2.0' xmlns:media='http://search.yahoo.com/mrss/' >";
echo "\n\t".'<channel>';
echo "\n\t\t".'<title>' . esc_attr($title) . '</title>';
	
if (is_array ($playlist_items)) {
	foreach ($playlist_items as $playlist_item_id) {
        $playlist_item = get_post($playlist_item_id);

    	echo "\n\t\t".'<item>';
    	echo "\n\t\t\t".'<title>' . esc_attr(stripslashes($playlist_item->post_title)) . '</title>';
        echo "\n\t\t\t"."<media:content url='" . esc_attr($playlist_item->guid) . "' />";

        $thumbnail = get_post_meta($playlist_item_id, JWP6 . "thumbnail", true);
        if ( $thumbnail ) {
            $thumbnail = get_post($thumbnail);
            if ( $thumbnail ) {
                echo "\n\t\t\t"."<media:thumbnail url='" . esc_attr($thumbnail->guid) . "' />";
            }
        }
		echo "\n\t\t".'</item>';
	}
}
	 
echo "\n\t".'</channel>';
echo "\n"."</rss>\n";	

?>