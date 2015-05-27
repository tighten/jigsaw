# Jigsaw

Simple static sites with Laravel's [Blade](http://laravel.com/docs/5.0/templates).

### Getting Started

1. Install via Composer:

    `$ composer global require jigsaw/jigsaw:dev-master`

    > Make sure `~/.composer/vendor/bin` is in your `$PATH`

2. Initialize a new project:

    `$ jigsaw init my-site`

### Building your first site

Building a Jigsaw site is exactly like building a static HTML site, except that files ending in `.blade.php` will be rendered using Laravel's [Blade Templating Language](http://laravel.com/docs/5.0/templates).

Build out your site however you like in the `/source` directory. It might look something like this:

```
├─ source
│  ├─ _layouts
│  │  └─ master.blade.php
│  ├─ img
│  │  └─ logo.png
│  ├─ about-us.blade.php
│  └─ index.blade.php
└─ config.php
```

When you'd like to build it, run the `build` command from within your project root:

`$ jigsaw build`

Your site will be built and placed in the `/build` directory.

Using the example structure above, you'd end up with something like this:

```
├─ build
│  ├─ img
│  │  └─ logo.png
│  ├─ about-us
│  │  └─ index.html
│  └─ index.html
├─ source
└─ config.php
```

To quickly preview it, start a local PHP server:

`$ php -S localhost:8000/ -t build_local`

#### Layouts

One of the biggest benefits of a templating language is the ability to create reusable layouts.

Since it's important that a layout is never rendered on it's own, you need to be able to tell Jigsaw when a file shouldn't be rendered.

To prevent a file or folder from being rendered, simply prefix it with an underscore:

```
├─ source
│  ├─ _layouts
│  │  └─ master.blade.php # Not rendered
│  └─ index.blade.php     # Rendered
└─ config.php
```

#### Config Variables

Anything you add to the array in `config.php` will be made available as a variable in your templates.

For example, if your config looks like this...

```php
return [
    'contact_email' => 'support@example.com',
];
```

...you can use that variable in your templates like so:

```
@extends('_layouts.master')

@section('content')
    <p>Contact us at {{ $contact_email }}</p>
@stop
```

#### Environments

You might have certain configuration variables that need to be different in different environments, like a Google Analytics tracking ID for example.

Jigsaw lets you specify a different configuration file for each environment to handle this.

To create an environment-specific config file, just stick your environment name in front of the file extension:

`config.production.php`

To build your site for a specific environment, use the `--env` option:

`$ jigsaw build --env=production`

Each environment gets it's own `build_*` folder, so in this case your site will be placed in `build_production`.

> Note: Environment-specific config files get _merged_ with the base config file, so you don't have to repeat values that don't need to change.

#### Pretty URLs

Jigsaw will automatically take any Blade files _not_ named `index` and render them as `index.html` in a subfolder with the same name as the original file.

For example, if you have a file named `about-us.blade.php` in your `source` directory:

```
├─ source
   ├─ _layouts
   ├─ about-us.blade.php
   └─ index.blade.php
```

...it will be rendered as `index.html` in the `build/about-us` directory:

```
├─ build
   ├─ about-us
   │  └─ index.html 
   └─ index.html
```
