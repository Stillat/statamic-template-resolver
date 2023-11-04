# Statamic Template Resolver

Statamic Template Resolver is a simple utility addon, intended to be used by other addons. It provides a simple utility for selecting and rendering a template based on an entry's *blueprint* and *collection*, with support for a fallback default template.

Example use-cases:

* Selecting templates dynamically to generate social media images from HTML,
* Generating HTML documents on-the-fly, without requiring network requests to the site,
* Anything where you need to support customizable templates based on an entry's blueprint/collection details

## How to Install

Run the following command from your project root:

``` bash
composer require stillat/statamic-template-resolver
```

## How to Use

You need to create an instance of `StringTemplateManager`, and supply the directory to search for templates in.

```php
<?php

use Stillat\StatamicTemplateResolver\StringTemplateManager;

$manager = new StringTemplateManager(
    resource_path('views/social_media_images')
);
```

Once you have a `StringTemplateManager` instance, you can check if a template exists for a given collection/blueprint combination:

```php
<?php

// ...

if ($manager->hasTemplate($collection, $blueprint)) {
    // The template exists.
}

```

The `hasTemplate` method will return `true` if a specific template *or* the default template exists. To create a default template, create a file named `default.antlers.html` or `default.blade.php` at the root of the template folder.

In our example, the default template would need to be placed here:

```
views/social_media_images/default.antlers.html
```

Specific collection/blueprint templates are stored within a nested directory structure using the following format:

```
<template_directory><collection_handle>/<blueprint_handle>.<extension>
```

For example, if we had a `blog` collection, with a `post` blueprint, we could create a specific template at the following location:

```
views/social_media_images/blog/post.antlers.html
```

This library supports the following extensions:

* `.antlers.html`: Renders the template using Statamic's Antlers templating engine
* `.blade.php`: Renders the template using Laravel's Blade templating engine

To render a template with data, we may use the `render` method:

```php
<?php

// ...

$results = $manager->render(
    'colllection_handle',
    'blueprint_handle',
    $data
);

```

The `render` method will return `null` if a template could not be found; `$data` is provided as an array, and is required.

We may also optionally modify the template before rendering it by supplying an optional callable as the fourth argument:

```php
<?php

// Modify the template before its rendered.
$results = $manager->render(
    'collection_handle',
    'blueprint_handle',
    $data,
    function ($template, $data) {
        return mb_strtoupper($template);
    }
);

```

The modifying callable will receive the unmodified template contents as its first argument, and the original data array as the second.

## License

Statamic Template Resolver is free software, released under the MIT license.
