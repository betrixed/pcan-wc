<?php
use Phalcon\Mvc\Router\Group as RouterGroup;

function doChimpRoutes($router) {

    $modname = "chimp";
    $prefix = $modname;
    
    $router->add("/msg/index", [
        "module" => $modname,
        "controller" => "msg",
        "action" => "index",
    ]); 
        
    $errors = new RouterGroup([
        "module" => $modname,
        "controller" => "errors"
    ]);
    $errors->setPrefix("/$prefix/errors");
    $errors->add("/index", ["action" => "index"]);
    $errors->add("/show404", ["action" => "show404"]);
    $errors->add("/show500", ["action" => "show500"]);
    $router->mount($errors);
    
    $group = new RouterGroup([
        "module" => $modname,
        "controller" => "mailchimp",
    ]);
    $group->setPrefix("/$prefix/mail");
    $group->add("/:action", ["action" => 1]);
    $group->add("/list/{editId}", ["action" => "list"]);
    $router->mount($group);

    $group = new RouterGroup([
        "module" => $modname,
        "controller" => "account",
    ]);
    $group->setPrefix("/$prefix/account");
    $group->add("/:action", ["action" => 1]);
    $router->mount($group);
    
    $group = new RouterGroup([
        "module" => $modname,
        "controller" => "member",
    ]);
    $group->setPrefix("/$prefix/member");
    $group->add("/:action", ["action" => 1]);
    $group->add("/edit/{id}", ["action" => "edit"]);
    $router->mount($group);


    $group = new RouterGroup([
        "module" => $modname,
        "controller" => "api",
    ]);
    $group->setPrefix("/$prefix/api");
    $group->add("/:action", ["action" => 1]);
    $router->mount($group);
}

doChimpRoutes($router);
