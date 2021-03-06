<?php

use TriTan\Functions as func;

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');

/**
 * Before router checks to make sure the logged in user
 * us allowed to access admin.
 */
$app->before('GET|POST', '/login', function() use($app) {
    $app->hook->{'do_action'}('before_router_login');
});

$app->group('/login', function() use ($app) {
    /**
     * Before route check.
     */
    $app->before('GET|POST', '/', function() use($app) {
        if (func\is_user_logged_in()) {
            $redirect_to = ($app->req->get['redirect_to'] != null ? $app->req->get['redirect_to'] : func\get_base_url() . 'admin' . '/');
            func\ttcms_redirect($redirect_to);
        }

        /**
         * Fires before a user has logged in.
         *
         * @since 0.9
         */
        $app->hook->{'do_action'}('ttcms_login');
    });

    $app->match('GET|POST', '/', function () use($app) {

        if ($app->req->isPost()) {
            /**
             * Filters where the admin should be redirected after successful login.
             */
            $login_link = $app->hook->{'apply_filter'}('admin_login_redirect', func\get_base_url() . 'admin' . '/');
            /**
             * This function is documented in app/functions/auth-function.php.
             * 
             * @since 0.9
             */
            func\ttcms_authenticate_user($app->req->post['user_login'], $app->req->post['user_pass'], $app->req->post['rememberme']);

            func\ttcms_redirect($login_link);
        }

        $app->foil->render('main::login/index', [
            'title' => func\_t('Login', 'tritan-cms')
                ]
        );
    });
});
