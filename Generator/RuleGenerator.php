<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use Akeneo\Bundle\RuleEngineBundle\Model\Rule;
use Akeneo\Bundle\RuleEngineBundle\Model\RuleDefinition;
use Akeneo\Bundle\RuleEngineBundle\Model\RuleInterface;
use Akeneo\Bundle\RuleEngineBundle\Normalizer\RuleNormalizer;
use Faker\Factory;
use Faker\Generator;
use Pim\Bundle\BaseConnectorBundle\Validator\Constraints\Channel;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Yaml\Dumper;

/**
 * Rules fixtures generator.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RuleGenerator implements GeneratorInterface
{
    const RULE_FILENAME = 'rule.yml';

    /** @var Generator */
    protected $faker;

    /** @var AttributeInterface[] */
    protected $attributes;

    /** @var LocaleInterface[] */
    protected $locales;

    /** @var ChannelInterface[] */
    protected $channels;

    /**
     * {@inheritdoc}
     */
    public function generate(array $globalConfig, array $generatorConfig, ProgressHelper $progress, array $options = [])
    {
        $count = (int)$generatorConfig['count'];
        $this->attributes = $options['attributes'];
        $this->locales    = $options['locales'];
        $this->channels   = $options['channels'];

        $this->faker = Factory::create();
        if (isset($globalConfig['seed'])) {
            $this->faker->seed($globalConfig['seed']);
        }

        $rules = [];
        for ($i = 0; $i < $count; $i++) {
            $rule = $this->generateRule();
            $rules[] = $rule;
            $progress->advance();
        }

        $this->writeYamlFile(
            $rules,
            sprintf('%s%s%s', $globalConfig['output_dir'], DIRECTORY_SEPARATOR, self::RULE_FILENAME)
        );
    }

    protected function generateRule()
    {
        $definition = new RuleDefinition();
        $rule = new Rule($definition);

        $condition = $this->getRandomCondition();

        $action = [
            'type'  => 'set',
            'field' => $this->getRandomTextField()->getCode(),
            'value' => $this->getRandomValue(),
        ];

        $rule->setPriority($this->faker->numberBetween(0,10));
        $rule->setCode($this->getRandomCode());
        $rule->setContent(['conditions' => [$condition], 'actions' => [$action]]);

        return $rule;
    }

    protected function getRandomCondition()
    {
        $attribute = $this->getRandomAttribute();

        $result = ['field' => $attribute->getCode()];

        if ($attribute->isLocalizable()) {
            /** @var LocaleInterface $locale */
            $locale = $this->faker->randomElement($this->locales);
            $result['locale'] = $locale->getCode();
        }

        if ($attribute->isScopable()) {
            /** @var ChannelInterface $scope */
            $scope = $this->faker->randomElement($this->channels);
            $result['scope'] = $scope->getCode();
        }

        return array_merge($result, $this->generateRandomCondition($attribute->getAttributeType()));
    }

    /**
     * @param $attributeType
     *
     * @return array
     */
    protected function generateRandomCondition($attributeType)
    {
        switch($attributeType) {
            case AttributeTypes::TEXT;
            case AttributeTypes::TEXTAREA:
                $randomOperator = $this->faker->randomElement([
                    Operators::IS_EMPTY,
                    Operators::IS_NOT_EMPTY,
                    Operators::STARTS_WITH,
                    Operators::CONTAINS,
                    Operators::ENDS_WITH,
                    Operators::DOES_NOT_CONTAIN,
                ]);

                return [
                    'operator' => $randomOperator,
                    'value'    => substr($this->faker->word(), 0, 1),
                ];

            case AttributeTypes::NUMBER:
                $randomOperator = $this->faker->randomElement([
                    Operators::LOWER_THAN,
                    Operators::LOWER_OR_EQUAL_THAN,
                    Operators::GREATER_THAN,
                    Operators::GREATER_OR_EQUAL_THAN,
                    Operators::EQUALS,
                    Operators::IS_EMPTY
                ]);

                return [
                    'operator' => $randomOperator,
                    'value'    => $this->faker->numberBetween(0,9),
                ];
        }

        return [];
    }

    /**
     * @return AttributeInterface
     *
     * @throws \Exception
     */
    protected function getRandomAttribute()
    {
        $allowedAttributes = [];
        foreach ($this->attributes as $attribute) {
            if (in_array($attribute->getAttributeType(), [
                AttributeTypes::TEXT,
                AttributeTypes::TEXTAREA,
                AttributeTypes::NUMBER,
            ])) {
                $allowedAttributes[] = $attribute;
            }
        }

        if (empty($allowedAttributes)) {
            throw new \Exception('There is no applicable field for rule generation.');
        }

        return $this->faker->randomElement($allowedAttributes);
    }

    /**
     * @return string
     *
     * @throws \Exception
     */
    protected function getRandomTextField()
    {
        $allowedAttributes = [];
        foreach ($this->attributes as $attribute) {
            if (in_array($attribute->getAttributeType(), [
                AttributeTypes::TEXT,
                AttributeTypes::TEXTAREA
            ]) && !$attribute->isLocalizable() && !$attribute->isScopable()) {
                $allowedAttributes[] = $attribute;
            }
        }

        if (empty($allowedAttributes)) {
            throw new \Exception('There is no applicable text field for rule generation.');
        }

        return $this->faker->randomElement($allowedAttributes);
    }

    /**
     * @return string
     */
    protected function getRandomValue()
    {
        return $this->faker->word();
    }

    /**
     * @return string
     */
    protected function getRandomCode()
    {
        return implode('_', $this->faker->words(3));
    }

    /**
     * @param RuleInterface[] $rules
     * @param string          $filename
     */
    protected function writeYamlFile($rules, $filename)
    {
        $dumper = new Dumper();

        $normalizer = new RuleNormalizer();

        $data = [
            'rules' => array_map(
                function ($rule) use ($normalizer) {
                    return $normalizer->normalize($rule);
                },
                $rules
            )
        ];
        $yamlData = $dumper->dump($data, 5, 0, true, true);

        file_put_contents($filename, $yamlData);
    }
}
