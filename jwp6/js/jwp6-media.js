function JWP6Media() {

    var $ = jQuery;

    var t = this;

    this.SELECT2_SETTINGS = {
        'minimumResultsForSearch': 8,
        'formatResult': function (opt) {
            var thumb = $(opt.element).data('thumb');
            if (thumb) {
                return '<span class="thumbedoption"><span class="thumboption" style="background-image: url(' + thumb + ');"></span>' + opt.text + '</span>';
            }
            return opt.text;
        }
    };

    this.fieldset_toggles = {
        'video' : {
            'show' : ['video_id_group', 'image_yesno_group'],
            'hide' : ['video_url_group', 'playlist_id_group', 'image_id_group', 'image_url_group'],
            'reset': ['video_url', 'playlist_id']
        },
        'video_url': {
            'show' : ['video_url_group', 'image_yesno_group'],
            'hide' : ['video_id_group', 'playlist_id_group', 'image_id_group', 'image_url_group'],
            'reset': ['video_id', 'playlist_id']
        },
        'playlist': {
            'show' : ['playlist_id_group'],
            'hide' : ['video_id_group', 'video_url_group', 'image_yesno_group', 'image_id_group', 'image_url_group'],
            'reset': ['video_id', 'video_url']
        },
        'image': {
            'show' : ['image_id_group'],
            'hide' : ['image_url_group', 'image_yesno_group'],
            'reset': ['image_url']
        },
        'image_url': {
            'show' : ['image_url_group'],
            'hide' : ['image_id_group', 'image_yesno_group'],
            'reset': ['image_id']
        },
        'image_yesno': {
            'show' : ['image_yesno_group'],
            'hide' : ['image_id_group', 'image_url_group']
        }
    }

    this.toggle = function (elements, show_or_hide) {
        for (var i = 0; i < elements.length; i++) {
            if (show_or_hide && 'hide' == show_or_hide) {
                $('#' + elements[i]).addClass('hidden');
            } else {
                $('#' + elements[i]).removeClass('hidden');
            }
        };
        return false;
    }

    this.fieldset_toggle = function (e) {
        e.stopPropagation();
        var parts = e.target.href.split('#');
        if (parts.length <= 1) {
            return false;
        }
        var hash = parts[1];
        if (t.fieldset_toggles[hash]) {
            if (t.fieldset_toggles[hash]['show']) t.toggle(t.fieldset_toggles[hash]['show']);
            if (t.fieldset_toggles[hash]['hide']) t.toggle(t.fieldset_toggles[hash]['hide'], 'hide');
            if (t.fieldset_toggles[hash]['reset']) {
                for (var i = 0; i < t.fieldset_toggles[hash]['reset'].length; i++) {
                    $('#' + t.fieldset_toggles[hash]['reset'][i]).val('');
                    $('select#' + t.fieldset_toggles[hash]['reset'][i]).select2('val', '');
                }
            }
        }
        //t.preview_player();
        return false;
    }

    this.select2_change = function(e) {
        console.log('Value changed to: ' + e.val);
    };

    this.preview_player = function () {
        var
            data          = {},
            player_name   = $('#player_name').select2("val"),
            video_id      = $('#video_id').select2("val"),
            video_url     = $('#video_url').val(),
            playlist_id   = $('#playlist_id').select2("val"),
            image_id      = $('#image_id').select2("val"),
            image_url     = $('#image_url').val()
        ;
        if ( video_id || video_url || playlist_id ) {
            data['player_name'] = player_name;
            if (playlist_id) {
                data['playlist_id'] = playlist_id;
            }
            else if (video_url) {
                data['video_url'] = video_url;
            }
            else {
                data['video_id'] = video_id;
            }
            if (image_url) {
                data['image_url'] = image_url;
            }
            else if (image_id) {
                data['image_id'] = image_id;
            }
            $.post(
                JWP6_AJAX_URL + '?call=embedcode',
                data,
                function (data) {
                    $('#player-preview').html(data);
                }
            );
        } else {
            $('#player-preview').html('<p class="info">The preview of the player will show after you select a player and a video/video url/playlist.</p>');
        }
    };

}

var jwp6media = new JWP6Media();


(function($) {
    
    $(function() {
        $('#tab-jwp6_media').addClass('active');
        $('a.fieldset_toggle').bind('click.fieldset_toggle', jwp6media.fieldset_toggle);
        $('#image_url, #video_url').bind('change', jwp6media.preview_player);
        //jwp6media.preview_player();
    });

})(jQuery);