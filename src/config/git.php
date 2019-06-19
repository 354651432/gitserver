<?php
/**
 * default six/six configuration
 */
return [
    // false disalbed all functions
    "enable" => true,

    // repos data dir path
    "basepath" => storage_path("repos"),

    // base url prefix
    "prefix" => "repos",

    // controllers namespace if extends change it
    "namespace" => '\Six\GitServer',

    // if no auth change it to empty string
    "middleware" => "auth.basic:git",

    // auth mysql table name
    "user_table" => "git_user",
];
