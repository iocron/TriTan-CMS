<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use Jenssegers\Date\Date;
use TriTan\Config;
use TriTan\Functions as func;

/**
 * Audit Trail View
 *  
 * @license GPLv3
 * 
 * @since       0.9
 * @package     TriTan CMS
 * @author      Joshua Parker <joshmac3@icloud.com>
 */
$this->layout('main::_layouts/admin-layout');
$this->section('backend');
Config::set('screen_parent', 'dashboard');
Config::set('screen_child', 'audit');
?>        

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="box box-solid">
        <div class="box-header with-border">
            <i class="fa fa-road"></i>
            <h3 class="box-title"><?= func\_t('Audit Trail', 'tritan-cms'); ?></h3>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">

        <?= func\_ttcms_flash()->showMessage(); ?> 

        <!-- SELECT2 EXAMPLE -->
        <div class="box box-default">
            <div class="box-body">
                <table id="example2" class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th class="text-center"><?= func\_t('Action', 'tritan-cms'); ?></th>
                            <th class="text-center"><?= func\_t('Process', 'tritan-cms'); ?></th>
                            <th class="text-center"><?= func\_t('Record', 'tritan-cms'); ?></th>
                            <th class="text-center"><?= func\_t('Username', 'tritan-cms'); ?></th>
                            <th class="text-center"><?= func\_t('Action Date', 'tritan-cms'); ?></th>
                            <th class="text-center"><?= func\_t('Expire Date', 'tritan-cms'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($this->audit as $aud) : ?>
                            <tr class="gradeX">
                                <td class="text-center"><?= $aud['action']; ?></td>
                                <td class="text-center"><?= $aud['process']; ?></td>
                                <td class="text-center"><?= $aud['record']; ?></td>
                                <td class="text-center"><?= $aud['uname']; ?></td>
                                <td class="text-center"><?= Date::parse($aud['created_at'])->format('D, M d, o'); ?></td>
                                <td class="text-center"><?= Date::parse($aud['expires_at'])->format('D, M d, o'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="text-center"><?= func\_t('Action', 'tritan-cms'); ?></th>
                            <th class="text-center"><?= func\_t('Process', 'tritan-cms'); ?></th>
                            <th class="text-center"><?= func\_t('Record', 'tritan-cms'); ?></th>
                            <th class="text-center"><?= func\_t('Username', 'tritan-cms'); ?></th>
                            <th class="text-center"><?= func\_t('Action Date', 'tritan-cms'); ?></th>
                            <th class="text-center"><?= func\_t('Expire Date', 'tritan-cms'); ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <!-- /.box-body -->
        </div>
        <!-- /.box -->

    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->
<?php $this->stop(); ?>