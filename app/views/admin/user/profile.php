<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
/**
 * Update Profile View
 *  
 * @license GPLv3
 * 
 * @since       1.0.0
 * @package     TriTan CMS
 * @author      Joshua Parker <joshmac3@icloud.com>
 */
$app = \Liten\Liten::getInstance();
$app->view->extend('_layouts/admin');
$app->view->block('admin');
define('SCREEN_PARENT', 'users');
define('SCREEN', 'profile');
$user = get_userdata(get_current_user_id());
?>

<!-- form start -->
<form method="post" action="<?= get_base_url(); ?>admin/user/profile/" data-toggle="validator" autocomplete="off">
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="box box-solid">
            <div class="box-header with-border">
                <i class="fa fa-user"></i>
                <h3 class="box-title"><?= _t('Profile', 'tritan-cms'); ?></h3>

                <div class="pull-right">
                    <input type="hidden" class="js-switch" name="user_id" value="<?=get_current_user_id();?>" />
                    <button type="submit" class="btn btn-success"><i class="fa fa-pencil"></i> <?= _t('Update', 'tritan-cms'); ?></button>
                    <button type="button"<?=ae('manage_users');?> class="btn btn-primary" onclick="window.location = '<?= get_base_url(); ?>admin/user/'"><i class="fa fa-ban"></i> <?= _t('Cancel'); ?></button>
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
                            <h3 class="box-title"><?= _t('Layout Options', 'tritan-cms'); ?></h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                            <div class="form-group">
                                <label><strong><?= _t('Fixed Layout', 'tritan-cms'); ?></strong></label>
                                <div class="ios-switch switch-md pull-right">
                                    <input type="hidden" class="js-switch" name="user_admin_layout" value="0" />
                                    <input type="checkbox" class="js-switch" name="user_admin_layout"<?= checked(1, get_user_option('user_admin_layout', (int) _escape($user['user_id'])), false); ?> value="1" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label><strong><?= _t('Toggle Sidebar', 'tritan-cms'); ?></strong></label>
                                <div class="ios-switch switch-md pull-right">
                                    <input type="hidden" class="js-switch" name="user_admin_sidebar" value="0" />
                                    <input type="checkbox" class="js-switch" name="user_admin_sidebar"<?= checked(1, get_user_option('user_admin_sidebar', (int) _escape($user['user_id'])), false); ?> value="1" />
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label><strong><?= _t('Skin', 'tritan-cms'); ?></strong></label>
                                <ul style="list-style: none;margin:0px 0px 0px -30px;">
                                    <li style="float:left; width: 25%; padding: 5px;">
                                        <input type="radio" class="flat-red" name="user_admin_skin"<?= checked('skin-blue', get_user_option('user_admin_skin', (int) _escape($user['user_id'])), false); ?> value="skin-blue" />
                                        <a href="javascript:void(0)" data-skin="skin-blue" style="display: block; box-shadow: 0 0 3px rgba(0,0,0,0.4)" class="clearfix full-opacity-hover">
                                        <div><span style="display:block; width: 20%; float: left; height: 7px; background: #367fa9"></span><span class="bg-light-blue" style="display:block; width: 80%; float: left; height: 7px;"></span></div>
                                        <div><span style="display:block; width: 20%; float: left; height: 20px; background: #222d32"></span><span style="display:block; width: 80%; float: left; height: 20px; background: #f4f5f7"></span></div>
                                        </a>
                                    </li>
                                    <li style="float:left; width: 25%; padding: 5px;">
                                        <input type="radio" class="flat-red" name="user_admin_skin"<?= checked('skin-black', get_user_option('user_admin_skin', (int) _escape($user['user_id'])), false); ?> value="skin-black" />
                                        <a href="javascript:void(0)" data-skin="skin-black" style="display: block; box-shadow: 0 0 3px rgba(0,0,0,0.4)" class="clearfix full-opacity-hover">
                                        <div style="box-shadow: 0 0 2px rgba(0,0,0,0.1)" class="clearfix"><span style="display:block; width: 20%; float: left; height: 7px; background: #fefefe"></span><span style="display:block; width: 80%; float: left; height: 7px; background: #fefefe"></span></div>
                                        <div><span style="display:block; width: 20%; float: left; height: 20px; background: #222"></span><span style="display:block; width: 80%; float: left; height: 20px; background: #f4f5f7"></span></div>
                                        </a>
                                    </li>
                                    <li style="float:left; width: 25%; padding: 5px;">
                                        <input type="radio" class="flat-red" name="user_admin_skin"<?= checked('skin-purple', get_user_option('user_admin_skin', (int) _escape($user['user_id'])), false); ?> value="skin-purple" />
                                        <a href="javascript:void(0)" data-skin="skin-purple" style="display: block; box-shadow: 0 0 3px rgba(0,0,0,0.4)" class="clearfix full-opacity-hover">
                                        <div><span style="display:block; width: 20%; float: left; height: 7px;" class="bg-purple-active"></span><span class="bg-purple" style="display:block; width: 80%; float: left; height: 7px;"></span></div>
                                        <div><span style="display:block; width: 20%; float: left; height: 20px; background: #222d32"></span><span style="display:block; width: 80%; float: left; height: 20px; background: #f4f5f7"></span></div>
                                        </a>
                                    </li>
                                    <li style="float:left; width: 25%; padding: 5px;">
                                        <input type="radio" class="flat-red" name="user_admin_skin"<?= checked('skin-green', get_user_option('user_admin_skin', (int) _escape($user['user_id'])), false); ?> value="skin-green" />
                                        <a href="javascript:void(0)" data-skin="skin-green" style="display: block; box-shadow: 0 0 3px rgba(0,0,0,0.4)" class="clearfix full-opacity-hover">
                                        <div><span style="display:block; width: 20%; float: left; height: 7px;" class="bg-green-active"></span><span class="bg-green" style="display:block; width: 80%; float: left; height: 7px;"></span></div>
                                        <div><span style="display:block; width: 20%; float: left; height: 20px; background: #222d32"></span><span style="display:block; width: 80%; float: left; height: 20px; background: #f4f5f7"></span></div>
                                        </a>
                                    </li>
                                    <li style="float:left; width: 25%; padding: 5px;">
                                        <input type="radio" class="flat-red" name="user_admin_skin"<?= checked('skin-red', get_user_option('user_admin_skin', (int) _escape($user['user_id'])), false); ?> value="skin-red" />
                                        <a href="javascript:void(0)" data-skin="skin-red" style="display: block; box-shadow: 0 0 3px rgba(0,0,0,0.4)" class="clearfix full-opacity-hover">
                                        <div><span style="display:block; width: 20%; float: left; height: 7px;" class="bg-red-active"></span><span class="bg-red" style="display:block; width: 80%; float: left; height: 7px;"></span></div>
                                        <div><span style="display:block; width: 20%; float: left; height: 20px; background: #222d32"></span><span style="display:block; width: 80%; float: left; height: 20px; background: #f4f5f7"></span></div>
                                        </a>
                                    </li>
                                    <li style="float:left; width: 25%; padding: 5px;">
                                        <input type="radio" class="flat-red" name="user_admin_skin"<?= checked('skin-yellow', get_user_option('user_admin_skin', (int) _escape($user['user_id'])), false); ?> value="skin-yellow" />
                                        <a href="javascript:void(0)" data-skin="skin-yellow" style="display: block; box-shadow: 0 0 3px rgba(0,0,0,0.4)" class="clearfix full-opacity-hover">
                                        <div><span style="display:block; width: 20%; float: left; height: 7px;" class="bg-yellow-active"></span><span class="bg-yellow" style="display:block; width: 80%; float: left; height: 7px;"></span></div>
                                        <div><span style="display:block; width: 20%; float: left; height: 20px; background: #222d32"></span><span style="display:block; width: 80%; float: left; height: 20px; background: #f4f5f7"></span></div>
                                        </a>
                                    </li>
                                    <li style="float:left; width: 25%; padding: 5px;">
                                        <input type="radio" class="flat-red" name="user_admin_skin"<?= checked('skin-blue-light', get_user_option('user_admin_skin', (int) _escape($user['user_id'])), false); ?> value="skin-blue-light" />
                                        <a href="javascript:void(0)" data-skin="skin-blue-light" style="display: block; box-shadow: 0 0 3px rgba(0,0,0,0.4)" class="clearfix full-opacity-hover">
                                        <div><span style="display:block; width: 20%; float: left; height: 7px; background: #367fa9"></span><span class="bg-light-blue" style="display:block; width: 80%; float: left; height: 7px;"></span></div>
                                        <div><span style="display:block; width: 20%; float: left; height: 20px; background: #f9fafc"></span><span style="display:block; width: 80%; float: left; height: 20px; background: #f4f5f7"></span></div>
                                        </a>
                                    </li>
                                    <li style="float:left; width: 25%; padding: 5px;">
                                        <input type="radio" class="flat-red" name="user_admin_skin"<?= checked('skin-black-light', get_user_option('skin-red', (int) _escape($user['user_id'])), false); ?> value="skin-black-light" />
                                        <a href="javascript:void(0)" data-skin="skin-black-light" style="display: block; box-shadow: 0 0 3px rgba(0,0,0,0.4)" class="clearfix full-opacity-hover">
                                        <div style="box-shadow: 0 0 2px rgba(0,0,0,0.1)" class="clearfix"><span style="display:block; width: 20%; float: left; height: 7px; background: #fefefe"></span><span style="display:block; width: 80%; float: left; height: 7px; background: #fefefe"></span></div>
                                        <div><span style="display:block; width: 20%; float: left; height: 20px; background: #f9fafc"></span><span style="display:block; width: 80%; float: left; height: 20px; background: #f4f5f7"></span></div>
                                        </a>
                                    </li>
                                    <li style="float:left; width: 25%; padding: 5px;">
                                        <input type="radio" class="flat-red" name="user_admin_skin"<?= checked('skin-purple-light', get_user_option('user_admin_skin', (int) _escape($user['user_id'])), false); ?> value="skin-purple-light" />
                                        <a href="javascript:void(0)" data-skin="skin-purple-light" style="display: block; box-shadow: 0 0 3px rgba(0,0,0,0.4)" class="clearfix full-opacity-hover">
                                        <div><span style="display:block; width: 20%; float: left; height: 7px;" class="bg-purple-active"></span><span class="bg-purple" style="display:block; width: 80%; float: left; height: 7px;"></span></div>
                                        <div><span style="display:block; width: 20%; float: left; height: 20px; background: #f9fafc"></span><span style="display:block; width: 80%; float: left; height: 20px; background: #f4f5f7"></span></div>
                                        </a>
                                    </li>
                                    <li style="float:left; width: 25%; padding: 5px;">
                                        <input type="radio" class="flat-red" name="user_admin_skin"<?= checked('skin-green-light', get_user_option('user_admin_skin', (int) _escape($user['user_id'])), false); ?> value="skin-green-light" />
                                        <a href="javascript:void(0)" data-skin="skin-green-light" style="display: block; box-shadow: 0 0 3px rgba(0,0,0,0.4)" class="clearfix full-opacity-hover">
                                        <div><span style="display:block; width: 20%; float: left; height: 7px;" class="bg-green-active"></span><span class="bg-green" style="display:block; width: 80%; float: left; height: 7px;"></span></div>
                                        <div><span style="display:block; width: 20%; float: left; height: 20px; background: #f9fafc"></span><span style="display:block; width: 80%; float: left; height: 20px; background: #f4f5f7"></span></div>
                                        </a>
                                    </li>
                                    <li style="float:left; width: 25%; padding: 5px;">
                                        <input type="radio" class="flat-red" name="user_admin_skin"<?= checked('skin-red-light', get_user_option('user_admin_skin', (int) _escape($user['user_id'])), false); ?> value="skin-red-light" />
                                        <a href="javascript:void(0)" data-skin="skin-red-light" style="display: block; box-shadow: 0 0 3px rgba(0,0,0,0.4)" class="clearfix full-opacity-hover">
                                        <div><span style="display:block; width: 20%; float: left; height: 7px;" class="bg-red-active"></span><span class="bg-red" style="display:block; width: 80%; float: left; height: 7px;"></span></div>
                                        <div><span style="display:block; width: 20%; float: left; height: 20px; background: #f9fafc"></span><span style="display:block; width: 80%; float: left; height: 20px; background: #f4f5f7"></span></div>
                                        </a>
                                    </li>
                                    <li style="float:left; width: 25%; padding: 5px;">
                                        <input type="radio" class="flat-red" name="user_admin_skin"<?= checked('skin-yellow-light', get_user_option('user_admin_skin', (int) _escape($user['user_id'])), false); ?> value="skin-yellow-light" />
                                        <a href="javascript:void(0)" data-skin="skin-yellow-light" style="display: block; box-shadow: 0 0 3px rgba(0,0,0,0.4)" class="clearfix full-opacity-hover">
                                        <div><span style="display:block; width: 20%; float: left; height: 7px;" class="bg-yellow-active"></span><span class="bg-yellow" style="display:block; width: 80%; float: left; height: 7px;"></span></div>
                                        <div><span style="display:block; width: 20%; float: left; height: 20px; background: #f9fafc"></span><span style="display:block; width: 80%; float: left; height: 20px; background: #f4f5f7"></span></div>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            
                            <?php
                            /**
                             * Fires at the end of the 'Layout Options' section on the 'Profile' screen.
                             * 
                             * @since 1.0.0
                             * @param array $user User data array.
                             */
                            $app->hook->{'do_action'}('user_profile_layout', $user);
                            ?>
                        </div>
                    </div>
                    
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?= _t('Name', 'tritan-cms'); ?></h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                            <div class="form-group">
                                <label><strong><?= _t('Username', 'tritan-cms'); ?></strong></label>
                                <input type="text" class="form-control" name="user_login" value="<?= get_user_option('user_login', (int) _escape($user['user_id'])); ?>" readonly required>
                            </div>

                            <div class="form-group">
                                <label><strong><font color="red">*</font> <?= _t('First Name', 'tritan-cms'); ?></strong></label>
                                <input type="text" class="form-control" name="user_fname" value="<?= get_user_option('user_fname', (int) _escape($user['user_id'])); ?>" required>
                            </div>

                            <div class="form-group">
                                <label><strong><font color="red">*</font> <?= _t('Last Name', 'tritan-cms'); ?></strong></label>
                                <input type="text" class="form-control" name="user_lname" value="<?= get_user_option('user_lname', (int) _escape($user['user_id'])); ?>" required>
                            </div>
                            
                            <?php
                            /**
                             * Fires at the end of the 'Name' section on the 'Profile' screen.
                             * 
                             * @since 1.0.0
                             * @param array $user User data array.
                             */
                            $app->hook->{'do_action'}('user_profile_name', $user);
                            ?>
                        </div>
                    </div>
                    
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?= _t('Contact info', 'tritan-cms'); ?></h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">

                            <div class="form-group">
                                <label><strong><font color="red">*</font> <?= _t('Email', 'tritan-cms'); ?></strong></label>
                                <input type="email" class="form-control" name="user_email" value="<?= get_user_option('user_email', (int) _escape($user['user_id'])); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label><strong><?= _t('URL', 'tritan-cms'); ?></strong></label>
                                <input type="text" class="form-control" name="user_url" value="<?= get_user_option('user_url', (int) _escape($user['user_id'])); ?>" />
                            </div>
                            
                            <?php
                            /**
                             * Fires at the end of the 'Contact info' section on the 'Profile' screen.
                             * 
                             * @since 1.0.0
                             * @param array $user User data array.
                             */
                            $app->hook->{'do_action'}('user_profile_contact', $user);
                            ?>
                            
                        </div>
                    </div>
                    
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?= _t('Password', 'tritan-cms'); ?></h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                            
                            <div class="form-group">
                                <label><strong><?= _t('New Password', 'tritan-cms'); ?></strong></label>
                                <input type="text" class="form-control" name="user_pass" />
                                <p class="help-block"><?= _t('Leave blank if not updating password.'); ?></p>
                            </div>
                            
                            <?php
                            /**
                             * Fires at the end of the 'New Password' section on the 'Profile' screen.
                             * 
                             * @since 1.0.0
                             * @param array $user User data array.
                             */
                            $app->hook->{'do_action'}('user_profile_password', $user);
                            ?>
                            
                        </div>
                    </div>
                    
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?= _t('About yourself', 'tritan-cms'); ?></h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">

                            <div class="form-group">
                                <label><strong><?= _t('Biography', 'tritan-cms'); ?></strong></label>
                                <textarea class="form-control" name="user_bio" rows="5"><?= get_user_option('user_bio', (int) _escape($user['user_id'])); ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label><strong><?= _t('Profile Picture', 'tritan-cms'); ?></strong></label>
                                <div><?=get_user_avatar(get_user_option('user_email', (int) _escape($user['user_id'])), 100);?></div>
                                <p class="help-block"><?= sprintf(_t('You can change your profile picture on <a href="%s">Gravatar</a>.'), '//en.gravatar.com/'); ?></p>
                            </div>
                            
                            <?php
                            /**
                             * Fires at the end of the 'About yourself' section on the 'Profile' screen.
                             * 
                             * @since 1.0.0
                             * @param array $user User data array.
                             */
                            $app->hook->{'do_action'}('user_profile_about', $user);
                            ?>
                            
                        </div>
                    </div>
                    
                    <?php
                    /**
                     * Fires after the 'About yourself' section on the 'Profile' screen.
                     * 
                     * @since 1.0.0
                     * @param array $user User data array.
                     */
                    $app->hook->{'do_action'}('user_profile', $user);
                    ?>
                    
                </div>
                <!-- /.box-body -->
            </div>
            <!-- /.box -->

        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->
</form>
<!-- form end -->
<?php $app->view->stop(); ?>