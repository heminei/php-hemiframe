<?php

namespace Examples;

use HemiFrame\Lib\DependencyInjection\Attributes\Inject;
use HemiFrame\Lib\Routing\Attributes\Route;

class Test
{
    #[Inject]
    protected Test2 $testPropertyInject;

    #[Route(url: '/comments', key: 'comments')]
    public function comments()
    {
        echo 'comments';
    }

    #[Route(url: '/comments/{{commentId}}', key: 'comments.details')]
    public function commentDetails()
    {
        echo 'comments';
    }

    #[Route(url: '/comments/check', key: 'comments.check', priority: 2)]
    #[Route(url: '/comments/check2', key: 'comments.check2', priority: 2)]
    public function commentCheck()
    {
        echo 'comments';
    }

    /**
     * @Route({"url": "/articles", "key": "articles", "priority": 222})
     */
    public function articles()
    {
        echo 'Articles';
    }
}
