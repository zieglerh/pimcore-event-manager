<?php
/**
 * For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 *
 * @copyright Harald Ziegler <h.ziegler@sent.com>
 */
declare(strict_types=1);

namespace EventManagerBundle\Event;

/**
 * Class Event
 *
 * @package EventManagerBundle\Event
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class Event
{
    /**
     * Subscribe $event to EventManager
     *
     * @param string $event
     * @param int    $priority
     */
    public function __construct(public string $event, public int $priority = 0)
    {
    }
}
