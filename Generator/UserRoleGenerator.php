<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use Oro\Bundle\UserBundle\Entity\Role;
use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Yaml;

/**
 * User roles fixtures generator.
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserRoleGenerator
{
    /** @staticvar string */
    const ROLES_FILENAME = 'user_roles.yml';

    /**
     * {@inheritdoc}
     */
    public function generate(array $config, $outputDir, ProgressHelper $progress)
    {
        $roles = $this->generateRoles($config);

        $normalizedRoles = $this->normalizeRoles($roles);

        $this->writeYamlFile(
            $normalizedRoles,
            $outputDir . "/" . static::ROLES_FILENAME
        );

        $progress->advance();

        return $roles;
    }

    /**
     * Generate roles objects
     *
     * @param array $rolesConfig
     *
     * @return Role[]
     */
    protected function generateRoles(array $rolesConfig)
    {
        foreach ($rolesConfig as $roleKey => $roleConfig) {
            $role = $this->generateRole($roleKey, $roleConfig);
            $roles[$role->getRole()] = $role;
        }

        return $roles;
    }

    /**
     * Generate a role object from the data provided
     *
     * @param string $key
     * @param array $config
     *
     * @return Role
     */
    public function generateRole($key, array $roleConfig)
    {
        $role = new Role();
        $role->setRole($key);
        $role->setLabel($roleConfig['label']);

        return $role;
    }

    /**
     * Normalize roles objects into a structured array
     *
     * @param Role[]
     *
     * @return array
     */
    public function normalizeRoles(array $roles)
    {
        $normalizedRoles = [];

        foreach ($roles as $role) {
            $normalizedRoles[$role->getRole()] = $this->normalizeRole($role);
        }

        return [ "user_roles" => $normalizedRoles ];
    }

    /**
     * Normalize role object into a structured array
     *
     * @param Role
     *
     * @return array
     */
    public function normalizeRole(Role $role)
    {
        return [
            "label" => $role->getLabel()
        ];
    }
    /**
     * Write a YAML file
     *
     * @param array  $data
     * @param string $filename
     */
    protected function writeYamlFile(array $data, $filename)
    {
        $dumper = new Yaml\Dumper();
        $yamlData = $dumper->dump($data, 5, 0, true, true);

        file_put_contents($filename, $yamlData);
    }
}
