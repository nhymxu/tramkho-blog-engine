<?php

use Slim\App as SlimApp;

use App\Action\{HomeAction, TagAction, TagListAction, PostAction};

return static function (SlimApp $app) {
    $app->get('/tag/{tag-slug}', TagAction::class)->setName('tag');
    $app->get('/tag', TagListAction::class)->setName('tag_list');
    $app->get('/{post-slug}', PostAction::class)->setName('post');
    $app->get('/', HomeAction::class)->setName('home');
};
