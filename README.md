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

In order to generate a full catalog, you need to:
 1. Generate the fixtures data
 2. Copy the minimal data set fixtures into a new fixtures set
 3. Copy the generated fixtures into this new set
 4. Install the PIM with the new fixtures set by configuring the `installer_data` parameter
 5. Generate the products data
 6. Import the generated products in the PIM using a CSV product import job
 7. Generate the associations
 8. Import the generated associations in the PIM using a CSV product import jobt a

Products data cannot be generated at the same time as the base fixtures (families, categories, attributes, etc...).
Indeed, to generate products data, we use the objects available in the PIM (families, attributes, etc).
In the same way, product associations are generated using products imported beforehand.

```bash
Usage:
 pim:generate:fixtures <configuration_file_path>
 pim:generate:products-file <configuration_file_path>
 pim:generate:associations-file <configuration_file_path>

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

Now, You can install the PIM with the generated fixtures by configuring the `installer_data` parameter in the `app/config/parameters.yml` file.

Generating products :
```yaml
data_generator:
    output_dir: /tmp/fixtures/products
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

Once the fixtures are set up in the PIM, you can generate and manually import the generated products file.

Generating associations :
```yaml
data_generator:
    output_dir: /tmp/fixtures/associations/
    seed: 20160218
    entities:
        associations:
            filename: associations.csv
            delimiter: ;
            product_associations_per_product: 1
            group_associations_per_product: 1
            products_to_process_limit: 1000
```

Once the products are imported in the PIM, you can generate the associations file. In order to import it in the PIM use a `csv_product_import` job.

This configuration will generate the number of associations as following:
```
MAX(
    product_associations_per_product * Number of products in the PIM + group_associations_per_product * number_of_products_in_the_pim,
    products_to_process_limit
)
```

More configuration examples are available in the ``Resources\examples`` directory.

Compatibility
-------------
This version is only compatible with Akeneo PIM 1.5.

Credits
-------
Thanks @fzaninotto for Faker ! (https://github.com/fzaninotto/Faker)
