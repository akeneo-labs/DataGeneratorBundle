DataGeneratorBundle
===================

This bundle generates file data in the native Akeneo CSV format.

For now, it only able to generate products information.

So you need a PIM system with all attributes, famillies, channels, locales and currency already setup.

From there, this bundle will generate valid product data

Installation
------------
```
composer require akeneo/data-generator-bundle dev-master
```
and update your app/AppKernel.php to add a new Pim\\Bundle\\DataGeneratorBundle\\PimDataGeneratorBundle to the bundle list.


Usage
-----
```
Usage:
 pim:generate-data [-p|--product="..."] [-a|--values-number="..."] [-d|--values-number-standard-deviation="..."] output_dir

Arguments:
 output_dir                              Target directory where to generate the data

Options:
 --product (-p)                          Number of products to generate
 --values-number (-a)                    Mean number of values to generate per products
 --values-number-standard-deviation (-d) Standard deviation for the number of values per product
```

Example
-------
```
php app/console pim:generate -p 10000 /tmp/
```
Will generates 10000 products in `/tmp/products.csv` file

Compatibility
-------------
Tested on PIM CE 1.1.

Credits
-------
Thanks @fzaninotto for Faker ! (https://github.com/fzaninotto/Faker)
