<?php
    require '../private/bootstrap.php';
    $f3 = init_app(__DIR__, 'pcan');
    try {
        $f3->run();
    } catch (Exception $x) {
        echo $x->getMessage();
    }