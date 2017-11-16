<?php
if (!defined('BASE_PATH')) exit('No direct script access allowed');
$app = \Liten\Liten::getInstance();
$app->view->extend('_layouts/admin');
$app->view->block('admin');
define('SCREEN_PARENT', $posttype);
define('SCREEN', $posttype);

?>

<script src="//cdn.tinymce.com/4/tinymce.min.js"></script>
<script type="text/javascript">
    tinymce.init({
        selector: "textarea",
        theme: "modern",
        relative_urls: false,
        remove_script_host: false,
        height: 300,
        media_live_embeds: true,
        plugins: [
            "advlist autolink lists link image charmap print preview anchor",
            "searchreplace visualblocks code codesample",
            "insertdatetime media table contextmenu paste <?php app()->hook->{'do_action'}('ttcms_wysiwyg_editor_plugin'); ?>"
        ],
        link_list: [
            <?php foreach(tinymce_link_list() as $link) : {echo "{title: '" . _escape($link['post_title']) . "', value: '" . get_base_url() . _escape($link['post_relative_url']) . "'}," . "\n"; } endforeach; ?>
        ],
        toolbar: "undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media | codesample | preview <?php app()->hook->{'do_action'}('ttcms_wysiwyg_editor_toolbar'); ?>",
        autosave_ask_before_unload: false,
        content_css: [
            "//fonts.googleapis.com/css?family=Lato:300,300i,400,400i",
            "//tritan-cms.s3.amazonaws.com/static/assets/css/tinymce.css"
        ],
        file_picker_callback: elFinderBrowser
    });
    function elFinderBrowser(callback, value, meta) {
        tinymce.activeEditor.windowManager.open({
            file: "<?= get_base_url(); ?>admin/elfinder/",
            title: "elFinder 2.1",
            width: 900,
            height: 600,
            resizable: "yes"
        }, {
            oninsert: function (file) {
                // Provide file and text for the link dialog
                if (meta.filetype == "file") {
                    //callback("mypage.html", {text: "My text"});
                    callback(file.url);
                }

                // Provide image and alt text for the image dialog
                if (meta.filetype == "image") {
                    //callback("myimage.jpg", {alt: "My alt text"});
                    callback(file.url);
                }

                // Provide alternative source and posted for the media dialog
                if (meta.filetype == "media") {
                    //callback("movie.mp4", {source2: "alt.ogg", poster: "image.jpg"});
                    callback(file.url);
                }
            }
        });
        return false;
    };
</script>
<?= ttcms_set_featured_image(); ?>

<script src="static/assets/js/url_slug.js" type="text/javascript"></script>
<script>
    $(function () {
        $('#post_title').keyup(function () {
            $('#post_slug').val(url_slug($(this).val()));
        });
    });
</script>

