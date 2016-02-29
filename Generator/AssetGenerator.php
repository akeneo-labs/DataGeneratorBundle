<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use bheller\ImagesGenerator\ImagesGeneratorProvider;
use Faker\Factory;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * Generate image assets
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssetGenerator implements GeneratorInterface
{
    /**
     * Returns the pathes of the generated assets.
     *
     * {@inheritdoc}
     */
    public function generate(array $globalConfig, array $config, ProgressBar $progress, array $options = [])
    {
        $faker = Factory::create();
        if (isset($globalConfig['seed'])) {
            $faker->seed($globalConfig['seed']);
        }

        $faker->addProvider(new ImagesGeneratorProvider($faker));

        $assetDirectory = $globalConfig['output_dir'] . '/' . $config['asset_directory'];
        if (!is_dir($assetDirectory)) {
            mkdir($assetDirectory);
        }

        $assetCount = $config['count'];
        $images = [];

        for ($index = 1; $index <= $assetCount; $index++) {
            $imagePath = $faker->imageGenerator($assetDirectory,
                $faker->numberBetween(600, 800),
                $faker->numberBetween(400, 600),
                'jpg',
                true,
                $faker->word,
                $faker->hexColor, $faker->hexColor
            );
            $images[] = $imagePath;
            $progress->advance();
        }

        return $images;
    }
}
