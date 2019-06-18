<?php

Route::group([
    "prefix" => config("git.prefix", "repos"),
    "namespace" => config("git.namespace", '\Six\GitServer'),
//    "middleware" => "auth.basic:git",
], function () {

//    Route::post('/create', 'GitController@init');

    Route::get('/{name}.git/info/refs', 'GitController@getInfoRefs');

    Route::post("/{name}.git/git-{service}", 'GitController@command');
});
