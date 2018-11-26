
# SLIM-3 Boilerplate MOD

PHP boilerplate,for a fast API prototyping based on Slim 3 Framework, for start projects with Eloquent ORM, Validation, Auth (JWT), Repositories and Transformers ready. Modified from [damianopetrungaro/slim-boilerplate](https://github.com/damianopetrungaro/slim-boilerplate)

  

# Installation and Setup

You need [composer](http://getcomposer.org) and [git](https://git-scm.com/) for download and install the repository.

  

```shell

$ git clone https://github.com/damianopetrungaro/slim-boilerplate.git

$ php composer.phar install

```

Edit the `.env.example` to `.env` and override it with your credentials.

```shell

$ php vendor/bin/phinx migrate

```

#### Container

All the object into the container are setted into the `bootstrap/container.php` file

  

#### Info

The routes are into the `app/Routes`, you can add all the .php file you want, each file will be read by slim for catch all the routes.

  

The exception handler is overriden into the `bootstrap/container.php` file.