<!-- form start -->
<form name="form" method="post" data-toggle="validator" action="<?= get_base_url() ?>admin/<?=$posttype;?>/<?= (int) _escape($post['post_id']); ?>/" id="form" autocomplete="off">
    <!-- Content Wrapper. Contains post content -->
    <div class="content-wrapper">
        <!-- Content Header (Post header) -->
        <div class="box box-solid">
            <div class="box-header with-border">
                <i class="fa fa-text-width"></i>
                <h3 class="box-title"><?= _t('Update', 'tritan-cms'); ?> <?= $posttype_title; ?></h3>

                <div class="pull-right">
                    <button type="button"<?=ae('create_posts');?> class="btn btn-warning" onclick="window.location = '<?= get_base_url(); ?>admin/<?=$posttype;?>/create/'"><i class="fa fa-plus"></i> <?= _t('New', 'tritan-cms'); ?> <?=$posttype;?></button>
                    <button type="submit"<?=ae('update_posts');?> class="btn btn-success"><i class="fa fa-pencil"></i> <?= _t('Update', 'tritan-cms'); ?></button>
                    <button type="button"<?=ae('delete_posts');?> class="btn btn-danger" data-toggle="modal" data-target="#delete-<?= (int) _escape($post['post_id']); ?>"><i class="fa fa-trash"></i> <?= _t('Delete', 'tritan-cms'); ?></button>
                    <button type="button" class="btn btn-primary" onclick="window.location = '<?= get_base_url(); ?>admin/<?= $posttype; ?>/'"><i class="fa fa-ban"></i> <?= _t('Cancel', 'tritan-cms'); ?></button>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <section class="content">
            <?= _ttcms_flash()->showMessage(); ?>
            <div class="row">
                <!-- left column -->
                <div class="col-md-9">
                    <!-- general form elements -->
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?= _t('Update', 'tritan-cms'); ?> <?= $posttype_title; ?></h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                            <div class="form-group">
                                <label><strong><font color="red">*</font> <?= _t('Title', 'tritan-cms'); ?></strong></label>
                                <input type="text" class="form-control input-lg" name="post_title" id="post_title" value="<?= _escape($post['post_title']); ?>" required/>
                            </div>
                            <div class="form-group">
                                <label><strong><?= _t('Slug', 'tritan-cms'); ?></strong> <a href="#slug" data-toggle="modal"><span class="badge"><i class="fa fa-question"></i></span></a></label>
                                <input type="text" class="form-control" name="post_slug" id="post_slug" value="<?= _escape($post['post_slug']); ?>" />
                            </div>
                            <?php $app->hook->{'do_action'}('update_post_content_field', $posttype, $post) ;?>
                            <div class="form-group">
                                <label><strong><?= _t('Content', 'tritan-cms'); ?></strong></label>
                                <textarea class="form-control" name="post_content"><?= _escape($post['post_content']); ?></textarea>
                            </div>
                        </div>
                        <!-- /.box-body -->
                    </div>
                    <!-- /.box-body -->
                </div>
                <!-- /.left column -->
                
                <?php $app->hook->{'do_action'}('update_post_metabox', $posttype, $post, 'normal', 'middle'); ?>

                <div class="col-md-3">
                    <?php $app->hook->{'do_action'}('update_post_metabox', $posttype, $post, 'side', 'top'); ?>
                    <!-- general form elements -->
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><font color="red">*</font> <?= _t('Post Type', 'tritan-cms'); ?></h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                            <div class="form-group">
                                <select class="form-control select2" name="post_posttype" style="width: 100%;" required>
                                    <option>&nbsp;</option>
                                        <?php foreach (get_all_post_types() as $post_type) : ?>
                                    <option value="<?= _escape($post_type['posttype_slug']); ?>"<?= selected(_escape($post_type['posttype_slug']), _escape($post['post_type']['post_posttype']), false); ?>><?= _escape($post_type['posttype_title']); ?></option>
                                        <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <!-- /.box-body -->
                        <?php $app->hook->{'do_action'}('update_post_metabox_posttype', $posttype, $post) ;?>
                    </div>
                    <!-- /.box-primary -->

                    <!-- general form elements -->
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?= _t('Publish', 'tritan-cms'); ?></h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                            <div class="form-group">
                                <label><strong><font color="red">*</font> <?= _t('Publication Date', 'tritan-cms'); ?></strong></label>
                                <div class='input-group date' id='datetimepicker1'>
                                    <input type="text" class="form-control" name="post_created" value="<?= _escape($post['post_created']); ?>" required/>
                                    <span class="input-group-addon">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                    </span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label><strong><font color="red">*</font> <?= _t('Status', 'tritan-cms'); ?></strong></label>
                                <select class="form-control select2" name="post_status" style="width: 100%;" required>
                                    <option>&nbsp;</option>
                                    <?php if(hasPermission('publish_posts')) : ?>
                                    <option value="published"<?= selected('published', _escape($post['post_status']), false); ?>><?= _t('Publish', 'tritan-cms'); ?></option>
                                    <?php endif; ?>
                                    <option value="draft"<?= selected('draft', _escape($post['post_status']), false); ?>><?= _t('Draft', 'tritan-cms'); ?></option>
                                    <option value="archived"<?= selected('archived', _escape($post['post_status']), false); ?>><?= _t('Archive', 'tritan-cms'); ?></option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label><strong><font color="red">*</font> <?= _t('Author', 'tritan-cms'); ?></strong></label>
                                <select class="form-control select2" name="post_author" style="width: 100%;" required>
                                    <option>&nbsp;</option>
                                    <?php get_users_list((int) _escape($post['post_author'])); ?>
                                </select>
                            </div>
                            <?php $app->hook->{'do_action'}('update_post_metabox_publish', $posttype, $post) ;?>
                        </div>
                        <!-- /.box-body -->
                    </div>
                    <!-- /.box-primary -->

                    <!-- general form elements -->
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?= _t('Post Attributes', 'tritan-cms'); ?></h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                            <div class="form-group">
                                <label><strong><?= _t('Parent', 'tritan-cms'); ?></strong></label>
                                <select class="form-control select2" name="post_parent" style="width: 100%;">
                                    <option value="">&nbsp;</option>
                                    <?php get_post_dropdown_list(_escape($post['post_attributes']['parent']['post_parent']), (int) _escape($post['post_id'])); ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label><strong><?= _t('Sidebar', 'tritan-cms'); ?></strong></label>
                                <div class="ios-switch switch-md pull-right">
                                    <input type="hidden" class="js-switch" name="post_sidebar" value="0" />
                                    <input type="checkbox" class="js-switch" name="post_sidebar"<?= checked(1, (int) _escape($post['post_attributes']['post_sidebar']), false); ?> value="1" />
                                </div>
                            </div>
                            <div class="form-group">
                                <label><strong><?= _t('Show in Menu', 'tritan-cms'); ?></strong></label>
                                <div class="ios-switch switch-md pull-right">
                                    <input type="hidden" class="js-switch" name="post_show_in_menu" value="0" />
                                    <input type="checkbox" class="js-switch" name="post_show_in_menu"<?= checked(1, (int) _escape($post['post_attributes']['post_show_in_menu']), false); ?> value="1" />
                                </div>
                            </div>
                            <div class="form-group">
                                <label><strong><?= _t('Show in Search', 'tritan-cms'); ?></strong></label>
                                <div class="ios-switch switch-md pull-right">
                                    <input type="hidden" class="js-switch" name="post_show_in_search" value="0" />
                                    <input type="checkbox" class="js-switch" name="post_show_in_search"<?= checked(1, (int) _escape($post['post_attributes']['post_show_in_search']), false); ?> value="1" />
                                </div>
                            </div>
                            <?php $app->hook->{'do_action'}('update_post_metabox_attributes', $posttype, $post) ;?>
                        </div>
                        <!-- /.box-body -->
                    </div>
                    <!-- /.box-primary -->

                    <!-- general form elements -->
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?= _t('Featured Image', 'tritan-cms'); ?></h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                            <div id="elfinder"></div>
                            <div id="elfinder_image"><img src="<?= _escape($post['post_featured_image']); ?>" style="width:280px;height:auto;background-size:contain;margin-bottom:.9em;background-repeat:no-repeat" /></div>
                            <?php if(_escape($post['post_featured_image']) != '') : ?>
                            <button type="button" class="btn btn-primary" onclick="window.location = '<?= get_base_url(); ?>admin/<?= $posttype; ?>/<?= (int) _escape($post['post_id']); ?>/remove-featured-image/'"><?= _t('Remove featured image', 'tritan-cms'); ?></button>
                            <?php else : ?>
                            <button type="button" id="set_image" class="btn btn-primary" style="display:none;"><?= _t('Set featured image', 'tritan-cms'); ?></button>
                            <button type="button" id="remove_image" class="btn btn-primary" style="display:none;"><?= _t('Remove featured image', 'tritan-cms'); ?></button>
                            <?php endif; ?>
                            <input type="hidden" class="form-control" name="post_featured_image" id="post_featured_image" value="<?= _escape($post['post_featured_image']); ?>" />
                            <?php $app->hook->{'do_action'}('update_post_metabox_featured_image', $posttype, $post) ;?>
                        </div>
                        <!-- /.box-body -->
                    </div>
                    <!-- /.box-primary -->
                    <?php $app->hook->{'do_action'}('update_post_metabox', $posttype, $post, 'side', 'bottom'); ?>
                </div>

            </div>
            <!--/.row -->
        </section>
        <!-- /.Main content -->

    </div>
    <!-- /.Content Wrapper. Contains post content -->
