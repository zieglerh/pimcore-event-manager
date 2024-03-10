<?php
/**
 * For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 *
 * @copyright Harald Ziegler <h.ziegler@sent.com>
 */
declare(strict_types=1);

namespace EventManagerBundle\DependencyInjection\Compiler;

use Pimcore\Model\DataObject\Concrete;
use EventManagerBundle\EventManager;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class EventManagerPass
 *
 * @package EventManagerBundle\DependencyInjection\Compiler
 */
class EventManagerPass implements CompilerPassInterface
{
    /**
     * Configuration for EventManager
     *
     * @var array
     */
    private static array $config = [
        'subscribers' => [],
        'typeMap'     => [],
        'arguments'   => [],
    ];

    /**
     * Add subscriber config
     *
     * @param string $eventMethod
     * @param string $className
     * @param string $subscriberMethod
     * @param array  $methodParameters
     *
     * @return void
     */
    public static function addSubscriber(
        string $eventMethod,
        string $className,
        string $subscriberMethod,
        array $methodParameters,
    ): void {
        self::$config['subscribers'][$eventMethod][$className][$subscriberMethod] = $methodParameters;

        foreach ($methodParameters as $parameters) {
            foreach ($parameters as $parameter) {
                if (is_subclass_of($parameter, Concrete::class)) {
                    $object = new $parameter();
                    self::$config['typeMap']['concrete'][$object->getClassName()][$className] = true;
                }
            }
        }
    }

    /**
     * Add event argument config
     *
     * @param string $attribute
     * @param string $className
     * @param string $subscriberMethod
     *
     * @return void
     */
    public static function addEventArgument(string $attribute, string $className, string $subscriberMethod): void
    {
        self::$config['arguments'][$attribute][$className][$subscriberMethod] = true;
    }

    /**
     * Process compiler
     *
     * @param ContainerBuilder $container
     *
     * @return void
     */
    public function process(ContainerBuilder $container): void
    {
        $definition = $container->getDefinition(EventManager::class);
        $definition->setArgument('$config', self::$config);
    }
}
