## Composer Plugin for Creating Symlinks/Junction Post Install/Update

Just add some mappings in the extra area of your `composer.json` file to create symbolic/juction links after `composer install` & `composer update` commands.

Should work with Windows & Linux.

```
  //composer.json
  ...
  "extra": {
    "symlink-mappings": [
      {
        "src": "vendor/author/package/dist/core.min.css",
        "dest": "test/styles.css"
      },
      {
        "src": "vendor/daylong/dl-assets/dist/wordpress",
        "dest": "test/wordpress"
      }
    ]
  }
}
```
