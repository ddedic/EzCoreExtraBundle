<?php

/*
 * This file is part of the EzCoreExtraBundle package.
 *
 * (c) Jérôme Vieilledent <jerome@vieilledent.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lolautruche\EzCoreExtraBundle;

use Lolautruche\EzCoreExtraBundle\DependencyInjection\Compiler\AssetPathResolutionPass;
use Lolautruche\EzCoreExtraBundle\DependencyInjection\Compiler\AssetThemePass;
use Lolautruche\EzCoreExtraBundle\DependencyInjection\Compiler\ParameterProviderPass;
use Lolautruche\EzCoreExtraBundle\DependencyInjection\Compiler\PHPStormPass;
use Lolautruche\EzCoreExtraBundle\DependencyInjection\Compiler\TwigThemePass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EzCoreExtraBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new ParameterProviderPass());
    }
}
