<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Entities\User;
use App\Route;

class AdminBaseController extends BaseController
{
    protected $template = "defaultAdmin";

    public function setLoggedInUser(User $user)
    {
        // prevent commenters to access anything other than
        // - its user update page
        // - the list of its comments
        if (
            $user->isCommenter() &&
            (
                (strpos(strtolower(Route::$controllerName), "users") !== false &&
                    strpos(strtolower(Route::$methodName), "update") === false)
                ||
                (strpos(strtolower(Route::$controllerName), "comments") !== false &&
                    strpos(strtolower(Route::$methodName), "read") === false)
            )
        )
        {
            Route::redirect("admin/users/update/$user->id");
        }

        $this->user = $user;
    }

    public function render(string $view, string $pageTitle = null, array $data = [])
    {
        parent::render("admin/$view", $pageTitle, $data);
    }
}
