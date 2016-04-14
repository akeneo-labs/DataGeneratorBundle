<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use Oro\Bundle\UserBundle\Entity\Role;
use Pim\Bundle\DataGeneratorBundle\Writer\CsvWriter;
use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Yaml;

/**
 * User roles fixtures generator.
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserRoleGenerator implements GeneratorInterface
{
    const TYPE = 'user_roles';

    const ROLES_FILENAME = 'user_roles.csv';

    /** @var CsvWriter */
    protected $writer;

    /**
     * @param CsvWriter $writer
     */
    public function __construct(CsvWriter $writer)
    {
        $this->writer = $writer;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(array $globalConfig, array $entitiesConfig, ProgressHelper $progress, array $options = [])
    {
        $roles = $this->generateRoles($entitiesConfig);

        $normalizedRoles = $this->normalizeRoles($roles);

        $this->writer
            ->setFilename(sprintf(
                '%s%s%s',
                $globalConfig['output_dir'],
                DIRECTORY_SEPARATOR,
                self::ROLES_FILENAME
            ))
            ->write($normalizedRoles);

        $progress->advance();

        return ['user_roles' => $roles];
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
     * @param array  $roleConfig
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
     * @param Role[] $roles
     *
     * @return array
     */
    public function normalizeRoles(array $roles)
    {
        $normalizedRoles = [];

        foreach ($roles as $role) {
            $normalizedRoles[] = $this->normalizeRole($role);
        }

        return $normalizedRoles;
    }

    /**
     * Normalize role object into a structured array
     *
     * @param Role $role
     *
     * @return array
     */
    public function normalizeRole(Role $role)
    {
        return [
            'label' => $role->getLabel(),
            'role'  => $role->getRole(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supports($type)
    {
        return self::TYPE == $type;
    }
}