</form>
<!-- modal -->
<div class="modal" id="slug">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?= $posttype_title; ?> <?= _t('Slug', 'tritan-cms'); ?></h4>
            </div>
            <div class="modal-body">
                <p><?= _t(sprintf("If left blank, the system will auto generate the %s slug.", $posttype_title), 'tritan-cms'); ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><?= _t('Close', 'tritan-cms'); ?></button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->

<!-- modal -->
<div class="modal" id="redirect">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?= $posttype_title; ?> <?= _t('Redirect', 'tritan-cms'); ?></h4>
            </div>
            <div class="modal-body">
                <p><?= _t(sprintf("If this %s should be redirected to an external url, enter it here.", $posttype_title), 'tritan-cms'); ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><?= _t('Close', 'tritan-cms'); ?></button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->

<!-- modal -->
<div class="modal" id="delete-<?= _escape($post['post_id']); ?>">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?= _escape($post['post_title']); ?></h4>
            </div>
            <div class="modal-body">
                <p><?=_t('Are you sure you want to delete this post?');?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><?= _t('Close'); ?></button>
                <button type="button" class="btn btn-primary" onclick="window.location='<?=get_base_url();?>admin/<?=$posttype;?>/<?= _escape($post['post_id']); ?>/d/'"><?= _t('Confirm'); ?></button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->

<?php $app->view->stop(); ?>