# PrestaShop Modules ZIP Builder

## Requirements

You need to install a stand-alone composer PHAR archive at the root
of the folder.

You can get it from https://getcomposer.org/download/

You also need an environment where `zip` tool is available.

## Install

```
$ composer install
```

## Run

```
$ php modules-builder.php build-zip [module_folder]
```

ZIP archives are built into the `var` folder.

## Tests

```
$ php tests/Integration/run.php
```
