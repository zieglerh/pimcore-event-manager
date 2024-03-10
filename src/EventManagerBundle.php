<?php
/**
 * For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 *
 * @copyright Harald Ziegler <h.ziegler@sent.com>
 */
declare(strict_types=1);

namespace EventManagerBundle;

use Pimcore\Extension\Bundle\AbstractPimcoreBundle;
use EventManagerBundle\DependencyInjection\Compiler\EventManagerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class EventManagerBundle
 *
 * @package EventManagerBundle
 */
class EventManagerBundle extends AbstractPimcoreBundle
{
    /**
     * Add compile pass
     *
     * @param ContainerBuilder $container
     *
     * @return void
     */
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new EventManagerPass());
    }
}
