<?php
/*
  Plugin Name: Framed Slideshow Gallery
  Plugin URI: http://wordpress.org/
  Description: Wordpress slideshow image gallery with fully controlling in back-end.
  Author: Tauhidul Alam
  Version: 0.1
  Author URI: http://wordpress.org/
  License: GPLv2 or later
 */

/*  © Copyright 2014 

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

define("SIG_BASE_DIR", dirname(__FILE__) . '/');
define("SIG_BASE_URL", plugins_url("/mlmalstic-slideshow-gallery/"));

class slideshow_iamge_gallery {

    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'mlmlstic_slideshow_enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'admin_mlmlstic_slideshow_enqueue_scripts'));
        add_shortcode('slideshow-image-gallery', array($this, 'mlmlstic_slideshow_gallery'));
        add_action('wp_footer', array($this, 'mlmlstic_footer_scripts'));
        add_action('wp_head', array($this, 'mlmlstic_header_style'));
        add_action('admin_footer', array($this, 'mlmlstic_admin_footer_scripts'));
        add_action('init', array($this, 'slideshow_gallery_admin_section'));
        add_action('add_meta_boxes', array($this, 'gallery_add_meta_box'));
        add_action('save_post', array($this, 'gallery_save_meta_box_data'));
        add_action('admin_menu', array($this, 'gallery_admin_page'));
        add_action("wp_ajax_save_slideshow_settings", array($this, "save_gallery_settins"));
    }

    function mlmlstic_slideshow_enqueue_scripts() {
        wp_enqueue_script('jquery');
        wp_enqueue_script('milmalstic-bootstrap', SIG_BASE_URL . 'bootstrap/js/bootstrap.min.js', array('jquery'));
//        wp_enqueue_style('milmalstic-bootstrap', SIG_BASE_URL . 'bootstrap/css/bootstrap.css');
        wp_enqueue_style('milmalstic-style', SIG_BASE_URL . 'css/style.css');
    }

    function admin_mlmlstic_slideshow_enqueue_scripts() {
        wp_enqueue_script('jquery');
        wp_enqueue_script("jquery-form");
        wp_enqueue_script('admin-milmalstic-bootstrap', SIG_BASE_URL . 'bootstrap/js/bootstrap.min.js', array('jquery'));
        wp_enqueue_style('admin-milmalstic-bootstrap', SIG_BASE_URL . 'bootstrap/css/bootstrap.css');
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('color-picker-custom-script-handle', plugins_url('custom-script.js', __FILE__), array('wp-color-picker'), false, true);
    }

    function save_gallery_settins() {
        update_option("gallery_settings", $_POST['slideshow']);
        die("Saved");
    }

    function gallery_admin_page() {
        $xyz = add_submenu_page('edit.php?post_type=mlmalstic-slideshow', 'Gallery->Settings', 'Settings', 'manage_options', 'gallery-settings', array($this, 'gallery_settings_page'));
    }

    function mlmlstic_header_style() {
        $data = get_option("gallery_settings", array());
        ?>
        <style>
            .mlmalstic-slideshow-gallery .msg_slideshow {
                width: 400px;
                height: 400px;
                padding: 10px;
                position: relative;
                overflow: hidden;
                background: <?php echo isset($data['frame']) ? $data['frame'] : '#101010'; ?> url("<?php echo SIG_BASE_URL; ?>icons/loading.gif") no-repeat center center;
                -moz-border-radius: 10px;
                -webkit-border-radius: 10px;
                border-radius: 10px;
            }
            .mlmalstic-slideshow-gallery .msg_wrapper img {
                display: inline-block !important;
                vertical-align: middle;
                -moz-box-shadow: 0px 0px 10px <?php echo isset($data['shadow']) ? $data['shadow'] : '#000000'; ?>;
                -webkit-box-shadow: 0px 0px 10px <?php echo isset($data['shadow']) ? $data['shadow'] : '#000000'; ?>;
                box-shadow: 0px 0px 10px <?php echo isset($data['shadow']) ? $data['shadow'] : '#000000'; ?>;
            }
            .mlmalstic-slideshow-gallery .msg_thumbs {
                background: <?php echo isset($data['popup']) ? $data['popup'] : '#000000'; ?>;
                position: absolute;
                width: 250px;
                height: 166px;
                top: -230px;
                left: 50%;
                padding: 30px;
                margin: 0 0 0 -155px;
                -moz-border-radius: 0px 0px 10px 10px;
                -webkit-border-bottom-left-radius: 10px;
                -webkit-border-bottom-right-radius: 10px;
                border-bottom-left-radius: 10px;
                border-bottom-right-radius: 10px;
                -moz-box-shadow: 1px 1px 5px <?php echo isset($data['popup']) ? $data['popup'] : '#000000'; ?>;
                -webkit-box-shadow: 1px 1px 5px <?php echo isset($data['popup']) ? $data['popup'] : '#000000'; ?>;
                box-shadow: 1px 1px 5px <?php echo isset($data['popup']) ? $data['popup'] : '#000000'; ?>;
                opacity: 0.9;
                filter: progid:DXImageTransform.Microsoft.Alpha(opacity=90);
                overflow: hidden;
            }
            .mlmalstic-slideshow-gallery .msg_controls {
                position: absolute;
                bottom: 15px;
                right: -110px;
                width: 104px;
                height: 26px;
                z-index: 20;
                -moz-border-radius: 5px;
                -webkit-border-radius: 5px;
                border-radius: 5px;
                background-color: <?php echo isset($data['controller']) ? $data['controller'] : '#000000'; ?>;
                opacity: 0.8;
                filter: progid:DXImageTransform.Microsoft.Alpha(opacity=80);
            }
        </style>
        <?php
    }

    function gallery_settings_page() {
        $data = get_option("gallery_settings", array());
        ?>
        <div class="mlmalstic-slideshow-gallery">
            <div class="row">
                <div class="col-md-6">
                    <form class="form" method="post" role="form" id="slideshowform">
                        <input type="hidden" name="action" value="save_slideshow_settings" />
                        <table class="table">
                            <tbody>
                                <tr>
                                    <td>Frame Color</td>
                                    <td><input type="text" name="slideshow[frame]" value="<?php echo isset($data['frame']) ? $data['frame'] : '#101010' ?>" class="frame-color-field" data-default-color="#101010" /></td>
                                </tr>
                                <tr>
                                    <td>Shadow Color</td>
                                    <td><input type="text" name="slideshow[shadow]" value="<?php echo isset($data['shadow']) ? $data['shadow'] : '#000000'; ?>" class="shadow-color-field" data-default-color="#000000" /></td>
                                </tr>
                                <tr>
                                    <td>Pop-up Frame Color</td>
                                    <td><input type="text" name="slideshow[popup]" value="<?php echo isset($data['popup']) ? $data['shadow'] : '#000000'; ?>" class="pop-up-color-field" data-default-color="#000000" /></td>
                                </tr>
                                <tr>
                                    <td>Controller Frame Color</td>
                                    <td><input type="text" name="slideshow[controller]" value="<?php echo isset($data['controller']) ? $data['controller'] : '#000000'; ?>" class="controller-up-color-field" data-default-color="#000000" /></td>
                                </tr>
                            </tbody>
                        </table>
                        <div>
                            <button id="load-slide-data" data-loading-text="Saving.." type="submit" class="btn btn-primary">Save Settings</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php
    }

    function gallery_add_meta_box() {
        add_meta_box(
                'gallery_meta_id', __('Gallery Icon', 'mlmalstic-slideshow-gallery'), array($this, 'gallery_meta_box_callback'), 'mlmalstic-slideshow', 'normal', 'high');
    }

    function gallery_meta_box_callback($post) {
        wp_nonce_field('gallery_meta_box', 'gallery_meta_box_nonce');
        $value = get_post_meta($post->ID, '_gallery_meta_value_icon', true);
        echo '<label for="gallery_icon">';
        _e('Thumbnail', 'mlmalstic-slideshow-gallery');
        echo '</label> ';
        echo '<input type="button" id="gallery_icon" value="Select" /><br><br>';
        echo '<img id="gallery_icon_preview" src="' . esc_attr($value) . '" width="100px" height="100px" alt="No thumbnail yet!" />';
        echo '<input id="gallery_icon_value" type="hidden" name="gallery_thumbnail" value="' . esc_attr($value) . '" />';
    }

    function gallery_save_meta_box_data($post_id) {
        if (!isset($_POST['gallery_meta_box_nonce'])) {
            return;
        }
        if (!wp_verify_nonce($_POST['gallery_meta_box_nonce'], 'gallery_meta_box')) {
            return;
        }
        $my_data = sanitize_text_field($_POST['gallery_thumbnail']);
        update_post_meta($post_id, '_gallery_meta_value_icon', $my_data);
    }

    function slideshow_gallery_admin_section() {
        $labels = array(
            'name' => _x('Slides', 'Post Type General Name', 'mlmalstic-slideshow-gallery'),
            'singular_name' => _x('Slide', 'Post Type Singular Name', 'mlmalstic-slideshow-gallery'),
            'menu_name' => __('Slides', 'mlmalstic-slideshow-gallery'),
            'parent_item_colon' => __('Parent Slide', 'mlmalstic-slideshow-gallery'),
            'all_items' => __('All Slides', 'mlmalstic-slideshow-gallery'),
            'view_item' => __('View Slide', 'mlmalstic-slideshow-gallery'),
            'add_new_item' => __('Add New Slide', 'mlmalstic-slideshow-gallery'),
            'add_new' => __('Add New', 'mlmalstic-slideshow-gallery'),
            'edit_item' => __('Edit Slide', 'mlmalstic-slideshow-gallery'),
            'update_item' => __('Update Slide', 'mlmalstic-slideshow-gallery'),
            'search_items' => __('Search Slide', 'mlmalstic-slideshow-gallery'),
            'not_found' => __('Not Found', 'mlmalstic-slideshow-gallery'),
            'not_found_in_trash' => __('Not found in Trash', 'mlmalstic-slideshow-gallery'),
        );

        $args = array(
            'label' => __('slides', 'mlmalstic-slideshow-gallery'),
            'description' => __('Slide news and reviews', 'mlmalstic-slideshow-gallery'),
            'labels' => $labels,
            'supports' => array('title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields',),
            'taxonomies' => array('genres'),
            'hierarchical' => false,
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => true,
            'show_in_admin_bar' => true,
            'menu_position' => 5,
            'can_export' => true,
            'has_archive' => true,
            'exclude_from_search' => false,
            'publicly_queryable' => true,
            'capability_type' => 'page',
        );

        register_post_type('mlmalstic-slideshow', $args);
    }

    function mlmlstic_slideshow_gallery() {
        $args = array(
            'post_type' => 'mlmalstic-slideshow',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'ignore_sticky_posts' => 1);
        $slides = new WP_Query($args);
        $count_posts = wp_count_posts("mlmalstic-slideshow");
        $total = $count_posts->publish;
        $count = 0;
        $count_hidden = 0;
        $hidden = array();
        $shown = '';
        if ($slides->have_posts()) {
            while ($slides->have_posts()) {
                $slides->the_post();
                $image = wp_get_attachment_image_src(get_post_thumbnail_id(), 'single-post-thumbnail');
                $thumbnail = get_post_meta(get_the_ID(), '_gallery_meta_value_icon', true);
                if ($count > 5) {
                    $hidden[$count_hidden] = <<<EOD
                        <a href="#"><img src="{$thumbnail}" alt="{$image[0]}"/></a>
EOD;
                    $count_hidden++;
                } else {
                    $shown .= <<<EOD
                        <a href="#"><img src="{$thumbnail}" alt="{$image[0]}"/></a>
EOD;
                }
                $count++;
            }
        }
        ?>
        <div class="mlmalstic-slideshow-gallery">
            <div class="content">
                <div id="msg_slideshow" class="msg_slideshow">
                    <div id="msg_wrapper" class="msg_wrapper">
                    </div>
                    <div id="msg_controls" class="msg_controls">
                        <a href="#" id="msg_grid" class="msg_grid"></a>
                        <a href="#" id="msg_prev" class="msg_prev"></a>
                        <a href="#" id="msg_pause_play" class="msg_pause"></a>
                        <a href="#" id="msg_next" class="msg_next"></a>
                    </div>
                    <div id="msg_thumbs" class="msg_thumbs"><!-- top has to animate to 0px, default -230px -->

                        <div class="msg_thumb_wrapper">
                            <?php echo $shown; ?>
                        </div>
                        <?php
                        $total_div = round($count_hidden / 6);
                        $re_add = $count_hidden % 6;
                        if ($re_add > 0) {
                            $total_div = $total_div + 1;
                        }
                        $new_count = 0;
                        for ($i = 0; $i < $total_div; $i++) {
                            ?>
                            <div class="msg_thumb_wrapper" style="display:none;">
                                <?php
                                for ($j = 0; $j < 6; $j++) {
                                    if ($new_count == $count_hidden)
                                        break;
                                    echo $hidden[$new_count];
                                    $new_count++;
                                }
                                ?>
                            </div>
                        <?php } ?>
                        <a href="#" id="msg_thumb_next" class="msg_thumb_next"></a>
                        <a href="#" id="msg_thumb_prev" class="msg_thumb_prev"></a>
                        <a href="#" id="msg_thumb_close" class="msg_thumb_close"></a>
                        <span class="msg_loading"></span>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    function mlmlstic_admin_footer_scripts() {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                $('#slideshowform').submit(function() {
                    $('#load-slide-data').button('loading');
                    $(this).ajaxSubmit({
                        url: ajaxurl,
                        success: function(res) {
                            $('#load-slide-data').button('reset');
                        }
                    });


                    return false;

                });
                var custom_file_frame;
                $('.frame-color-field').wpColorPicker();
                $('.shadow-color-field').wpColorPicker();
                $('.pop-up-color-field').wpColorPicker();
                $('.controller-up-color-field').wpColorPicker();
                $(document).on('click', '#gallery_icon', function(event) {
                    event.preventDefault();
                    custom_file_frame = media_initialize(custom_file_frame);

                    custom_file_frame.on('select', function() {
                        var selection = custom_file_frame.state().get('selection');
                        selection.map(function(attachment) {
                            attachment = attachment.toJSON();
                            $('#gallery_icon_value').val(attachment.url);
                            $('#gallery_icon_preview').attr("src", $('#gallery_icon_value').val());
                        });
                    });

                    custom_file_frame.open();
                });
            });
            function media_initialize(custom_file_frame) {
                if (typeof (custom_file_frame) !== "undefined") {
                    custom_file_frame.close();
                }

                custom_file_frame = wp.media.frames.customHeader = wp.media({
                    title: "Sample title of WP Media Uploader Frame",
                    library: {
                        type: 'image'
                    },
                    button: {
                        text: "insert text"
                    },
                    multiple: false
                });
                return custom_file_frame;
            }
        </script>
        <?php
    }

    function mlmlstic_footer_scripts() {
        ?>
        <script type = "text/javascript" >
            jQuery(document).ready(function($) {
                var interval = 4000;
                var playtime;
                var current = 0;
                var current_thumb = 0;
                var nmb_thumb_wrappers = $('#msg_thumbs .msg_thumb_wrapper').length;
                var nmb_images_wrapper = 6;
                play();

                slideshowMouseEvent();
                function slideshowMouseEvent() {
                    $('#msg_slideshow').unbind('mouseenter')
                            .bind('mouseenter', showControls)
                            .andSelf()
                            .unbind('mouseleave')
                            .bind('mouseleave', hideControls);
                }

                $('#msg_grid').bind('click', function(e) {
                    hideControls();
                    $('#msg_slideshow').unbind('mouseenter').unbind('mouseleave');
                    pause();
                    $('#msg_thumbs').stop().animate({'top': '0px'}, 500);
                    e.preventDefault();
                });

                $('#msg_thumb_close').bind('click', function(e) {
                    showControls();
                    slideshowMouseEvent();
                    $('#msg_thumbs').stop().animate({'top': '-230px'}, 500);
                    e.preventDefault();
                });

                $('#msg_pause_play').bind('click', function(e) {
                    var $this = $(this);
                    if ($this.hasClass('msg_play'))
                        play();
                    else
                        pause();
                    e.preventDefault();
                });

                $('#msg_next').bind('click', function(e) {
                    pause();
                    next();
                    e.preventDefault();
                });
                $('#msg_prev').bind('click', function(e) {
                    pause();
                    prev();
                    e.preventDefault();
                });

                function showControls() {
                    $('#msg_controls').stop().animate({'right': '15px'}, 500);
                }
                function hideControls() {
                    $('#msg_controls').stop().animate({'right': '-110px'}, 500);
                }

                function play() {
                    next();
                    $('#msg_pause_play').addClass('msg_pause').removeClass('msg_play');
                    playtime = setInterval(next, interval)
                }

                function pause() {
                    $('#msg_pause_play').addClass('msg_play').removeClass('msg_pause');
                    clearTimeout(playtime);
                }

                function next() {
                    ++current;
                    showImage('r');
                }

                function prev() {
                    --current;
                    showImage('l');
                }

                function showImage(dir) {
                    alternateThumbs();

                    var $thumb = $('#msg_thumbs .msg_thumb_wrapper:nth-child(' + current_thumb + ')')
                            .find('a:nth-child(' + parseInt(current - nmb_images_wrapper * (current_thumb - 1)) + ')')
                            .find('img');
                    if ($thumb.length) {
                        var source = $thumb.attr('alt');
                        var $currentImage = $('#msg_wrapper').find('img');
                        if ($currentImage.length) {
                            $currentImage.fadeOut(function() {
                                $(this).remove();
                                $('<img />').load(function() {
                                    var $image = $(this);
                                    resize($image);
                                    $image.hide();
                                    $('#msg_wrapper').empty().append($image.fadeIn());
                                }).attr('src', source);
                            });
                        }
                        else {
                            $('<img />').load(function() {
                                var $image = $(this);
                                resize($image);
                                $image.hide();
                                $('#msg_wrapper').empty().append($image.fadeIn());
                            }).attr('src', source);
                        }

                    }
                    else { 
                        if (dir == 'r')
                            --current;
                        else if (dir == 'l')
                            ++current;
                        alternateThumbs();
                        return;
                    }
                }

                function alternateThumbs() {
                    $('#msg_thumbs').find('.msg_thumb_wrapper:nth-child(' + current_thumb + ')')
                            .hide();
                    current_thumb = Math.ceil(current / nmb_images_wrapper);
                    if (current_thumb > nmb_thumb_wrappers) {
                        current_thumb = 1;
                        current = 1;
                    }
                    else if (current_thumb == 0) {
                        current_thumb = nmb_thumb_wrappers;
                        current = current_thumb * nmb_images_wrapper;
                    }

                    $('#msg_thumbs').find('.msg_thumb_wrapper:nth-child(' + current_thumb + ')')
                            .show();
                }

                $('#msg_thumb_next').bind('click', function(e) {
                    next_thumb();
                    e.preventDefault();
                });
                $('#msg_thumb_prev').bind('click', function(e) {
                    prev_thumb();
                    e.preventDefault();
                });
                function next_thumb() {
                    var $next_wrapper = $('#msg_thumbs').find('.msg_thumb_wrapper:nth-child(' + parseInt(current_thumb + 1) + ')');
                    if ($next_wrapper.length) {
                        $('#msg_thumbs').find('.msg_thumb_wrapper:nth-child(' + current_thumb + ')')
                                .fadeOut(function() {
                                    ++current_thumb;
                                    $next_wrapper.fadeIn();
                                });
                    }
                }
                function prev_thumb() {
                    var $prev_wrapper = $('#msg_thumbs').find('.msg_thumb_wrapper:nth-child(' + parseInt(current_thumb - 1) + ')');
                    if ($prev_wrapper.length) {
                        $('#msg_thumbs').find('.msg_thumb_wrapper:nth-child(' + current_thumb + ')')
                                .fadeOut(function() {
                                    --current_thumb;
                                    $prev_wrapper.fadeIn();
                                });
                    }
                }

                $('#msg_thumbs .msg_thumb_wrapper > a').bind('click', function(e) {
                    var $this = $(this);
                    $('#msg_thumb_close').trigger('click');
                    var idx = $this.index();
                    var p_idx = $this.parent().index();
                    current = parseInt(p_idx * nmb_images_wrapper + idx + 1);
                    showImage();
                    e.preventDefault();
                }).bind('mouseenter', function() {
                    var $this = $(this);
                    $this.stop().animate({'opacity': 1});
                }).bind('mouseleave', function() {
                    var $this = $(this);
                    $this.stop().animate({'opacity': 0.5});
                });

                function resize($image) {
                    var theImage = new Image();
                    theImage.src = $image.attr("src");
                    var imgwidth = theImage.width;
                    var imgheight = theImage.height;

                    var containerwidth = 400;
                    var containerheight = 400;

                    if (imgwidth > containerwidth) {
                        var newwidth = containerwidth;
                        var ratio = imgwidth / containerwidth;
                        var newheight = imgheight / ratio;
                        if (newheight > containerheight) {
                            var newnewheight = containerheight;
                            var newratio = newheight / containerheight;
                            var newnewwidth = newwidth / newratio;
                            theImage.width = newnewwidth;
                            theImage.height = newnewheight;
                        }
                        else {
                            theImage.width = newwidth;
                            theImage.height = newheight;
                        }
                    }
                    else if (imgheight > containerheight) {
                        var newheight = containerheight;
                        var ratio = imgheight / containerheight;
                        var newwidth = imgwidth / ratio;
                        if (newwidth > containerwidth) {
                            var newnewwidth = containerwidth;
                            var newratio = newwidth / containerwidth;
                            var newnewheight = newheight / newratio;
                            theImage.height = newnewheight;
                            theImage.width = newnewwidth;
                        }
                        else {
                            theImage.width = newwidth;
                            theImage.height = newheight;
                        }
                    }
                    $image.css({
                        'width': theImage.width,
                        'height': theImage.height
                    });
                }
            });
        </script>
        <?php
    }

}

new slideshow_iamge_gallery();
