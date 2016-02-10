<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Yaml;

/**
 * Generate native YAML file for group types. No configuration allowed, it generates VARIANT and RELATED group types.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupTypeGenerator implements GeneratorInterface
{
    const GROUP_TYPES_FILENAME = 'group_types.yml';

    /**
     * {@inheritdoc}
     */
    public function generate(array $config, $outputDir, ProgressHelper $progress, array $options = null)
    {
        $data = [
            'group_types' => [
                'VARIANT' => ['variant' => 1],
                'RELATED' => ['variant' => 0],
            ]
        ];

        $progress->advance();

        $this->writeYamlFile($data, $outputDir);
    }

    /**
     * Write a YAML file
     *
     * @param array  $data
     * @param string $outputDir
     */
    protected function writeYamlFile(array $data, $outputDir)
    {
        $dumper = new Yaml\Dumper();
        $yamlData = $dumper->dump($data, 3, 0, true, true);

        file_put_contents($outputDir.'/'.self::GROUP_TYPES_FILENAME, $yamlData);
    }
}
