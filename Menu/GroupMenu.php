<?php

/*
 * This file is part of the enhavo package.
 *
 * (c) WE ARE INDEED GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Enhavo\Bundle\UserBundle\Menu;

use Enhavo\Bundle\AppBundle\Menu\AbstractMenuType;
use Enhavo\Bundle\AppBundle\Menu\Type\LinkMenuType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author gseidel
 */
class GroupMenu extends AbstractMenuType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'icon' => 'people_outline',
            'label' => 'group.label.group',
            'translation_domain' => 'EnhavoUserBundle',
            'route' => 'enhavo_user_admin_group_index',
            'permission' => 'ROLE_ENHAVO_USER_GROUP_INDEX',
        ]);
    }

    public static function getName(): ?string
    {
        return 'user_group';
    }

    public static function getParentType(): ?string
    {
        return LinkMenuType::class;
    }
}
