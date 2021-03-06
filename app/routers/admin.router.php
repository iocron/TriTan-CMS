<?php

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use TriTan\Config;
use TriTan\Exception\Exception;
use Cascade\Cascade;
use TriTan\Functions as func;

$user = func\get_userdata(func\get_current_user_id());

/**
 * Before router checks to make sure the logged in user
 * us allowed to access admin.
 */
$app->before('GET|POST', '/admin(.*)', function() {
    if (!func\is_user_logged_in()) {
        func\_ttcms_flash()->{'error'}(func\_t('401 - Error: Unauthorized.', 'tritan-cms'), func\get_base_url() . 'login' . '/');
        exit();
    }
    if (!func\current_user_can('access_admin')) {
        func\_ttcms_flash()->{'error'}(func\_t('403 - Error: Forbidden.', 'tritan-cms'), func\get_base_url());
        exit();
    }
});

$app->group('/admin', function() use ($app, $user) {

    $app->get('/', function () use($app) {

        $app->foil->render('main::admin/index', [
            'title' => func\_t('Admin Dashboard', 'tritan-cms')
                ]
        );
    });

    $app->before('GET', '/media/', function() {
        if (!func\current_user_can('manage_media')) {
            func\_ttcms_flash()->{'error'}(func\_t('You do not have permission to manage the media library.', 'tritan-cms'), func\get_base_url() . 'admin' . '/');
            exit();
        }
    });

    $app->get('/media/', function () use($app) {

        $app->foil->render('main::admin/media', [
            'title' => func\_t('Media Library', 'tritan-cms')
                ]
        );
    });

    $app->before('GET', '/ftp/', function() {
        if (!func\current_user_can('manage_ftp')) {
            func\_ttcms_flash()->{'error'}(func\_t('You do not have permission to manage FTP.', 'tritan-cms'), func\get_base_url() . 'admin' . '/');
            exit();
        }
    });

    $app->get('/ftp/', function () use($app) {

        $app->foil->render('main::admin/ftp', [
            'title' => func\_t('FTP', 'tritan-cms')
                ]
        );
    });

    /**
     * Before route checks to make sure the logged in user
     * us allowed to manage options/settings.
     */
    $app->before('GET|POST', '/options-general/', function() {
        if (!func\current_user_can('manage_options')) {
            func\_ttcms_flash()->{'error'}(func\_t('You do not have permission to manage options.', 'tritan-cms'), func\get_base_url() . 'admin' . '/');
            exit();
        }
    });

    $app->match('GET|POST', '/options-general/', function() use($app) {
        if ($app->req->isPost()) {
            $options = [
                'sitename', 'site_description', 'admin_email', 'ttcms_core_locale',
                'cookieexpire', 'cookiepath', 'enable_cron_jobs', 'site_cache',
                'system_timezone', 'api_key'
            ];
            foreach ($options as $option_name) {
                if (!isset($app->req->post[$option_name]))
                    continue;
                $value = $app->req->post[$option_name];
                $app->hook->{'update_option'}($option_name, $value);
            }

            $site = $app->db->table('site');
            $site->begin();
            try {
                $site->where('site_id', (int) Config::get('site_id'))
                        ->update([
                            'site_name' => $app->req->post['sitename'],
                            'site_modified' => (string) Jenssegers\Date\Date::now()
                ]);
                $site->commit();
            } catch (Exception $ex) {
                $site->rollback();
                Cascade::getLogger('error')->{'error'}(sprintf('SQLSTATE[%s]: %s', $ex->getCode(), $ex->getMessage()));
            }
            func\_ttcms_flash()->{'success'}(func\_ttcms_flash()->notice(200), $app->req->server['HTTP_REFERER']);
        }

        $app->foil->render('main::admin/options-general', [
            'title' => func\_t('General Options', 'tritan-cms'),
                ]
        );
    });

    /**
     * Before route checks to make sure the logged in user
     * us allowed to manage options/settings.
     */
    $app->before('GET|POST', '/options-reading/', function() {
        if (!func\current_user_can('manage_options')) {
            func\_ttcms_flash()->{'error'}(func\_t('You do not have permission to manage options.', 'tritan-cms'), func\get_base_url() . 'admin' . '/');
            exit();
        }
    });

    $app->match('GET|POST', '/options-reading/', function() use($app) {
        if ($app->req->isPost()) {
            $options = [
                'current_site_theme', 'posts_per_page', 'date_format', 'time_format'
            ];
            foreach ($options as $option_name) {
                if (!isset($app->req->post[$option_name]))
                    continue;
                $value = $app->req->post[$option_name];
                $app->hook->{'update_option'}($option_name, $value);
            }
            func\_ttcms_flash()->{'success'}(func\_ttcms_flash()->notice(200), $app->req->server['HTTP_REFERER']);
        }

        $app->foil->render('main::admin/options-reading', [
            'title' => func\_t('Reading Options', 'tritan-cms'),
                ]
        );
    });

    /**
     * Before route check.
     */
    $app->before('GET|POST', '/plugin/', function() {
        if (!func\current_user_can('manage_plugins')) {
            func\_ttcms_flash()->{'error'}(func\_t("You do not have permission to manage plugins.", 'tritan-cms'), func\get_base_url() . 'admin' . '/');
        }
    });

    $app->get('/plugin/', function() use($app) {
        $app->foil->render('main::admin/plugin/index', ['title' => func\_t('Plugins')]);
    });

    /**
     * Before route check.
     */
    $app->before('GET|POST', '/plugin/install/', function() {
        if (!func\current_user_can('install_plugins')) {
            func\_ttcms_flash()->{'error'}(func\_t("You do not have permission to install plugins.", 'tritan-cms'), func\get_base_url() . 'admin' . '/');
        }
    });

    $app->match('GET|POST', '/plugin/install/', function() use($app) {
        if ($app->req->isPost()) {
            $name = explode(".", $_FILES["plugin_zip"]["name"]);
            $accepted_types = [
                'application/zip',
                'application/x-zip-compressed',
                'multipart/x-zip',
                'application/x-compressed'
            ];

            foreach ($accepted_types as $mime_type) {
                if ($mime_type == $type) {
                    $okay = true;
                    break;
                }
            }

            $continue = strtolower($name[1]) == 'zip' ? true : false;

            if (!$continue) {
                func\_ttcms_flash()->{'error'}(func\_t('The file you are trying to upload is not the accepted file type (.zip). Please try again.'));
            }
            $target_path = BASE_PATH . 'plugins' . DS . $_FILES["plugin_zip"]["name"];
            if (move_uploaded_file($_FILES["plugin_zip"]["tmp_name"], $target_path)) {
                $zip = new \ZipArchive();
                $x = $zip->open($target_path);
                if ($x === true) {
                    $zip->extractTo(BASE_PATH . 'plugins' . DS);
                    $zip->close();
                    unlink($target_path);
                }
                func\_ttcms_flash()->{'success'}(func\_t('Your plugin was uploaded and installed properly.'), $app->req->server['HTTP_REFERER']);
            } else {
                func\_ttcms_flash()->{'error'}(func\_t('There was a problem uploading your plugin. Please try again or check the plugin package.'), $app->req->server['HTTP_REFERER']);
            }
        }

        $app->foil->render('main::admin/plugin/install', ['title' => func\_t('Install Plugins')]);
    });

    $app->before('GET|POST', '/plugin/activate/', function () {
        if (!func\current_user_can('manage_plugins')) {
            func\_ttcms_flash()->{'error'}(func\_t('Permission denied to activate a plugin.'), func\get_base_url() . 'admin' . '/');
            exit();
        }
    });

    $app->get('/plugin/activate/', function () use($app) {
        ob_start();

        $plugin_name = $app->req->get['id'];

        /**
         * This function will validate a plugin and make sure
         * there are no errors before activating it.
         *
         * @since 0.9
         */
        func\ttcms_validate_plugin($plugin_name);

        if (ob_get_length() > 0) {
            $output = ob_get_clean();
            $error = new TriTan\Error('unexpected_output', func\_t('The plugin generated unexpected output.'), $output);
            Cascade::getLogger('error')->{'error'}(sprintf('PLUGIN[%s]: %s', $error->get_error_code(), $error->get_error_message()));
            func\_ttcms_flash()->{'error'}($error->get_error_message());
        }
        ob_end_clean();

        func\ttcms_redirect($app->req->server['HTTP_REFERER']);
    });

    $app->before('GET|POST', '/deactivate/', function () {
        if (!func\current_user_can('manage_plugins')) {
            func\_ttcms_flash()->{'error'}(func\_t('Permission denied to deactivate a plugin.'), func\get_base_url() . 'admin' . '/');
            exit();
        }
    });

    $app->get('/plugin/deactivate/', function () use($app) {
        $pluginName = $app->req->get['id'];
        /**
         * Fires before a specific plugin is deactivated.
         *
         * $pluginName refers to the plugin's
         * name (i.e. smtp.plugin.php).
         *
         * @since 0.9
         * @param string $pluginName
         *            The plugin's base name.
         */
        $app->hook->{'do_action'}('deactivate_plugin', $pluginName);

        /**
         * Fires as a specifig plugin is being deactivated.
         *
         * $pluginName refers to the plugin's
         * name (i.e. smtp.plugin.php).
         *
         * @since 0.9
         * @param string $pluginName
         *            The plugin's base name.
         */
        $app->hook->{'do_action'}('deactivate_' . $pluginName);

        func\deactivate_plugin($pluginName);

        /**
         * Fires after a specific plugin has been deactivated.
         *
         * $pluginName refers to the plugin's
         * name (i.e. smtp.plugin.php).
         *
         * @since 0.9
         * @param string $pluginName
         *            The plugin's base name.
         */
        $app->hook->{'do_action'}('deactivated_plugin', $pluginName);

        func\ttcms_redirect($app->req->server['HTTP_REFERER']);
    });

    /**
     * Before route check.
     */
    $app->before('GET|POST|PATCH|PUT|OPTIONS|DELETE', '/connector/', function() {
        if (!func\is_user_logged_in()) {
            func\_ttcms_flash()->{'error'}(func\_t("You do not have permission to access requested screen", 'tritan-cms'), func\get_base_url() . 'admin' . '/');
            exit();
        }
    });

    $app->match('GET|POST|PATCH|PUT|OPTIONS|DELETE', '/connector/', function () use($app) {
        error_reporting(0);
        try {
            func\_mkdir(BASE_PATH . 'private' . DS . 'sites' . DS . (int) Config::get('site_id') . DS . 'uploads' . DS . '__optimized__' . DS);
        } catch (\TriTan\Exception\IOException $e) {
            Cascade::getLogger('error')->error(sprintf('IOSTATE[%s]: Unable to create directory: %s', $e->getCode(), $e->getMessage()));
        }
        $opts = [
            // 'debug' => true,
            'locale' => 'en_US.UTF-8',
            'roots' => [
                [
                    'driver' => 'LocalFileSystem',
                    'startPath' => Config::get('site_path') . 'uploads' . DS,
                    'path' => Config::get('site_path') . 'uploads' . DS,
                    'alias' => 'Media Library',
                    'mimeDetect' => 'auto',
                    'accessControl' => 'access',
                    'tmbURL' => func\get_base_url() . 'private/sites/' . (int) Config::get('site_id') . '/uploads/' . '.tmb',
                    'tmpPath' => Config::get('site_path') . 'uploads' . DS . '.tmb',
                    'URL' => func\get_base_url() . 'private/sites/' . (int) Config::get('site_id') . '/uploads/',
                    'attributes' => [
                        [
                            'read' => true,
                            'write' => true,
                            'locked' => false
                        ],
                        [
                            'pattern' => '/\__optimized__/',
                            'read' => false,
                            'write' => false,
                            'hidden' => true,
                            'locked' => true
                        ],
                        [
                            'pattern' => '/\.gitkeep/',
                            'read' => false,
                            'write' => false,
                            'hidden' => true,
                            'locked' => true
                        ],
                        [
                            'pattern' => '/\.gitignore/',
                            'read' => false,
                            'write' => false,
                            'hidden' => true,
                            'locked' => true
                        ],
                        [
                            'pattern' => '/\.htaccess/',
                            'read' => false,
                            'write' => false,
                            'hidden' => true,
                            'locked' => true
                        ],
                        [
                            'pattern' => '/\index.html/',
                            'read' => false,
                            'write' => false,
                            'hidden' => true,
                            'locked' => false
                        ],
                        [
                            'pattern' => '/\.tmb/',
                            'read' => false,
                            'write' => false,
                            'hidden' => true,
                            'locked' => false
                        ],
                        [
                            'pattern' => '/\.quarantine/',
                            'read' => false,
                            'write' => false,
                            'hidden' => true,
                            'locked' => false
                        ],
                        [
                            'pattern' => '/\.DS_Store/',
                            'read' => false,
                            'write' => false,
                            'hidden' => true,
                            'locked' => false
                        ],
                        [
                            'pattern' => '/\.json$/',
                            'read' => true,
                            'write' => true,
                            'hidden' => false,
                            'locked' => false
                        ]
                    ],
                    'uploadMaxSize' => '500M',
                    'uploadAllow' => [
                        'text/plain', 'image/png', 'image/jpeg', 'image/gif', 'application/zip',
                        'text/csv', 'application/pdf', 'application/msword', 'application/vnd.ms-excel',
                        'application/vnd.ms-powerpoint', 'application/msword', 'application/vnd.ms-excel',
                        'application/vnd.ms-powerpoint', 'video/mp4'
                    ],
                    'uploadOrder' => ['allow', 'deny']
                ]
            ]
        ];
        // run elFinder
        $connector = new elFinderConnector(new elFinder($opts));
        $connector->run();
    });

    /**
     * Before route check.
     */
    $app->before('GET|POST|PATCH|PUT|OPTIONS|DELETE', '/ftp-connector/', function() {
        if (!func\is_user_logged_in()) {
            func\_ttcms_flash()->{'error'}(func\_t("You do not have permission to access requested screen", 'tritan-cms'), func\get_base_url() . 'admin' . '/');
            exit();
        }
    });

    $app->match('GET|POST|PATCH|PUT|OPTIONS|DELETE', '/ftp-connector/', function () use($app) {
        error_reporting(0);
        try {
            func\_mkdir(BASE_PATH . 'private' . DS . 'sites' . DS . (int) Config::get('site_id') . DS . 'uploads' . DS . '__optimized__' . DS);
        } catch (\TriTan\Exception\IOException $e) {
            Cascade::getLogger('error')->error(sprintf('IOSTATE[%s]: Unable to create directory: %s', $e->getCode(), $e->getMessage()));
        }
        $opts = [
            // 'debug' => true,
            'locale' => 'en_US.UTF-8',
            'roots' => [
                [
                    'driver' => 'LocalFileSystem',
                    'path' => BASE_PATH . 'private' . DS,
                    'tmbURL' => func\get_base_url() . 'private/.tmb',
                    'tmpPath' => BASE_PATH . 'private' . DS . '.tmb',
                    'detectDirIcon' => 'favicon.ico',
                    'alias' => 'Files',
                    'mimeDetect' => 'auto',
                    'accessControl' => 'access',
                    'attributes' => [
                        [
                            'read' => true,
                            'write' => true,
                            'locked' => false
                        ],
                        [
                            'pattern' => '/\.gitkeep/',
                            'read' => false,
                            'write' => false,
                            'hidden' => true,
                            'locked' => true
                        ],
                        [
                            'pattern' => '/\.gitignore/',
                            'read' => false,
                            'write' => false,
                            'hidden' => true,
                            'locked' => true
                        ],
                        [
                            'pattern' => '/\.htaccess/',
                            'read' => false,
                            'write' => false,
                            'hidden' => true,
                            'locked' => true
                        ],
                        [
                            'pattern' => '/\index.html/',
                            'read' => false,
                            'write' => false,
                            'hidden' => true,
                            'locked' => false
                        ],
                        [
                            'pattern' => '/\.tmb/',
                            'read' => false,
                            'write' => false,
                            'hidden' => true,
                            'locked' => false
                        ],
                        [
                            'pattern' => '/\.quarantine/',
                            'read' => false,
                            'write' => false,
                            'hidden' => true,
                            'locked' => false
                        ],
                        [
                            'pattern' => '/\.DS_Store/',
                            'read' => false,
                            'write' => false,
                            'hidden' => true,
                            'locked' => false
                        ],
                        [
                            'pattern' => '/\.json$/',
                            'read' => true,
                            'write' => true,
                            'hidden' => false,
                            'locked' => false
                        ]
                    ],
                    'uploadMaxSize' => '500M',
                    'uploadAllow' => [
                        'text/plain', 'text/html', 'application/json', 'application/xml',
                        'application/javascript'
                    ],
                    'uploadOrder' => ['allow', 'deny']
                ],
                [
                    'driver' => 'LocalFileSystem',
                    'startPath' => Config::get('site_path') . 'uploads' . DS,
                    'path' => Config::get('site_path') . 'uploads' . DS,
                    'alias' => 'Media Library',
                    'mimeDetect' => 'auto',
                    'accessControl' => 'access',
                    'tmbURL' => func\get_base_url() . 'private/sites/' . (int) Config::get('site_id') . '/uploads/' . '.tmb',
                    'tmpPath' => Config::get('site_path') . 'uploads' . DS . '.tmb',
                    'URL' => func\get_base_url() . 'private/sites/' . (int) Config::get('site_id') . '/uploads/',
                    'attributes' => [
                        [
                            'read' => true,
                            'write' => true,
                            'locked' => false
                        ],
                        [
                            'pattern' => '/\__optimized__/',
                            'read' => false,
                            'write' => false,
                            'hidden' => true,
                            'locked' => true
                        ],
                        [
                            'pattern' => '/\.gitkeep/',
                            'read' => false,
                            'write' => false,
                            'hidden' => true,
                            'locked' => true
                        ],
                        [
                            'pattern' => '/\.gitignore/',
                            'read' => false,
                            'write' => false,
                            'hidden' => true,
                            'locked' => true
                        ],
                        [
                            'pattern' => '/\.htaccess/',
                            'read' => false,
                            'write' => false,
                            'hidden' => true,
                            'locked' => true
                        ],
                        [
                            'pattern' => '/\index.html/',
                            'read' => false,
                            'write' => false,
                            'hidden' => true,
                            'locked' => false
                        ],
                        [
                            'pattern' => '/\.tmb/',
                            'read' => false,
                            'write' => false,
                            'hidden' => true,
                            'locked' => false
                        ],
                        [
                            'pattern' => '/\.quarantine/',
                            'read' => false,
                            'write' => false,
                            'hidden' => true,
                            'locked' => false
                        ],
                        [
                            'pattern' => '/\.DS_Store/',
                            'read' => false,
                            'write' => false,
                            'hidden' => true,
                            'locked' => false
                        ],
                        [
                            'pattern' => '/\.json$/',
                            'read' => true,
                            'write' => true,
                            'hidden' => false,
                            'locked' => false
                        ]
                    ],
                    'uploadMaxSize' => '500M',
                    'uploadAllow' => [
                        'text/plain', 'image/png', 'image/jpeg', 'image/gif', 'application/zip',
                        'text/csv', 'application/pdf', 'application/msword', 'application/vnd.ms-excel',
                        'application/vnd.ms-powerpoint', 'application/msword', 'application/vnd.ms-excel',
                        'application/vnd.ms-powerpoint', 'video/mp4'
                    ],
                    'uploadOrder' => ['allow', 'deny']
                ]
            ]
        ];
        // run elFinder
        $connector = new elFinderConnector(new elFinder($opts));
        $connector->run();
    });

    /**
     * Before route check.
     */
    $app->before('GET|POST', '/elfinder/', function() {
        if (!func\is_user_logged_in()) {
            func\_ttcms_flash()->{'error'}(func\_t("You don't have permission to view the requested screen", 'tritan-cms'), func\get_base_url() . 'login' . '/');
            exit();
        }
    });

    $app->match('GET|POST', '/elfinder/', function () use($app) {

        $app->foil->render('main::admin/elfinder', [
            'title' => 'elfinder 2.1'
                ]
        );
    });

    /**
     * Before route check.
     */
    $app->before('GET|POST', '/permission.*', function() {
        if (!func\current_user_can('manage_roles')) {
            func\_ttcms_flash()->{'error'}(func\_t("You don't have permission to manage roles/permissions.", 'tritan-cms'), func\get_base_url() . 'admin' . '/');
            exit();
        }
    });

    $app->match('GET|POST', '/permission/', function () use($app) {


        $app->foil->render('main::admin/permission/index', [
            'title' => func\_t('Manage Permissions', 'tritan-cms')
                ]
        );
    });

    $app->match('GET|POST', '/permission/(\d+)/', function ($id) use($app, $user) {
        if ($app->req->isPost()) {
            $perm = $app->db->table('permission');
            $perm->begin();
            try {
                $perm->where('permission_id', (int) $id)
                        ->update([
                            'permission_key' => func\if_null($app->req->post['permission_key']),
                            'permission_name' => func\if_null($app->req->post['permission_name']),
                ]);
                $perm->commit();
                func\ttcms_logger_activity_log_write('Update Record', 'Permission', $app->req->post['permission_name'], func\_escape($user->user_login));
                func\_ttcms_flash()->{'success'}(func\_ttcms_flash()->notice(200), $app->req->server['HTTP_REFERER']);
            } catch (Exception $ex) {
                $perm->rollback();
                Cascade::getLogger('error')->{'error'}($ex->getMessage());
                func\_ttcms_flash()->{'error'}($ex->getMessage());
            }
        }

        $perm = $app->db->table('permission')->where('permission_id', (int) $id)->first();


        /**
         * If the database table doesn't exist, then it
         * is false and a 404 should be sent.
         */
        if ($perm == false) {

            $app->res->_format('json', 404);
            exit();
        }
        /**
         * If the query is legit, but there
         * is no data in the table, then 404
         * will be shown.
         */ elseif (empty($perm) == true) {

            $app->res->_format('json', 404);
            exit();
        }
        /**
         * If data is zero, 404 not found.
         */ elseif ((int) func\_escape($perm['permission_id']) <= 0) {

            $app->res->_format('json', 404);
            exit();
        }
        /**
         * If we get to this point, the all is well
         * and it is ok to process the query and print
         * the results in a html format.
         */ else {

            $app->foil->render('main::admin/permission/update', [
                'title' => func\_t('Update Permission', 'tritan-cms'),
                'perm' => $perm
                    ]
            );
        }
    });

    $app->match('GET|POST', '/permission/create/', function () use($app, $user) {

        if ($app->req->isPost()) {
            $perm = $app->db->table('permission');
            $perm->begin();
            try {
                $permission_id = func\auto_increment('permission', 'permission_id');
                $perm->insert([
                    'permission_id' => (int) $permission_id,
                    'permission_key' => func\if_null($app->req->post['permission_key']),
                    'permission_name' => func\if_null($app->req->post['permission_name']),
                ]);
                $perm->commit();
                func\ttcms_logger_activity_log_write('Create Record', 'Permission', $app->req->post['permission_name'], func\_escape($user->user_login));
                func\_ttcms_flash()->{'success'}(func\_ttcms_flash()->notice(200), func\get_base_url() . 'admin/permission' . '/');
            } catch (Exception $ex) {
                $perm->rollback();
                Cascade::getLogger('error')->{'error'}($ex->getMessage());
                func\_ttcms_flash()->{'error'}($ex->getMessage());
            }
        }

        $app->foil->render('main::admin/permission/create', [
            'title' => func\_t('Create New Permission', 'tritan-cms')
                ]
        );
    });

    /**
     * Before route check.
     */
    $app->before('GET|POST', '/role(.*)', function() {
        if (!func\current_user_can('manage_roles')) {
            func\_ttcms_flash()->{'error'}(func\_t("You don't have permission to manage roles/permissions."), func\get_base_url() . 'admin' . '/');
            exit();
        }
    });

    $app->match('GET|POST', '/role/', function () use($app) {

        $app->foil->render('main::admin/role/index', [
            'title' => func\_t('Manage Roles', 'tritan-cms')
                ]
        );
    });

    $app->match('GET|POST', '/role/(\d+)/', function ($id) use($app) {

        $role = $app->db->table('role')
                ->where('role_id', (int) $id)
                ->first();

        /**
         * If the database table doesn't exist, then it
         * is false and a 404 should be sent.
         */
        if ($role == false) {

            $app->res->_format('json', 404);
            exit();
        }
        /**
         * If the query is legit, but there
         * is no data in the table, then 404
         * will be shown.
         */ elseif (empty($role) == true) {

            $app->res->_format('json', 404);
            exit();
        }
        /**
         * If data is zero, 404 not found.
         */ elseif ((int) func\_escape($role['role_id']) <= 0) {

            $app->res->_format('json', 404);
            exit();
        }
        /**
         * If we get to this point, the all is well
         * and it is ok to process the query and print
         * the results in a html format.
         */ else {

            $app->foil->render('main::admin/role/update', [
                'title' => func\_t('Update Role', 'tritan-cms'),
                'role' => $role
                    ]
            );
        }
    });

    $app->match('GET|POST', '/role/create/', function () use($app) {

        if ($app->req->isPost()) {
            $role = $app->db->table('role');
            $role->begin();
            try {
                $role_id = func\auto_increment('role', 'role_id');
                $role->insert([
                    'role_id' => (int) $role_id,
                    'role_name' => (string) $app->req->post['role_name'],
                    'role_key' => (string) _trim($app->req->post['role_key']),
                    'role_permission' => $app->hook->{'maybe_serialize'}($app->req->post['role_permission'])
                ]);
                $role->commit();
                func\_ttcms_flash()->{'success'}(func\_ttcms_flash()->notice(200), func\get_base_url() . 'admin/role' . '/' . (int) $role_id . '/');
            } catch (Exception $e) {
                $role->rollback();
                func\_ttcms_flash()->{'error'}($e->getMessage());
            }
        }

        $app->foil->render('main::admin/role/create', [
            'title' => func\_t('Create Role', 'tritan-cms')
                ]
        );
    });

    $app->post('/role/edit-role/', function () use($app) {
        $role = $app->db->table('role');
        $role->begin();
        try {
            $role->where('role_id', (int) $app->req->post['role_id'])
                    ->update([
                        'role_name' => (string) $app->req->post['role_name'],
                        'role_key' => (string) _trim($app->req->post['role_key']),
                        'role_permission' => $app->hook->{'maybe_serialize'}($app->req->post['role_permission'])
            ]);
            $role->commit();
            func\_ttcms_flash()->{'success'}(func\_ttcms_flash()->notice(200));
        } catch (Exception $e) {
            $role->rollback();
            func\_ttcms_flash()->{'error'}($e->getMessage());
        }

        func\ttcms_redirect($app->req->server['HTTP_REFERER']);
    });

    /**
     * Before route check.
     */
    $app->before('GET|POST', '/system-snapshot/', function () {
        if (!func\current_user_can('manage_settings')) {
            func\_ttcms_flash()->{'error'}(func\_t("You don't have permission to view the System Snapshot Report screen.", 'tritan-cms'), func\get_base_url() . 'admin' . '/');
        }
    });

    $app->get('/system-snapshot/', function () use($app) {
        $user = $app->db->table('user')->where('user_status', 'A');
        $error = $app->db->table(Config::get('tbl_prefix') . 'error');
        $app->foil->render('main::admin/system-snapshot', [
            'title' => func\_t('System Snapshot Report', 'tritan-cms'),
            'user' => (int) $user->count(),
            'error' => (int) $error->count()
        ]);
    });

    $app->before('GET|POST', '/error/(.*)', function () {
        if (!func\current_user_can('manage_settings')) {
            func\_ttcms_flash()->{'error'}(func\_t("You don't have permission to view the Error Log screen.", 'tritan-cms'), func\get_base_url() . 'admin' . '/');
            exit();
        }
    });

    $app->get('/error/', function () use($app) {
        $errors = $app->db->table(Config::get('tbl_prefix') . 'error')
                ->all();

        $app->foil->render('main::error/index', [
            'title' => func\_t('Error Logs', 'tritan-cms'),
            'errors' => $errors
                ]
        );
    });

    $app->get('/error/(\d+)/delete/', function ($id) use($app) {
        $errors = $app->db->table(Config::get('tbl_prefix') . 'error');
        $errors->begin();
        try {
            $errors->where('error_id', (int) $id)
                    ->delete();
            $errors->commit();
            func\_ttcms_flash()->{'success'}(func\_ttcms_flash()->notice(200), func\get_base_url() . 'admin/error/' . '/');
        } catch (Exception $ex) {
            $errors->rollback();
            Cascade::getLogger('error')->{'error'}($ex->getMessage());
            func\_ttcms_flash()->{'error'}($ex->getMessage());
        }

        $app->foil->render('main::error/index', [
            'title' => func\_t('Error Logs', 'tritan-cms'),
            'errors' => $errors
                ]
        );
    });

    $app->before('GET|POST', '/audit-trail/', function () {
        if (!func\current_user_can('manage_settings')) {
            func\_ttcms_flash()->{'error'}(func\_t("You don't have permission to view the Audit Trail screen.", 'tritan-cms'), func\get_base_url() . 'admin' . '/');
            exit();
        }
    });

    $app->get('/audit-trail/', function () use($app) {

        $audit = $app->db->table(Config::get('tbl_prefix') . 'activity')
                ->sortBy('created_at', 'DESC')
                ->get();

        $app->foil->render('main::error/audit', [
            'title' => func\_t('Audit Trail', 'tritan-cms'),
            'audit' => $audit
                ]
        );
    });

    /**
     * Before route check.
     */
    $app->before('GET|POST', '/flush-cache/', function () {
        if (!func\current_user_can('manage_settings')) {
            func\_ttcms_flash()->{'error'}(func\_t("You are not allowed to flush the site cache.", 'tritan-cms'), func\get_base_url() . 'admin' . '/');
            exit();
        }
    });

    $app->get('/flush-cache/', function () use($app) {
        if ($app->hook->{'get_option'}('current_site_theme') !== 'null' && $app->hook->{'get_option'}('current_site_theme') !== '' && $app->hook->{'get_option'}('current_site_theme') !== false) {
            $app->fenom->clearAllCompiles();
        }
        func\ttcms_cache_flush();
        /**
         * Action is triggered after cache is flushed and cache
         * directory is re-created.
         * 
         * @since 0.9.5
         */
        $app->hook->{'do_action'}('protect_cache_dir');
        func\ttcms_redirect($app->req->server['HTTP_REFERER']);
    });

    /**
     * If the requested page does not exist,
     * return a 404.
     */
    $app->setError(function() use($app) {
        $app->res->_format('json', 404);
    });
});
