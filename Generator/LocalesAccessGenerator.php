<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use Oro\Bundle\UserBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Yaml;

/**
 * Generate native YAML file for locales accesses. It gives all rights for every group in every locale.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocalesAccessGenerator implements GeneratorInterface
{
    /** @staticvar string */
    const LOCALE_ACCESSES_FILENAME = 'locale_accesses.yml';

    /** @staticvar string */
    const LOCALE_ACCESSES = 'locale_accesses';

    /** @var Group[] */
    protected $groups;

    /** @var Locale[] */
    protected $locales;

    /**
     * @param Group[] $groups
     */
    public function setGroups(array $groups)
    {
        $this->groups = $groups;
    }

    /**
     * @param Locale[] $locales
     */
    public function setLocales(array $locales)
    {
        $this->locales = $locales;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(array $config, $outputDir, ProgressHelper $progress, array $options = null)
    {
        $data = [];
        foreach ($this->locales as $locale) {
            $localeCode = $locale->getCode();
            $data[$localeCode] = [];
            foreach (['viewProducts', 'editProducts'] as $access) {
                $data[$localeCode][$access] = [];
                foreach ($this->groups as $group) {
                    if ('all' !== $group->getName()) {
                        $data[$localeCode][$access][] = $group->getName();
                    }
                }
            }
        }

        $assetCategoryAccesses = [self::LOCALE_ACCESSES => $data];

        $progress->advance();

        $this->writeYamlFile($assetCategoryAccesses, $outputDir);
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

        file_put_contents($outputDir.'/'.self::LOCALE_ACCESSES_FILENAME, $yamlData);
    }
}
