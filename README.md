# http git server base on php laravel 
> mysql http base authorized server

## install guide
0. install `composer install six/gitserver`
0. initialize table `php artisan git:user init`
0. add a user `php artisan git:user add tata@dr.h:123`
0. add a repos `php artisan git:server app1`
0. clone it from you website `git clone http://you-hosts/repos/app1.git`
    0. username is tata@dr.h
    0. password is 123
    0. the user can `clone` and `pull` and `push`...

## configurations
> default configuration see src/config/git.php
> if you need change it move it to you config path
