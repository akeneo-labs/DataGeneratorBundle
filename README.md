DataGeneratorBundle
===================

This bundle generates file data in the native Akeneo CSV format.

It's able to generate products and attributes information (including families and attributes options).

So you need a PIM system with channels, locales and currency already setup.

From that, this bundle will generate valid product and attribute data.

Installation
------------
```bash
 $ composer.phar require akeneo-labs/data-generator-bundle ~0.3
```
and update your ``app/AppKernel.php`` as follow:

```php
    $bundles[] = new Pim\Bundle\DataGeneratorBundle\PimDataGeneratorBundle();
```

How to use it
-------------
The catalog generation is done in two phases:
 1. generating the catalog fixtures
 2. generating the product CSV import files

```bash
Usage:
 pim:generate:fixtures <configuration_file_path>
 pim:generate:products-file <configuration_file_path>

Arguments:
 configuration-file    YAML configuration file
```


Configuration file examples
---------------------------
Generating base fixtures:

```yaml
data_generator:
    output_dir: /tmp/fixtures/
    entities:
        attributes:
            count: 200
            identifier_attribute: "sku"
        families:
            count: 30
            attributes_count: 60
            identifier_attribute: "sku"
            label_attribute: "label"
```

Generating products:
```yaml
data_generator:
    output_dir: /tmp/
    entities:
        products:
            count: 1000
            filled_attributes_count: 50
            filled_attributes_standard_deviation: 10
            mandatory_attributes: [sku, name] # properties that will always be filled in with a random value
            delimiter: ,
            force_values: { manufacturer: 'FactoryInc', brand: 'SuperProd' } # properties that if they are filled in, will be filled in the given value
            start_index: 0
            categories_count: 10
```

More configuration examples are available in the ``Resources\examples`` directory.

## Warning
Products data cannot be generated at the same time as the base fixtures (families, categories, attributes, etc...).
Indeed, to generate products data, we use the objects available in the PIM (families, attributes, etc).

So if you need to generate a full catalog, you need to:
 1. generate the fixtures
 2. copy the minimal data set fixtures into a new fixtures set
 3. copy the generated fixtures into this new set
 4. install the new fixtures set by changing the `installer_data` configuration
 5. generate the products data

How to use the generated attributes and families data
-----------------------------------------------------
The generated files are meant to be used in the fixtures. Only the generated products CSV file
must be imported by the import profiles.

Compatibility
-------------
This version is only compatible with Akeneo PIM CE >= 1.4.

Credits
-------
Thanks @fzaninotto for Faker ! (https://github.com/fzaninotto/Faker)
