<?php
/**
 * For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 *
 * @copyright Harald Ziegler <h.ziegler@sent.com>
 */
declare(strict_types=1);

namespace EventManagerBundle\Event\Argument;

/**
 * Class IsAutoSave
 *
 * @package EventManagerBundle\Event\Argument
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
final class IsAutoSave
{
}
