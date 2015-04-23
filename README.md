nstallation
============

Step 1: Download the Bundle
---------------------------

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```bash
$ composer require pydev/imageeditor-bundle "~1"
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Step 2: Enable the Bundle
-------------------------

Then, enable the bundle by adding the following line in the `app/AppKernel.php`
file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...

            new PyDev\ImageEditorBundle\ImageEditorBundle()
        );

        // ...
    }

    // ...
}
```

Step 3: include routes
----------------------

Include the following in your routing.yml

```
py_dev_imageeditor:
    resource: "@ImageEditorBundle/Resources/config/routing.yml"
    prefix:   /edit_image
```
