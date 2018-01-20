<?php

namespace App\Entities;

use App\Entities\Repositories\Category as CategoryRepo;
use App\Entities\Repositories\Post as PostRepo;
use App\Router;

class Category extends Entity
{
    public $slug = "";

    /**
     * @var CategoryRepo
     */
    protected $categoryRepo;

    /**
     * @var PostRepo
     */
    protected $postRepo;

    public function __construct(Router $router, CategoryRepo $categoryRepo, PostRepo $postRepo)
    {
        parent::__construct($router);
        $this->categoryRepo = $categoryRepo;
        $this->postRepo = $postRepo;
    }

    /**
     * @return PostRepo[]|false
     */
    public function getPosts()
    {
        return $this->postRepo->getAll(["category_id" => $this->id]);
    }

    public function getLink(string $routeName = "category")
    {
        return parent::getLink($routeName);
    }
}
