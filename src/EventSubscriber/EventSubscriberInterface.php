<?php
/**
 * For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 *
 * @copyright Harald Ziegler <h.ziegler@sent.com>
 */
declare(strict_types=1);

namespace EventManagerBundle\EventSubscriber;

use EventManagerBundle\EventManager;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * Interface EventSubscriberInterface
 *
 * @package EventManagerBundle\EventSubscriber
 */
#[AutoconfigureTag(EventManager::SERVICE_TAG)]
interface EventSubscriberInterface
{
    /**
     * Returns listener enabled state
     *
     * @return bool
     */
    public function isEnabled(): bool;
}
