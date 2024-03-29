<?php

use Slim\App as SlimApp;

use App\Action\Admin;
use App\Action\{HomeAction, PingAction, PostAction, ProjectListAction, TagAction, TagListAction};
use Slim\Routing\RouteCollectorProxy;

return static function (SlimApp $app)
{
    $app->group('/admin', function (RouteCollectorProxy $group) {
        $group->get('', Admin\AdminAction::class)->setName('admin:home');
        $group->get('/post', Admin\PostListAction::class)->setName('admin:post_list');
        $group->get('/post/edit/{post-id}', Admin\PostEditAction::class)->setName('admin:post_edit');
        $group->get('/post/new', Admin\PostNewAction::class)->setName('admin:post_new');
        $group->post('/post/save', Admin\PostSaveAction::class)->setName('admin:post_save');
        $group->post('/post/trash', Admin\PostTrashAction::class)->setName('admin:post_trash');
        $group->get('/tag', Admin\TagListAction::class)->setName('admin:tag_list');
    });

    $app->get('/tag/{tag-slug}', TagAction::class)->setName('tag');
    $app->get('/tag', TagListAction::class)->setName('tag_list');

    $app->get('/projects', ProjectListAction::class)->setName('projects');

    $app->get('/ping', PingAction::class);

    $app->get('/', HomeAction::class)->setName('home');

    $app->get('/{post-slug}', PostAction::class)->setName('post');
};
