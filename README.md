DataGeneratorBundle
===================

[![Build Status](https://travis-ci.org/akeneo-labs/DataGeneratorBundle.svg?branch=master)](https://travis-ci.org/akeneo-labs/DataGeneratorBundle)

This bundle generates file data in the native Akeneo CSV format.

It's able to generate products and attributes information (including families and attributes options).

So you need a PIM system with channels, locales and currency already setup.

From that, this bundle will generate valid product and attribute data.

Compatibility
-------------

This bundle is compatible with Akeneo PIM 1.3, 1.4, 1.5 & 1.6.

As Akeneo PIM 1.7 exposes a Web API, this Web API can be used to inject data without having to install a bundle in it.

Here is an example of a [simple command line tool](https://github.com/nidup/akeneo-data-generator) which generates & injects data through the Web API.

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

**Be careful! The order of the entries under `entities` is important.**

```yaml
data_generator:
    output_dir: /path/to/your/pim/src/Pim/Bundle/InstallerBundle/Resources/fixtures/data_generator/
    entities:
        locales: ~
        channels:
            mobile:
                locales: [en_US, fr_FR]
                currencies: [USD, EUR]
            magento:
                locales: [en_US, fr_FR, de_DE, es_ES]
                currencies: [USD, EUR]
            paper:
                locales: [en_US, fr_FR, de_DE, es_ES, it_IT]
                currencies: [USD, EUR]
            website:
                locales: [en_US, fr_FR, de_DE, es_ES, it_IT]
                currencies: [USD, EUR]
            marketplace:
                locales: [en_US]
                currencies: [USD]
        attribute_groups: ~
        categories: ~
        attributes:
            count: 2000
            identifier_attribute: sku
            force_attributes: [name=pim_catalog_text]
        attribute_options:
            count_per_attribute: 5
        families:
            count: 10
            attributes_count: 200
            identifier_attribute: sku
            label_attribute: name

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
This version is only compatible with Akeneo PIM with latest builds.

Credits
-------
Thanks @fzaninotto for Faker ! (https://github.com/fzaninotto/Faker)
