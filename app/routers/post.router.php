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

    foreach (func\get_all_post_types() as $post_type) :
        /**
         * Before route checks to make sure the logged in user
         * has permission to create a new post.
         */
        $app->before('GET|POST', '/' . func\_escape($post_type['posttype_slug']) . '/', function() {
            if (!func\current_user_can('manage_posts')) {
                func\_ttcms_flash()->{'error'}(func\_t('You do not have permission to manage posts.', 'tritan-cms'), func\get_base_url() . 'admin' . '/');
                exit();
            }
        });
        /**
         * Show a list of all of our posts in the backend.
         */
        $app->get('/' . func\_escape($post_type['posttype_slug']) . '/', function () use($app, $post_type) {
            $posts = $app->db->table(Config::get('tbl_prefix') . 'post')
                    ->where('post_type.post_posttype', func\_escape($post_type['posttype_slug']))
                    ->sortBy('post_created', 'desc')
                    ->get();

            $app->foil->render('main::admin/post/index', [
                'title' => func\_escape($post_type['posttype_title']),
                'posts' => $posts,
                'posttype' => func\_escape($post_type['posttype_slug'])
                    ]
            );
        });

        /**
         * Before route checks to make sure the logged in user
         * has permission to create a new post.
         */
        $app->before('GET|POST', '/' . func\_escape($post_type['posttype_slug']) . '/create/', function() {
            if (!func\current_user_can('create_posts')) {
                func\_ttcms_flash()->{'error'}(func\_t('You do not have permission to create posts.', 'tritan-cms'), func\get_base_url() . 'admin' . '/');
                exit();
            }
        });
        /**
         * Shows the add new post form.
         */
        $app->match('GET|POST', '/' . func\_escape($post_type['posttype_slug']) . '/create/', function () use($app, $post_type) {

            if ($app->req->isPost()) {
                $post = $app->db->table(Config::get('tbl_prefix') . 'post');
                $post->begin();
                try {
                    $post_id = func\auto_increment(Config::get('tbl_prefix') . 'post', 'post_id');
                    $posttype = func\get_posttype_by('posttype_slug', $app->req->post['post_posttype']);
                    $post_status = $app->req->post['post_status'];
                    $post_slug = $app->req->post['post_slug'] != '' ? $app->req->post['post_slug'] : func\ttcms_slugify($app->req->post['post_title']);
                    $relative_url = func\_escape($post_type['posttype_slug']) . '/' . $post_slug . '/';
                    $featured_image = func\ttcms_optimized_image_upload($app->req->post['post_featured_image']);
                    $post->insert([
                        'post_id' => (int) $post_id,
                        'post_title' => (string) $app->req->post['post_title'],
                        'post_slug' => (string) $post_slug,
                        'post_content' => func\if_null($app->req->post['post_content']),
                        'post_author' => (int) $app->req->post['post_author'],
                        'post_type' => [
                            'posttype_id' => (int) func\_escape($posttype['posttype_id']),
                            'post_posttype' => (string) $app->req->post['post_posttype']
                        ],
                        'post_attributes' => [
                            'parent' => [
                                'parent_id' => func\if_null(func\get_post_id($app->req->post['post_parent'])),
                                'post_parent' => func\if_null($app->req->post['post_parent'])
                            ],
                            'post_sidebar' => func\if_null($app->req->post['post_sidebar']),
                            'post_show_in_menu' => func\if_null($app->req->post['post_show_in_menu']),
                            'post_show_in_search' => func\if_null($app->req->post['post_show_in_search'])
                        ],
                        'post_relative_url' => (string) $relative_url,
                        'post_featured_image' => func\if_null($featured_image),
                        'post_status' => (string) $post_status,
                        'post_created' => (string) $app->req->post['post_created']
                    ]);
                    $post->commit();
                    $lastId = $post_id;
                    /**
                     * Action hook triggered after the post is created.
                     * 
                     * @since 0.9
                     * @param int $lastId Post ID.
                     */
                    $app->hook->{'do_action'}('create_post', $lastId);
                    /**
                     * Action hook triggered depending on page status.
                     * 
                     * @since 0.9
                     * @param string $page_status Posted status of page.
                     * @param int $lastId Post ID.
                     */
                    $app->hook->{'do_action'}("{$post_type['posttype_slug']}_{$post_status}_create", $lastId);
                    func\_ttcms_flash()->{'success'}(func\_ttcms_flash()->notice(200), func\get_base_url() . 'admin/' . $app->req->post['post_posttype'] . '/' . $lastId . '/');
                } catch (Exception $ex) {
                    $post->rollback();
                    Cascade::getLogger('error')->{'error'}(sprintf('SQLSTATE[%s]: %s', $ex->getCode(), $ex->getMessage()));
                    func\_ttcms_flash()->{'error'}(func\_ttcms_flash()->notice(409));
                }
            }

            $post_count = $app->db->table(Config::get('tbl_prefix') . 'post')->count();

            $app->foil->render('main::admin/post/create', [
                'title' => func\_t('Create', 'tritan-cms') . ' ' . func\_escape($post_type['posttype_title']),
                'posttype_title' => func\_escape($post_type['posttype_title']),
                'posttype' => func\_escape($post_type['posttype_slug']),
                'post_count' => (int) $post_count
                    ]
            );
        });

        /**
         * Before route checks to make sure the logged in
         * user has the permission to edit a post.
         */
        $app->before('GET|POST', '/' . func\_escape($post_type['posttype_slug']) . '/(\d+)/', function() {
            if (!func\current_user_can('update_posts')) {
                func\_ttcms_flash()->{'error'}(func\_t('You do not have permission to update posts.', 'tritan-cms'), func\get_base_url() . 'admin' . '/');
                exit();
            }
        });

        /**
         * Shows the edit form with the requested id.
         */
        $app->match('GET|POST', '/' . func\_escape($post_type['posttype_slug']) . '/(\d+)/', function ($id) use($app, $post_type) {

            if ($app->req->isPost()) {
                $post = $app->db->table(Config::get('tbl_prefix') . 'post');
                $post->begin();
                try {
                    $posttype = func\get_posttype_by('posttype_slug', $app->req->post['post_posttype']);
                    $post_status = $app->req->post['post_status'];
                    $post_slug = $app->req->post['post_slug'] != '' ? $app->req->post['post_slug'] : func\ttcms_slugify($app->req->post['post_title']);
                    /**
                     * Can be used to filter the relative url.
                     * 
                     * @since 0.9
                     */
                    $url_filter = $app->hook->{'apply_filter'}('relative_url', func\_escape($post_type['posttype_slug']) . '/', $post_type);
                    $relative_url = $url_filter . $post_slug . '/';
                    $featured_image = func\ttcms_optimized_image_upload($app->req->post['post_featured_image']);
                    $post->where('post_id', (int) $id)->update([
                        'post_title' => (string) $app->req->post['post_title'],
                        'post_slug' => (string) $post_slug,
                        'post_content' => func\if_null($app->req->post['post_content']),
                        'post_author' => (int) $app->req->post['post_author'],
                        'post_type' => [
                            'posttype_id' => (int) func\_escape($posttype['posttype_id']),
                            'post_posttype' => (string) $app->req->post['post_posttype']
                        ],
                        'post_attributes' => [
                            'parent' => [
                                'parent_id' => func\if_null(func\get_post_id($app->req->post['post_parent'])),
                                'post_parent' => func\if_null($app->req->post['post_parent'])
                            ],
                            'post_sidebar' => func\if_null($app->req->post['post_sidebar']),
                            'post_show_in_menu' => func\if_null($app->req->post['post_show_in_menu']),
                            'post_show_in_search' => func\if_null($app->req->post['post_show_in_search'])
                        ],
                        'post_relative_url' => (string) $relative_url,
                        'post_featured_image' => func\if_null($featured_image),
                        'post_status' => (string) $post_status,
                        'post_created' => (string) $app->req->post['post_created'],
                        'post_modified' => (string) Jenssegers\Date\Date::now()
                    ]);
                    $post->commit();

                    $parent = $app->db->table(Config::get('tbl_prefix') . 'post');
                    $parent->where('post_attributes.parent.parent_id', (int) $id)
                            ->update([
                                'post_attributes.parent.post_parent' => (string) $post_slug
                    ]);
                    func\ttcms_cache_delete((int) $id, 'post');
                    /**
                     * Action hook triggered after the post is updated.
                     * 
                     * @since 0.9
                     * @param int $id Post ID.
                     */
                    $app->hook->{'do_action'}('update_post', (int) $id);
                    /**
                     * Action hook triggered depending on post status.
                     * 
                     * @since 0.9
                     * @param string $post_status Posted status of post.
                     * @param int $id Post ID.
                     */
                    $app->hook->{'do_action'}("{$post_type['posttype_slug']}_{$post_status}_update", (int) $id);
                    func\_ttcms_flash()->{'success'}(func\_ttcms_flash()->notice(200), func\get_base_url() . 'admin/' . (string) $app->req->post['post_posttype'] . '/' . (int) $id . '/');
                } catch (Exception $ex) {
                    $post->rollback();
                    Cascade::getLogger('error')->{'error'}(sprintf('SQLSTATE[%s]: %s', $ex->getCode(), $ex->getMessage()));
                    func\_ttcms_flash()->{'error'}(func\_ttcms_flash()->notice(409));
                }
            }

            $q = $app->db->table(Config::get('tbl_prefix') . 'post');
            $cache = func\ttcms_cache_get((int) $id, 'post');
            if (empty($cache)) {
                $cache = $q->where('post_id', (int) $id)
                        ->where('post_type.post_posttype', func\_escape((string) $post_type['posttype_slug']))
                        ->first();
                func\ttcms_cache_add((int) $id, $cache, 'post');
            }

            /**
             * If the category doesn't exist, then it
             * is false and a 404 page should be displayed.
             */
            if ($cache === false) {
                $app->res->_format('json', 404);
                exit();
            }
            /**
             * If the query is legit, but the
             * the category does not exist, then a 404
             * page should be displayed
             */ elseif (empty($cache) === true) {
                $app->res->_format('json', 404);
                exit();
            }
            /**
             * If data is zero, 404 not found.
             */ elseif (count($cache) <= 0) {
                $app->res->_format('json', 404);
                exit();
            }
            /**
             * If we get to this point, then all is well
             * and it is ok to process the query and print
             * the results in a jhtml format.
             */ else {

                $app->foil->render('main::admin/post/update-post', [
                    'title' => func\_t('Update', 'tritan-cms') . ' ' . func\_escape($post_type['posttype_title']),
                    'posttype_title' => func\_escape($post_type['posttype_title']),
                    'posttype' => func\_escape($post_type['posttype_slug']),
                    'post' => $cache
                        ]
                );
            }
        });

        /**
         * Before route checks to make sure the logged in user
         * is allowed to delete posts.
         */
        $app->before('GET|POST', '/' . func\_escape($post_type['posttype_slug']) . '/(\d+)/remove-featured-image/', function() {
            if (!func\current_user_can('update_posts')) {
                func\_ttcms_flash()->{'error'}(func\_t('You do not have permission to update posts.', 'tritan-cms'), func\get_base_url() . 'admin' . '/');
                exit();
            }
        });

        $app->get('/' . func\_escape($post_type['posttype_slug']) . '/(\d+)/remove-featured-image/', function($id) use($app) {
            $post = $app->db->table(Config::get('tbl_prefix') . 'post');
            $post->begin();
            try {
                $post->where('post_id', (int) $id)->update([
                    'post_featured_image' => null
                ]);
                $post->commit();
                func\ttcms_cache_delete((int) $id, 'post');
                func\_ttcms_flash()->{'success'}(func\_ttcms_flash()->notice(200), $app->req->server['HTTP_REFERER']);
            } catch (Exception $ex) {
                $post->rollback();
                Cascade::getLogger('error')->{'error'}(sprintf('SQLSTATE[%s]: %s', $ex->getCode(), $ex->getMessage()));
                func\_ttcms_flash()->{'error'}($ex->getMessage(), $app->req->server['HTTP_REFERER']);
            }
        });

        /**
         * Before route checks to make sure the logged in user
         * is allowed to delete posts.
         */
        $app->before('GET', '/' . func\_escape($post_type['posttype_slug']) . '/(\d+)/d/', function() {
            if (!func\current_user_can('delete_posts')) {
                func\_ttcms_flash()->{'error'}(func\_t('You do not have permission to delete posts.', 'tritan-cms'), func\get_base_url() . 'admin' . '/');
                exit();
            }
        });

        $app->get('/' . func\_escape($post_type['posttype_slug']) . '/(\d+)/d/', function($id) use($app, $post_type) {
            $post = $app->db->table(Config::get('tbl_prefix') . 'post');
            $post->begin();
            try {
                $post->where('post_id', (int) $id)
                        ->delete();
                $post->commit();
                func\_ttcms_flash()->{'success'}(func\_ttcms_flash()->notice(200), func\get_base_url() . 'admin/' . (string) func\_escape($post_type['posttype_slug']) . '/');
            } catch (Exception $ex) {
                $post->rollback();
                Cascade::getLogger('error')->{'error'}(sprintf('SQLSTATE[%s]: %s', $ex->getCode(), $ex->getMessage()));
                func\_ttcms_flash()->{'error'}($ex->getMessage(), func\get_base_url() . 'admin/' . (string) func\_escape($post_type['posttype_slug']) . '/');
            }
        });
    endforeach;

    /**
     * Before route checks to make sure the logged in user
     * is allowed to delete posts.
     */
    $app->before('GET|POST', '/post-type/', function() {
        if (!func\current_user_can('manage_posts')) {
            func\_ttcms_flash()->{'error'}(func\_t('You do not have permission to manage posts or post types.', 'tritan-cms'), func\get_base_url() . 'admin' . '/');
            exit();
        }
    });

    $app->match('GET|POST', '/post-type/', function () use($app) {

        if ($app->req->isPost()) {
            $posttype = $app->db->table(Config::get('tbl_prefix') . 'posttype');
            $posttype->begin();
            try {
                $posttype_id = func\auto_increment(Config::get('tbl_prefix') . 'posttype', 'posttype_id');
                $posttype_slug = $app->req->post['posttype_slug'] != '' ? $app->req->post['posttype_slug'] : func\ttcms_slugify((string) $app->req->post['posttype_title'], 'posttype');
                $posttype->insert([
                    'posttype_id' => (int) $posttype_id,
                    'posttype_title' => func\if_null($app->req->post['posttype_title']),
                    'posttype_slug' => (string) $posttype_slug,
                    'posttype_description' => func\if_null($app->req->post['posttype_description'])
                ]);
                $posttype->commit();
                $lastId = $posttype_id;
                func\ttcms_cache_delete('posttype', 'posttype');
                /**
                 * Action hook triggered after the posttype is created.
                 * 
                 * @since 0.9
                 * @param int $lastId posttype ID.
                 */
                $app->hook->{'do_action'}('create_posttype', (int) $lastId);
                func\_ttcms_flash()->{'success'}(func\_ttcms_flash()->notice(200), $app->req->server['HTTP_REFERER']);
            } catch (Exception $ex) {
                $posttype->rollback();
                Cascade::getLogger('error')->{'error'}(sprintf('SQLSTATE[%s]: %s', $ex->getCode(), $ex->getMessage()));
                func\_ttcms_flash()->{'error'}(func\_ttcms_flash()->notice(409));
            }
        }

        $posttypes = $app->db->table(Config::get('tbl_prefix') . 'posttype')->all();

        $app->foil->render('main::admin/post/posttype', [
            'title' => func\_t('Post Types', 'tritan-cms'),
            'posttypes' => $posttypes
                ]
        );
    });

    /**
     * Before route checks to make sure the logged in
     * user has the permission to edit a posttype.
     */
    $app->before('GET|POST', '/post-type/(\d+)/', function() {
        if (!func\current_user_can('update_posts')) {
            func\_ttcms_flash()->{'error'}(func\_t('You do not have permission to update posts or post types.', 'tritan-cms'), func\get_base_url() . 'admin' . '/');
            exit();
        }
    });

    $app->match('GET|POST', '/post-type/(\d+)/', function ($id) use($app) {
        $current_pt = $app->db->table(Config::get('tbl_prefix') . 'posttype')
                ->where('posttype_id', (int) $id)
                ->first();

        if ($app->req->isPost()) {
            $posttype = $app->db->table(Config::get('tbl_prefix') . 'posttype');
            $posttype->begin();
            try {
                $posttype_slug = $app->req->post['posttype_slug'] != '' ? $app->req->post['posttype_slug'] : func\ttcms_slugify((string) $app->req->post['posttype_title'], 'posttype');
                $posttype->where('posttype_id', (int) $id)->update([
                    'posttype_title' => (string) $app->req->post['posttype_title'],
                    'posttype_slug' => (string) $posttype_slug,
                    'posttype_description' => func\if_null($app->req->post['posttype_description'])
                ]);
                $posttype->commit();

                /**
                 * Update all post's relative url if the the posted data
                 * for posttype does not equal to the current posttype.
                 * 
                 * @since 0.9.6
                 */
                if ($current_pt['posttype_slug'] != (string) $posttype_slug) {
                    func\update_post_relative_url_posttype($id, $current_pt['posttype_slug'], (string) $posttype_slug);
                }
                func\ttcms_cache_delete((int) $id, 'posttype');
                /**
                 * Action hook triggered after the posttype is updated.
                 * 
                 * @since 0.9
                 * @param int $id Post Type ID.
                 */
                $app->hook->{'do_action'}('update_posttype', (int) $id);
                func\_ttcms_flash()->{'success'}(func\_ttcms_flash()->notice(200), $app->req->server['HTTP_REFERER']);
            } catch (Exception $ex) {
                $posttype->rollback();
                Cascade::getLogger('error')->{'error'}(sprintf('SQLSTATE[%s]: %s', $ex->getCode(), $ex->getMessage()));
                func\_ttcms_flash()->{'error'}(func\_ttcms_flash()->notice(409));
            }
        }

        $q = $app->db->table(Config::get('tbl_prefix') . 'posttype')->where('posttype_id', (int) $id)->first();
        $posttypes = $app->db->table(Config::get('tbl_prefix') . 'posttype')->all();

        /**
         * If the posttype doesn't exist, then it
         * is false and a 404 page should be displayed.
         */
        if ($q === false) {
            $app->res->_format('json', 404);
            exit();
        }
        /**
         * If the query is legit, but the
         * the posttype does not exist, then a 404
         * page should be displayed
         */ elseif (empty($q) === true) {
            $app->res->_format('json', 404);
            exit();
        }
        /**
         * If we get to this point, then all is well
         * and it is ok to process the query and print
         * the results in a jhtml format.
         */ else {

            $app->foil->render('main::admin/post/update-posttype', [
                'title' => func\_t('Update Post Type', 'tritan-cms'),
                'posttype' => $q,
                'posttypes' => $posttypes
                    ]
            );
        }
    });

    /**
     * Before route checks to make sure the logged in user
     * us allowed to delete posttypes.
     */
    $app->before('GET|POST', '/post-type/(\d+)/d/', function() {
        if (!func\current_user_can('delete_posts')) {
            func\_ttcms_flash()->{'error'}(func\_t('You do not have permission to delete posts or post types.', 'tritan-cms'), func\get_base_url() . 'admin' . '/');
            exit();
        }
    });

    $app->get('/post-type/(\d+)/d/', function($id) use($app) {
        $posttype = $app->db->table(Config::get('tbl_prefix') . 'posttype');
        $posttype->begin();
        try {
            $posttype->where('posttype_id', (int) $id)
                    ->delete();
            $posttype->commit();

            $post = $app->db->table(Config::get('tbl_prefix') . 'post');
            $post->begin();
            try {
                $post->where('post_type.posttype_id', (int) $id)
                        ->delete();
                $post->commit();
                func\ttcms_cache_delete('posttype', 'posttype');
                func\ttcms_cache_delete('post', 'post');
            } catch (Exception $ex) {
                $posttype->rollback();
                Cascade::getLogger('error')->{'error'}(sprintf('SQLSTATE[%s]: %s', $ex->getCode(), $ex->getMessage()));
            }

            func\_ttcms_flash()->{'success'}(func\_ttcms_flash()->notice(200), func\get_base_url() . 'admin/post-type/');
        } catch (Exception $ex) {
            $posttype->rollback();
            Cascade::getLogger('error')->{'error'}(sprintf('SQLSTATE[%s]: %s', $ex->getCode(), $ex->getMessage()));
            func\_ttcms_flash()->{'error'}($ex->getMessage(), func\get_base_url() . 'admin/post-type/');
        }
    });

    /**
     * If the requested page does not exist,
     * return a 404.
     */
    $app->setError(function() use($app) {
        $app->res->_format('json', 404);
    });
});
