<?php
/**
 * For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 *
 * @copyright Harald Ziegler <h.ziegler@sent.com>
 */
declare(strict_types=1);

namespace EventManagerBundle\EventSubscriber;

/**
 * Trait EnabledTrait
 *
 * @package EventManagerBundle\EventSubscriber
 */
trait EnabledTrait
{
    /**
     * Listener state
     *
     * @var bool
     */
    private static bool $isEnabled = true;

    /**
     * Disable listener
     *
     * @return bool
     */
    public static function disable(): bool
    {
        $state = self::$isEnabled;
        self::$isEnabled = false;

        return $state;
    }

    /**
     * Enable listener
     *
     * @return bool
     */
    public static function enable(): bool
    {
        $state = self::$isEnabled;
        self::$isEnabled = true;

        return $state;
    }

    /**
     * Set enable or disable listener state
     *
     * @param bool $enable
     *
     * @return bool
     */
    public static function setEnabled(bool $enable): bool
    {
        $state = self::$isEnabled;
        self::$isEnabled = $enable;

        return $state;
    }

    /**
     * Returns state
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return self::$isEnabled;
    }
}
