<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use Oro\Bundle\UserBundle\Entity\Group;
use Pim\Bundle\UserBundle\Entity\User;
use Pim\Component\Catalog\Model\GroupInterface;
use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Yaml;

/**
 * User groups fixtures generator.
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserGroupGenerator
{
    const GROUPS_FILENAME = 'user_groups.csv';

    /**
     * {@inheritdoc}
     */
    public function generate(array $globalConfig, array $config, ProgressHelper $progress, array $options = [])
    {
        $groups = $this->generateGroups($config);

        $normalizedGroups = $this->normalizeGroups(array_values($groups));

        $csvWriter = new CsvWriter($globalConfig['output_dir'] . "/" . static::GROUPS_FILENAME, $normalizedGroups);
        $csvWriter->write();

        $progress->advance();

        return $groups;
    }

    /**
     * Generate groups objects
     *
     * @param array $groupsConfig
     *
     * @return GroupInterface[]
     */
    protected function generateGroups(array $groupsConfig)
    {
        $allGroup = $this->generateGroup(['name' => User::GROUP_DEFAULT]);
        $groups = [User::GROUP_DEFAULT => $allGroup];

        foreach ($groupsConfig as $groupConfig) {
            $group = $this->generateGroup($groupConfig);
            $groups[$group->getName()] = $group;
        }

        return $groups;
    }

    /**
     * Generate a group object from the data provided
     *
     * @param array $groupConfig
     *
     * @return Group
     */
    protected function generateGroup(array $groupConfig)
    {
        $group = new Group();
        $group->setName($groupConfig['name']);

        return $group;
    }

    /**
     * Normalize groups objects into a structured array
     *
     * @param Group[] $groups
     *
     * @return array
     */
    protected function normalizeGroups(array $groups)
    {
        $normalizedGroups = [];
        foreach ($groups as $group) {
            $normalizedGroups[] = $this->normalizeGroup($group);
        }

        return $normalizedGroups;
    }

    /**
     * Normalize group object into a structured array
     *
     * @param Group $group
     *
     * @return array
     */
    protected function normalizeGroup(Group $group)
    {
        return ['name' => $group->getName()];
    }
}
