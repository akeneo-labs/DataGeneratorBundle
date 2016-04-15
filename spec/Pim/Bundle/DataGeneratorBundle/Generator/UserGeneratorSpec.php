<?php

namespace spec\Pim\Bundle\DataGeneratorBundle\Generator;

use Doctrine\Common\Collections\Collection;
use Oro\Bundle\UserBundle\Entity\Group;
use Oro\Bundle\UserBundle\Entity\Role;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGeneratorBundle\Writer\CsvWriter;
use Pim\Component\Catalog\Model\CategoryInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Prophecy\Argument;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Security\Core\Role\RoleInterface;

class UserGeneratorSpec extends ObjectBehavior
{
    function let(CsvWriter $writer)
    {
        $this->beConstructedWith($writer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\DataGeneratorBundle\Generator\UserGenerator');
    }

    function it_is_a_generator()
    {
        $this->shouldHaveType('Pim\Bundle\DataGeneratorBundle\Generator\GeneratorInterface');
    }

    function it_supports_users()
    {
        $this->supports('users')->shouldReturn(true);
        $this->supports('yolo')->shouldReturn(false);
    }

    function it_generates_users(
        $writer,
        ProgressBar $progress,
        LocaleInterface $locale,
        ChannelInterface $channel,
        CategoryInterface $categoryMaster,
        Role $roleAdministrator,
        Group $groupItSupport,
        Collection $rolesCollection
    ) {
        $globalOptions = ['output_dir' => '/'];
        $options = [
            'locales'     => [$locale],
            'channels'    => [$channel],
            'categories'  => ['master' => $categoryMaster],
            'user_roles'  => ['ROLE_ADMINISTRATOR' => $roleAdministrator],
            'user_groups' => ['IT support' => $groupItSupport],
        ];
        $entitiesConfig = [
            [
                'username'  => 'admin',
                'password'  => 'admin',
                'email'     => 'admin@example.com',
                'firstname' => 'Peter',
                'lastname'  => 'Doe',
                'roles'     => [ 'ROLE_ADMINISTRATOR' ],
                'groups'    => [ 'IT support' ],
                'enable'    => true,
            ]
        ];
        $groupItSupport->getName()->willReturn('IT Support');
        $groupItSupport->getRoles()->willReturn($rolesCollection);
        $rolesCollection->toArray()->willReturn([$roleAdministrator]);
        $categoryMaster->getCode()->willReturn('Master');
        $locale->getCode()->willReturn('fr_FR');
        $channel->getCode()->willReturn('Channel');
        $roleAdministrator->getRole()->willReturn('ROLE_ADMINISTRATOR');

        $writer->setFilename(Argument::any())->willReturn($writer);

        $writer->write([
            [
                'username'       => 'admin',
                'password'       => 'admin',
                'email'          => 'admin@example.com',
                'first_name'     => 'Peter',
                'last_name'      => 'Doe',
                'catalog_locale' => 'fr_FR',
                'catalog_scope'  => 'Channel',
                'default_tree'   => 'Master',
                'roles'          => 'ROLE_ADMINISTRATOR',
                'groups'         => 'IT Support',
                'enabled'        => '1',
                'user_locale'    => 'en_US'
            ]
        ])->shouldBeCalled();

        $this->generate($globalOptions, $entitiesConfig, $progress, $options);
    }
}
