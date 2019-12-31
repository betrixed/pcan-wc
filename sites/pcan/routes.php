<?php
return [
        'GET /' => 'GH\Home->show',
        'GET /index.php' => 'GH\Home->show',
        'GET /article/@title' => 'Pcan\Article->title',
        'GET /links' => 'GH\Home->links',
        'GET /events' => 'Pcan\EventList->index',
        'GET /cat/@catid' => 'Pcan\CatView->index',
        'GET /cat/fetch/@bid' => 'Pcan\CatView->fetch',
        'GET /cat/article/@title' => 'Pcan\CatView->article',
        'GET /cat/menu/@cap' => 'Pcan\CatView->menu',
        'GET /cat/linkery/@title' => 'Pcan\CatView->linkery',
        'GET /gallery' => 'Pcan\GalleryCtl->index',
        'GET /gallery/view/@name' => 'Pcan\GalleryCtl->view',
        'POST /login' => 'Pcan\Login->check',
        'GET /logout' => 'Pcan\Login->checkout',
        'GET /login' => 'Pcan\Login->index',
        'GET /login/forgot' => 'Pcan\Login->forgot',
        'POST /login/forgotPost' => 'Pcan\Login->forgotPost',
        'GET /login/changepwd' => 'Pcan\Login->changePwd',
        'POST /login/changePwdPost' => 'Pcan\Login->changePwdPost',
        'GET /reset-password/@code/@email' => 'Pcan\Login->resetPwd',
        'GET /dash' => 'Pcan\Dash->show',
        'GET /admin/info' => 'Pcan\Dash->info',
        'GET /admin/cat' => 'Pcan\Catadm->index',
        'GET /admin/gallery' => 'Pcan\GalleryAdm->index',
        'GET /admin/gallery/edit/@name' => 'Pcan\GalleryAdm->edit',
        'GET /admin/gallery/scan/@name' => 'Pcan\GalleryAdm->scan',
        'GET /admin/gallery/sync/@name' => 'Pcan\GalleryAdm->sync',
        'GET /admin/gallery/new' => 'Pcan\GalleryAdm->newRec',
        'POST /admin/gallery/post' => 'Pcan\GalleryAdm->post',
        'POST /admin/gallery/imageList [ajax]' => 'Pcan\GalleryAdm->imageList',
        'POST /admin/gallery/upload [ajax]' => 'Pcan\GalleryAdm->upload',
        'GET /admin/series' => 'Pcan\SeriesAdm->index',
        'GET /series/@id' => 'Pcan\SeriesCtl->view',
        'GET /admin/blog/export' => 'Pcan\BlogAdm->export',
        'POST /admin/blog/exportpost' => 'Pcan\BlogAdm->exportPost',
        'GET /admin/blog/new' => 'Pcan\BlogAdm->newRec',
        'POST /admin/blog/verify [ajax]' => 'Pcan\BlogAdm->verify',
        'GET /admin/blog' => 'Pcan\BlogAdm->index',
        'GET /admin/blog/index' => 'Pcan\BlogAdm->index',
        'GET /admin/blog/import' => 'Pcan\BlogAdm->import',
        'POST /admin/blog/importpost' => 'Pcan\BlogAdm->importPost',
        'POST /admin/blog/categorytick [ajax]' => 'Pcan\BlogAdm->catTick',
        'GET /admin/blog/edit/@bid' => 'Pcan\BlogAdm->edit',
        'POST /admin/blog/post' => 'Pcan\BlogAdm->editPost',
        'POST /admin/blog/postnew' => 'Pcan\BlogAdm->postNew',
        'POST /admin/blog/postflag' => 'Pcan\BlogAdm->postFlag',
        'POST /admin/blog/addevent' => 'Pcan\BlogAdm->addEvent',
        'POST /admin/blog/eventUpdate' => 'Pcan\BlogAdm->eventUpdate',
        'GET /contact-us' => 'Pcan\EmailForm->email',
        'GET /contact/email/new' => 'Pcan\EmailForm->email',
        'GET /contact/email/edit/@cid' => 'Pcan\EmailForm->edit',
        'POST /contact/email/post' => 'Pcan\EmailForm->post',
        'GET /admin/links/blog/@bid' => 'Pcan\LinksAdm->generate',
        'GET /admin/link' => 'Pcan\LinksAdm->index',
        'GET /admin/link/edit/@lid' => 'Pcan\LinksAdm->edit',
        'GET /admin/link/new' => 'Pcan\LinksAdm->newLink',
        'POST /admin/link/post' => 'Pcan\LinksAdm->linkPost',
        'GET /admin/linkery/new' => 'Pcan\LinkeryAdm->newRec',
        'GET /admin/linkery/edit/@lid' => 'Pcan\LinkeryAdm->edit',
        'GET /admin/linkery' => 'Pcan\LinkeryAdm->index',
        'POST /admin/linkery/post' => 'Pcan\LinkeryAdm->post',
        'POST /admin/linkery/add' => 'Pcan\LinkeryAdm->add',
        'GET /linkery/view/@name' => 'Pcan\LinkeryCtl->view',
        'GET /admin/member/new' => 'Pcan\MemberAdm->newMember',
        'GET /admin/member/list' => 'Pcan\MemberAdm->index',
        'GET /admin/member/edit/@mid' => 'Pcan\MemberAdm->edit',
        'POST /admin/member/post' => 'Pcan\MemberAdm->post',
        'POST /admin/member/empost' => 'Pcan\MemberAdm->empost',
        'POST /admin/member/donate [ajax]' => 'Pcan\MemberAdm->donate',
        'POST /admin/member/update' => 'Pcan\MemberAdm->update',
        'GET /admin/menu' => 'Pcan\MenuAdm->menuList',
        'GET /admin/menu/reset' => 'Pcan\MenuAdm->reset',
        'GET /admin/menu/list' => 'Pcan\MenuAdm->listAll',
        'GET /admin/menu/edit' => 'Pcan\MenuAdm->editTree',
        'GET /admin/menu/submenu' => 'Pcan\MenuAdm->itemNew',
        'GET /admin/menu/item/@id' => 'Pcan\MenuAdm->itemEdit',
        'POST /admin/menu/delete' => 'Pcan\MenuAdm->deleteItem',
        'POST /admin/menu/post' => 'Pcan\MenuAdm->postItem',
        'POST /admin/user/gpost' => 'Pcan\UserAdm->gpost',
        'GET /admin/user' => 'Pcan\UserAdm->index',
        'GET /admin/user/edit/@id' => 'Pcan\UserAdm->edit',
        'GET /admin/user/groups/@id' => 'Pcan\UserAdm->groups',
        'GET /id/signup' => 'GH\SignUp->signup',
        'POST /id/signupPost' => 'GH\SignUp->signupPost',
        'GET /register/@id' => 'Pcan\Register->newReg',
        'POST /register/regpost [ajax]' => 'Pcan\Register->regPost',
        'GET /admin/event/@id' => 'Pcan\EventEdit->edit',
        'POST /admin/eventpost' => 'Pcan\EventEdit->evtpost',
        'GET /reglink/@code/@id' => 'Pcan\Register->regEdit'

    ];