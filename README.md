# malam-menu

## Sample

config:

    return array(
        'admin' => array(
            // DASHBOARD
            'dashboard'     => array(
                'title'     => __('Dashboard'),
                'url'       => '/admin/dashboard',          // URI
            ),
            // POST
            'post'          => array(
                'title'     => __('Manage posts'),
                'url'       => 'admin-post',                // route name
            ),
            // POST
            'post-create'   => array(
                'title'     => __('Create new post'),
                'url'       => 'admin-post',                // route name
                'params'    => array('action' => 'create'), // with params
            ),
        ),
    );

Call (with menu config):

    Menu::factory('admin')->render();

Or (from array):

    $array = Kohana::$config->load('menu.admin');
    Menu::factory($array)->render('my-template-file');

Set current page:

    Menu::factory('admin')->set_current('post/create')->render();


Inspired by git://github.com/coreyworrell/Menu.git

