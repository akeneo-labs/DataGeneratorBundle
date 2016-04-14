<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use Faker\Factory;
use Faker\Generator;
use Oro\Bundle\UserBundle\Entity\Group;
use Oro\Bundle\UserBundle\Entity\Role;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Pim\Bundle\DataGeneratorBundle\Writer\CsvWriter;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Pim\Component\Catalog\Model\CategoryInterface;
use Pim\Bundle\UserBundle\Entity\User;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Yaml;

/**
 * Users fixtures generator.
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserGenerator implements GeneratorInterface
{
    const TYPE = 'users';

    const USERS_FILENAME = 'users.csv';

    /** @var CsvWriter */
    protected $writer;

    /** @var Channel[] */
    protected $channels = [];

    /** @var Locale[] */
    protected $locales = [];

    /** @var Group[] */
    protected $userGroups = [];

    /** @var Role[] */
    protected $userRoles = [];

    /** @var string[] */
    protected $assetCategoryCodes = [];

    /** @var CategoryInterface[] */
    protected $categories = [];

    /** @var Generator */
    protected $faker;

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
    public function generate(array $globalConfig, array $entitiesConfig, ProgressBar $progress, array $options = [])
    {
        $this->locales            = $options['locales'];
        $this->channels           = $options['channels'];
        $this->categories         = $options['categories'];
        $this->userRoles          = $options['user_roles'];
        $this->userGroups         = $options['user_groups'];
        $this->assetCategoryCodes = isset($options['asset_category_codes']) ? $options['asset_category_codes'] : [];

        $this->faker = Factory::create();
        if (isset($globalConfig['seed'])) {
            $this->faker->seed($globalConfig['seed']);
        }
        $users = $this->generateUsers($entitiesConfig);

        $normalizedUsers = $this->normalizeUsers($users);

        $this->writer
            ->setFilename(sprintf(
                '%s%s%s',
                $globalConfig['output_dir'],
                DIRECTORY_SEPARATOR,
                self::USERS_FILENAME
            ))
            ->write($normalizedUsers);

        $progress->advance();

        return ['users' => $users];
    }

    /**
     * Generate users objects
     *
     * @param array $usersConfig
     *
     * @return UserInterface[]
     */
    protected function generateUsers(array $usersConfig)
    {
        $users = [];
        foreach ($usersConfig as $userConfig) {
            $user = $this->generateUser($userConfig);
            $users[$user->getUsername()] = $user;
        }

        return $users;
    }

    /**
     * Generate a user object from the data provided
     *
     * @param array $userConfig
     *
     * @return UserInterface
     */
    protected function generateUser(array $userConfig)
    {
        $user = new User();
        $user->setUsername($userConfig['username']);
        $user->setPassword($userConfig['password']);
        $user->setEmail($userConfig['email']);
        $user->setFirstname($userConfig['firstname']);
        $user->setLastname($userConfig['lastname']);
        $user->setEnabled($userConfig['enable']);

        foreach ($userConfig['groups'] as $groupCode) {
            $user->addGroup($this->userGroups[$groupCode]);
        }

        foreach ($userConfig['roles'] as $roleCode) {
            $user->addRole($this->userRoles[$roleCode]);
        }

        if (isset($userConfig['catalog_locale'])) {
            $localeCode = $userConfig['catalog_locale'];
            $user->setCatalogLocale($this->locales[$localeCode]);
        } else {
            $user->setCatalogLocale(reset($this->locales));
        }

        if (isset($userConfig['catalog_scope'])) {
            $channelCode = $userConfig['catalog_scope'];
            $user->setCatalogScope($this->channels[$channelCode]);
        } else {
            $user->setCatalogScope(reset($this->channels));
        }

        if (isset($userConfig['default_tree'])) {
            $categoryCode = $userConfig['default_tree'];
            $user->setDefaultTree($this->categories[$categoryCode]);
        } else {
            $user->setDefaultTree($this->categories[ChannelGenerator::DEFAULT_TREE]);
        }

        return $user;
    }

    /**
     * Normalize users objects into a structured array
     *
     * @param UserInterface[] $users
     *
     * @return array
     */
    protected function normalizeUsers(array $users)
    {
        $normalizedUsers = [];
        foreach ($users as $user) {
            $normalizedUsers[] = $this->normalizeUser($user);
        }

        return $normalizedUsers;
    }

    /**
     * Normalize user object into a structured array
     *
     * @param UserInterface $user
     *
     * @return array
     */
    protected function normalizeUser(UserInterface $user)
    {
        $userGroupCodes = [];
        foreach ($user->getGroups() as $userGroup) {
            $userGroupCodes[] = $userGroup->getName();
        }

        $userRoleCodes = [];
        foreach ($user->getRoles() as $userRole) {
            $userRoleCodes[] = $userRole->getRole();
        }

        $result = [
            'username'       => $user->getUsername(),
            'password'       => $user->getPassword(),
            'email'          => $user->getEmail(),
            'first_name'     => $user->getFirstname(),
            'last_name'      => $user->getLastname(),
            'catalog_locale' => $user->getCatalogLocale()->getCode(),
            'catalog_scope'  => $user->getCatalogScope()->getCode(),
            'default_tree'   => $user->getDefaultTree()->getCode(),
            'roles'          => implode(',', $userRoleCodes),
            'groups'         => implode(',', $userGroupCodes),
            'enabled'        => $user->isEnabled() ? '1' : '0',
            'user_locale'    => 'en_US',
        ];

        if (count($this->assetCategoryCodes) > 0) {
            $result["default_asset_tree"] = $this->faker->randomElement($this->assetCategoryCodes);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($type)
    {
        return self::TYPE == $type;
    }
}
