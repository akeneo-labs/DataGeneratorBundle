DataGeneratorBundle
===================

This bundle generates file data in the native Akeneo CSV format.

For now, it only able to generate products information.

So you need a PIM system with all attributes, famillies, channels, locales and currency already setup.

From there, this bundle will generate valid product data

Installation
------------
```
composer.phar require akeneo/data-generator-bundle dev-master
```
and update your app/AppKernel.php to add a new Pim\\Bundle\\DataGeneratorBundle\\PimDataGeneratorBundle to the bundle list.


Usage
-----
```
Usage:
 pim:generate-data [-a|--values-number="..."] [-d|--values-number-standard-deviation="..."] [-m|--mandatory-attributes="..."] [-c|--delimiter="..."] [-f|--force-value="..."] [-i|--start-index="..."] [--categories-count="..."] entity-type amount output-file

Arguments:
 entity-type                             Type of entity to generate (product, association)
 amount                                  Number of entities to generate
 output-file                             Target file where to generate the data

Options:
 --values-number (-a)                    Mean number of values to generate per products
 --values-number-standard-deviation (-d) Standard deviation for the number of values per product
 --mandatory-attributes (-m)             List of mandatory attributes for products (the identifier is always included) (multiple values allowed)
 --delimiter (-c)                        Character delimiter used for the CSV file
 --force-value (-f)                      Force the value of an attribute to the provided value. Syntax: attribute_code:value (multiple values allowed)
 --start-index (-i)                      Define the start index value for the products sku definition.
 --categories-count                      Average number of categories in which the product must be present. Set to 0 to have no category presence for products.
```

Example
-------
```
php app/console pim:generate-data product 1000 /tmp/products.csv
```
Will generates 10000 products in `/tmp/products.csv` file

Compatibility
-------------
Tested on PIM CE 1.1.

Credits
-------
Thanks @fzaninotto for Faker ! (https://github.com/fzaninotto/Faker)
