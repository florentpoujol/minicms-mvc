<?php

namespace App\Controllers\Admin;

use App\Entities\Page;
use App\Messages;
use App\Router;
use App\Validator;

class Pages extends AdminBaseController
{
    public function getRead(int $pageNumber = 1)
    {
        $allRows = Page::getAll(["pageNumber" => $pageNumber]);

        $data = [
            "allRows" => $allRows,
            "pagination" => [
                "pageNumber" => $pageNumber,
                "itemsCount" => Page::countAll(),
                "queryString" => Router::getQueryString("admin/pages/read")
            ]
        ];
        $this->render("pages.read", "admin.page.readtitle", $data);
    }

    public function getCreate()
    {
        $this->render("pages.update", "admin.page.create", ["action" => "create"]);
    }

    public function postCreate()
    {
        $post = Validator::sanitizePost([
            "id" => "int",
            "slug" => "string",
            "title" => "string",
            "content" => "string",
            "parent_page_id" => "int",
            "published" => "checkbox",
            "allow_comments" => "checkbox"
        ]);

        if (Validator::csrf("pagecreate")) {
            if (Validator::page($post)) {
                $page = Page::create($post);

                if (is_object($page)) {
                    Messages::addSuccess("page.created");
                    Router::redirect("admin/pages/update/$page->id");
                } else {
                    Messages::addError("page.create");
                }
            }
        } else {
            Messages::addError("csrffail");
        }

        $data = [
            "action" => "create",
            "post" => $post
        ];
        $this->render("pages.update", "admin.page.create", $data);
    }

    public function getUpdate(int $pageId)
    {
        $page = Page::get($pageId);
        if ($page === false) {
            Messages::addError("page.unknown");
            Router::redirect("admin/pages/read");
        }

        $data = [
            "action" => "update",
            "post" => $page->toArray()
        ];
        $this->render("pages.update", "admin.page.updatetitle", $data);
    }

    public function postUpdate()
    {
        $post = Validator::sanitizePost([
            "id" => "int",
            "slug" => "string",
            "title" => "string",
            "content" => "string",
            "parent_page_id" => "int",
            "published" => "checkbox",
            "allow_comments" => "checkbox"
        ]);

        if (Validator::csrf("pageupdate")) {

            if (Validator::page($post)) {
                $page = Page::get($post["id"]);

                if (is_object($page)) {
                    if ($page->update($post)) {
                        Messages::addSuccess("page.updated");
                        Router::redirect("admin/pages/update/$page->id");
                    } else {
                        Messages::addError("db.pageupdated");
                    }
                } else {
                    Messages::addError("page.unknown");
                }
            }
        } else {
            Messages::addError("csrffail");
        }

        $post["creation_datetime"] = Page::get($post["id"])->creation_datetime;

        $data = [
            "action" => "update",
            "post" => $post
        ];
        $this->render("pages.update", "admin.page.updatetitle", $data);
    }

    public function postDelete()
    {
        $id = (int)$_POST["id"];
        if (Validator::csrf("pagedelete$id")) {
            $page = Page::get($id);
            if (is_object($page)) {
                if ($page->delete()) {
                    Messages::addSuccess("page.deleted");
                } else {
                    Messages::addError("page.deleting");
                }
            } else {
                Messages::addError("page.unknown");
            }
        } else {
            Messages::addError("csrffail");
        }

        Router::redirect("admin/pages/read");
    }
}
