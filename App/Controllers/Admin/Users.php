<?php

namespace App\Controllers\Admin;

use App\Entities\User;
use App\Messages;
use App\Route;
use App\Validator;

class Users extends AdminBaseController
{
    public function getRead(int $pageNumber = 1)
    {
        $allRows = User::getAll(["pageNumber" => $pageNumber]);

        $data = [
            "allRows" => $allRows,
            "pagination" => [
                "pageNumber" => $pageNumber,
                "itemsCount" => User::countAll(),
                "queryString" => Route::buildQueryString("admin/users/read/")
            ]
        ];
        $this->render("users.read", "users.pagetitle", $data);
    }

    public function getCreate()
    {
        if ($this->user->isWriter()) {
            Route::redirect("admin/users/read");
        }

        $this->render("users.update", "users.createnewuser", ["action" => "create"]);
    }

    public function postCreate()
    {
        if ($this->user->isWriter()) {
            Route::redirect("admin/users/read");
        }

        $post = Validator::sanitizePost([
            "name" => "string",
            "email" => "string",
            "password" => "string",
            "password_confirmation" => "string",
            "role" => "string"
        ]);
        if (Validator::csrf("usercreate")) {

            if (Validator::user($post)) {
                $user = User::create($post);

                if (is_object($user)) {
                    Messages::addSuccess("user.created");
                    Route::redirect("admin/users/update/$user->id");
                } else {
                    Messages::addError("db.createuser");
                }
            }
        } else {
            Messages::addError("csrffail");
        }

        $data = [
            "action" => "create",
            "post" => $post
        ];
        $this->render("users.update", "users.createnewuser", $data);
    }

    public function getUpdate(int $userId)
    {
        if (! $this->user->isAdmin() && $userId !== $this->user->id) {
            Route::redirect("admin/users/update/$this->user->id");
        }

        $user = User::get($userId);
        if ($user === false) {
            Messages::addError("user.unknown");
            Route::redirect("admin/users");
        }

        $data = [
            "action" => "update",
            "post" => $user->toArray()
        ];
        $this->render("users.update", "users.updateuser", $data);
    }

    public function postUpdate()
    {
        $schema = [
            "id" => "int",
            "name" => "string",
            "email" => "string",
            "password" => "string",
            "password_confirmation" => "string",
            "role" => "string"
        ];

        if ($this->user->isAdmin()) {
            $schema = array_merge($schema, [
                "email_token" => "string",
                "password_token" => "string",
                "password_change_time" => "int",
                "is_blocked" => "checkbox",
            ]);
        }
        $post = Validator::sanitizePost($schema);
        if (Validator::csrf("userupdate")) {

            if (! $this->user->isAdmin()) {
                $post["id"] = $this->user->id;
                $post["role"] = $this->user->role;
            }

            if (Validator::user($post)) {
                $user = User::get($post["id"]);

                if (is_object($user)) {
                    if ($user->update($post)) {
                        Messages::addSuccess("user.updated");
                        Route::redirect("admin/users/update/$user->id");
                    } else {
                        Messages::addError("db.userupdated");
                    }
                } else {
                    Messages::addError("user.unknown");
                }
            }
        } else {
            Messages::addError("csrffail");
        }

        $post["creation_datetime"] = User::get($post["id"])->creation_datetime;

        $data = [
            "action" => "update",
            "post" => $post
        ];
        $this->render("users.update", "users.createnewuser", $data);
    }

    public function postDelete()
    {
        if ($this->user->isAdmin()) {

            $id = (int)$_POST["id"];
            if ($this->user->id !== $id) {

                if (Validator::csrf("userdelete$id")) {

                    $user = User::get($id);
                    if (is_object($user)) {
                        if ($user->deleteByAdmin($this->user->id)) {
                            Messages::addSuccess("user.deleted");
                        } else {
                            Messages::addError("user.deleting");
                        }
                    } else {
                        Messages::addError("user.unknown");
                    }
                } else {
                    Messages::addError("csrffail");
                }
            } else {
                Messages::addError("user.cantdeleteownuser");
            }
        }

        Route::redirect("admin/users/read");
    }
}
