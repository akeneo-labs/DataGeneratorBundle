<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use Pim\Bundle\CatalogBundle\Entity\AssociationType;
use Pim\Bundle\CatalogBundle\Entity\AssociationTypeTranslation;
use Pim\Bundle\CatalogBundle\Model\AssociationTypeInterface;
use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Yaml;

/**
 * Generate native YAML file for association types.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationTypeGenerator implements GeneratorInterface
{
    const ASSOCIATION_TYPES_FILENAME = 'association_types.yml';

    const ASSOCIATIONS = 'associations';

    /**
     * {@inheritdoc}
     */
    public function generate(array $config, $outputDir, ProgressHelper $progress, array $options = null)
    {
        $associationTypes = $this->generateAssociationTypes($config);

        $data = [];

        foreach ($associationTypes as $associationType) {
            $data = array_merge($data, $this->normalizeAssociationType($associationType));
        }

        $data = [self::ASSOCIATIONS => $data];

        $this->writeYamlFile($data, $outputDir);

        $progress->advance();

        return $this;
    }

    protected function generateAssociationTypes(array $config)
    {
        $associationTypes = [];

        foreach ($config as $code => $associationTypeConfig) {
            $associationType = new AssociationType();
            $associationType->setCode($code);

            foreach ($associationTypeConfig['labels'] as $locale => $label) {
                $translation = new AssociationTypeTranslation();
                $translation->setLocale($locale);
                $translation->setLabel($label);
                $associationType->addTranslation($translation);
            }

            $associationTypes[] = $associationType;
        }

        return $associationTypes;
    }

    protected function normalizeAssociationType(AssociationTypeInterface $associationType)
    {
        $code = $associationType->getCode();
        $result = [
            $code => [
                'labels' => []
            ]
        ];

        /** @var AssociationTypeTranslation $translation */
        foreach ($associationType->getTranslations() as $translation) {
            $result[$code]['labels'][$translation->getLocale()] = $translation->getLabel();
        }

        return $result;
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
        $yamlData = $dumper->dump($data, 4, 0, true, true);

        file_put_contents($outputDir.'/'.self::ASSOCIATION_TYPES_FILENAME, $yamlData);
    }
}
