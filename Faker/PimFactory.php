<?php

namespace Pim\Bundle\DataGeneratorBundle\Faker;

use Faker\Factory;
use Faker\Generator;

/**
 * Override of the Faker Factory to Generate PIM objects depending on providers
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimFactory extends Factory
{
    const DEFAULT_LOCALE = 'en_US';

    protected static $defaultProviders = array('BaseObject');

    /**
     * {@inheritdoc}
     */
    public static function create($locale = self::DEFAULT_LOCALE)
    {
        $generator = new Generator();
        foreach (static::$defaultProviders as $provider) {
            $providerClassName = self::getProviderClassname($provider, $locale);
            $generator->addProvider(new $providerClassName($generator));
        }
        return $generator;
    }

    /**
     * {@inheritdoc}
     */
    protected static function getProviderClassname($provider, $locale = '')
    {
        if ($providerClass = self::findProviderClassname($provider, $locale)) {
            return $providerClass;
        }
        // fallback to default locale
        if ($providerClass = self::findProviderClassname($provider, static::DEFAULT_LOCALE)) {
            return $providerClass;
        }
        // fallback to no locale
        $providerClass = self::findProviderClassname($provider);
        if (class_exists($providerClass)) {
            return $providerClass;
        }
        throw new \InvalidArgumentException(sprintf('Unable to find provider "%s" with locale "%s"', $provider, $locale));
    }

    /**
     * {@inheritdoc}
     */
    protected static function findProviderClassname($provider, $locale = '')
    {
        $providerClass = 'Pim\\Bundle\\DataGeneratorBundle\\Faker\\' . ($locale ? sprintf('Provider\%s\%s', $locale, $provider) : sprintf('Provider\%s', $provider));
        if (class_exists($providerClass, true)) {
            return $providerClass;
        }
    }
}
