<?php if (!defined('BASE_PATH')) exit('No direct script access allowed');
$app = \Liten\Liten::getInstance();
$app->view->extend('_layouts/admin');
$app->view->block('admin');
define('SCREEN_PARENT', 'options');
define('SCREEN', 'options-general');
?>
<!-- form start -->
<form name="form" method="post" data-toggle="validator" action="<?= get_base_url(); ?>admin/options-general/" id="form" autocomplete="off">
    <!-- Content Wrapper. Contains post content -->
    <div class="content-wrapper">
        <!-- Content Header (Post header) -->
            <div class="box box-solid">
                <div class="box-header with-border">
                    <i class="fa fa-cogs"></i>
                    <h3 class="box-title"><?= _t('General Options', 'tritan-cms'); ?></h3>

                    <div class="pull-right">
                        <button type="submit" class="btn btn-success"><i class="fa fa-pencil"></i> <?= _t('Update', 'tritan-cms'); ?></button>
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
                                <h3 class="box-title"><?= _t('General Options', 'tritan-cms'); ?></h3>
                            </div>
                            <!-- /.box-header -->
                            <div class="box-body">
                                <div class="form-group">
                                    <label><strong><font color="red">*</font> <?= _t('Site Name', 'tritan-cms'); ?></strong></label>
                                    <input type="text" class="form-control" name="sitename" value="<?= $app->hook->{'get_option'}('sitename'); ?>" required/>
                                </div>
                                <div class="form-group">
                                    <label><strong><?= _t('Site Description', 'tritan-cms'); ?></strong></label>
                                    <input type="text" class="form-control" name="site_description" value="<?= $app->hook->{'get_option'}('site_description'); ?>" />
                                </div>
                                <div class="form-group">
                                    <label><strong><font color="red">*</font> <?= _t('Admin Email', 'tritan-cms'); ?></strong></label>
                                    <input type="text" class="form-control" name="admin_email" value="<?= $app->hook->{'get_option'}('admin_email'); ?>" required/>
                                </div>
                                <div class="form-group">
                                    <label><strong><font color="red">*</font> <?= _t('Local', 'tritan-cms'); ?></strong></label>
                                    <select class="form-control select2" name="ttcms_core_locale" style="width: 100%;" required>
                                        <option>&nbsp;</option>
                                        <?php ttcms_dropdown_languages($app->hook->{'get_option'}( 'ttcms_core_locale' )); ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label><strong><font color="red">*</font> <?= _t('Cookie Expire', 'tritan-cms'); ?></strong></label>
                                    <input type="text" class="form-control" name="cookieexpire" value="<?= $app->hook->{'get_option'}('cookieexpire'); ?>" required/>
                                </div>
                                <div class="form-group">
                                    <label><strong><font color="red">*</font> <?= _t('Cookie Path', 'tritan-cms'); ?></strong></label>
                                    <input type="text" class="form-control" name="cookiepath" value="<?= $app->hook->{'get_option'}('cookiepath'); ?>" required/>
                                </div>
                                <div class="form-group">
                                    <label><strong><font color="red">*</font> <?= _t('Cronjobs', 'tritan-cms'); ?></strong></label>
                                    <select class="form-control select2" name="enable_cron_jobs" style="width: 100%;" required>
                                        <option value=""> ------------------------- </option>
                                        <option value="1"<?=selected( $app->hook->{'get_option'}( 'enable_cron_jobs' ), '1', false ); ?>><?=_t( "On" );?></option>
                                        <option value="0"<?=selected( $app->hook->{'get_option'}( 'enable_cron_jobs' ), '0', false ); ?>><?=_t( "Off" );?></option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label><strong><font color="red">*</font> <?= _t('Site Cache', 'tritan-cms'); ?></strong></label>
                                    <select class="form-control select2" name="site_cache" style="width: 100%;" required>
                                        <option value=""> ------------------------- </option>
                                        <option value="1"<?=selected( $app->hook->{'get_option'}( 'site_cache' ), '1', false ); ?>><?=_t( "On" );?></option>
                                        <option value="0"<?=selected( $app->hook->{'get_option'}( 'site_cache' ), '0', false ); ?>><?=_t( "Off" );?></option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label><strong><font color="red">*</font> <?= _t('System Timezone', 'tritan-cms'); ?></strong></label>
                                    <select class="form-control select2" name="system_timezone" style="width: 100%;" required>
                                        <option value=""> ------------------------- </option>
                                        <?php foreach(generate_timezone_list() as $k => $v) : ?>
                                            <option value="<?=$k;?>"<?=selected( $app->hook->{'get_option'}( 'system_timezone' ), $k, false ); ?>><?=$v;?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label><strong><font color="red">*</font> <?= _t('API Key', 'tritan-cms'); ?></strong></label>
                                    <input type="text" class="form-control" name="api_key" value="<?= $app->hook->{'get_option'}('api_key'); ?>" required/>
                                </div>
                                <?php $app->hook->{'do_action'}('options_general_form'); ?>
                            </div>
                            <!-- /.box-body -->
                        </div>
                        <!-- /.box-body -->
                    </div>
                    <!-- /.left column -->

                </div>
                <!--/.row -->
            </section>
            <!-- /.Main content -->
    </div>
</form>
<!-- /.Content Wrapper. Contains post content -->
<?php $app->view->stop(); ?>