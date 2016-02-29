<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use Oro\Bundle\UserBundle\Entity\Group;
use Symfony\Component\Console\Helper\ProgressBar;
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
    const GROUPS_FILENAME = 'user_groups.yml';

    /**
     * {@inheritdoc}
     */
    public function generate(array $globalConfig, array $config, ProgressBar $progress, array $options = [])
    {
        $groups = $this->generateGroups($config);

        $normalizedGroups = $this->normalizeGroups($groups);

        $this->writeYamlFile(
            $normalizedGroups,
            $globalConfig['output_dir'] . "/" . static::GROUPS_FILENAME
        );

        $progress->advance();

        return $groups;
    }

    /**
     * Generate groups objects
     *
     * @param array $groupsConfig
     *
     * @return Group[]
     */
    protected function generateGroups(array $groupsConfig)
    {
        foreach ($groupsConfig as $groupConfig) {
            $group = $this->generateGroup($groupConfig);
            $groups[$group->getName()] = $group;
        }

        $allGroup = $this->generateGroup(['name' => 'all']);

        $groups[$allGroup->getName()] = $allGroup;

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

        return ['user_groups' => $normalizedGroups];
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
