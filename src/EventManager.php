<?php
/**
 * For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 *
 * @copyright Harald Ziegler <h.ziegler@sent.com>
 */
declare(strict_types=1);

namespace EventManagerBundle;

use EventManagerBundle\Event\Argument\IsAutoSave;
use EventManagerBundle\Event\Argument\SaveVersionOnly;
use EventManagerBundle\EventSubscriber\EventSubscriberInterface;
use EventManagerBundle\EventSubscriber\EnabledTrait;
use Pimcore\Model\DataObject\Concrete;
use Pimcore\Model\Element\ValidationException;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\TaggedLocator;

/**
 * Class EventManager
 *
 * @package EventManagerBundle
 */
class EventManager
{
    use EnabledTrait;

    /**
     * Service tag namespace
     */
    public const SERVICE_TAG = 'event_manager.event_subscriber.handler';

    /**
     * @param array              $config
     * @param ContainerInterface $locator
     * @param LoggerInterface    $logger
     */
    public function __construct(
        private array $config,
        #[TaggedLocator(self::SERVICE_TAG)]
        private ContainerInterface $locator,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * Catch event calls
     * See Asset/Documents/UserRole/DataObjectEvents.php constants @Event
     *
     * @param string $method
     * @param array  $args
     *
     * @return void
     * @throws \Throwable
     * @internal
     */
    public function __call(string $method, array $args): void
    {
        if (!self::isEnabled()) {
            return;
        }
        $eventArgs = [];
        if (method_exists($args[0], 'getArguments')) {
            $eventArgs = $args[0]->getArguments();
        }
        $object = null;
        foreach (['getObject', 'getAsset', 'getSubject', 'getDocument', 'getUserRole', 'getVersion'] as $eventMethod) {
            if (method_exists($args[0], $eventMethod)) {
                $object = $args[0]->$eventMethod();
            }
        }
        $this->resolve($method, $object, $args, $eventArgs);
    }

    /**
     * Resolve method call to subscribers
     *
     * @param string $method
     * @param mixed  $object
     * @param array  $args
     * @param array  $eventArgs
     *
     * @return void
     * @throws \Throwable
     */
    private function resolve(string $method, mixed $object, array $args, array $eventArgs): void
    {
        try {
            $subscribers = $this->getSubscribers($method, $object, $eventArgs);
        } catch (\Exception $exception) {
            $this->logger->error(
                'Could not get subscribers :: {msg}', //
                ['exception' => $exception, 'msg' => $exception->getMessage()]
            );

            return;
        }

        foreach ($subscribers as $subscriber => $subscriberMethods) {
            try {
                $subscriber = $this->locator->get($subscriber);
            } catch (\Throwable $throwable) {
                $this->logger->critical(
                    'Could not load subscriber: {class}', //
                    ['class' => $subscriber, 'throwable' => $throwable]
                );
                continue;
            }

            if (!$subscriber instanceof EventSubscriberInterface) {
                $this->logger->error(
                    'Skipping subscriber class: {class}', //
                    ['class' => $subscriber::class]
                );
                continue;
            }

            if (!$subscriber->isEnabled()) {
                continue;
            }

            foreach ($subscriberMethods as $methodName => $arguments) {
                $callArgs = [];
                foreach ($arguments as $argumentName => $classNames) {
                    foreach ($classNames as $className) {
                        if ($object instanceof $className) {
                            $callArgs[$argumentName] = $object;
                            break;
                        }

                        foreach ($args as $arg) {
                            if ($arg instanceof $className) {
                                $callArgs[$argumentName] = $arg;
                                break(2);
                            }
                        }
                    }

                    if (!isset($callArgs[$argumentName])) {
//                        $this->eventSubscriberLogger->error(
//                            'Could not resolve {subscriber}::{method} for argument {argument}', //
//                            [
//                                'subscriber' => $subscriber::class,
//                                'method'     => $methodName,
//                                'argument'   => $argumentName,
//                            ]
//                        );
                        continue(2);
                    }
                }

                try {
                    call_user_func_array([$subscriber, $methodName], $callArgs);
                } catch (ValidationException $exception) {
                    throw $exception;
                } catch (\Throwable $throwable) {
                    $this->logger->critical(
                        'Listener threw exception: {class}', //
                        [
                            'class'     => $subscriber::class . '::' . $methodName,
                            'exception' => $throwable,
                        ]
                    );
                    throw $throwable;
                }
            }
        }
    }

    /**
     * Returns subscribers considering $method and $object and event args
     *
     * @param string $method
     * @param mixed  $object
     * @param array  $eventArgs
     *
     * @return array
     * @throws \Exception
     */
    private function getSubscribers(string $method, mixed $object, array $eventArgs): array
    {
        $subscribers = $this->getSubscribersByMethod($method);
        $subscribers = $this->filterSubscribersByObject($subscribers, $object);
        $subscribers = $this->filterSubscribersByEventArgs($subscribers, $eventArgs);

        return array_filter($subscribers);
    }

    /**
     * Returns subscribers
     *
     * @param string $method
     *
     * @return array
     * @throws \Exception
     */
    public function getSubscribersByMethod(string $method): array
    {
        $cache = $this->config['subscribers'];
        if (!isset($cache[$method])) {
            throw new \Exception('Unknown method: ' . $method);
        }

        return $cache[$method];
    }

    /**
     * Filters subscribers by $object
     *
     * @param array $subscribers
     * @param mixed $object
     *
     * @return array
     */
    private function filterSubscribersByObject(array $subscribers, mixed $object): array
    {
        if ($object instanceof Concrete) {
            return array_intersect_key(
                $subscribers,
                $this->config['typeMap']['concrete'][$object->getClassName()] ?? []
            );
        }

        return $subscribers;
    }

    /**
     * Filters subscribers by event arguments
     *
     * @param array $subscribers
     * @param array $eventArgs
     *
     * @return array
     */
    private function filterSubscribersByEventArgs(array $subscribers, array $eventArgs): array
    {
        $eventArgConfig = [
            'saveVersionOnly' => SaveVersionOnly::class,
            'isAutoSave'      => IsAutoSave::class,
        ];
        foreach ($eventArgConfig as $arg => $class) {
            if (isset($eventArgs[$arg]) && $eventArgs[$arg]) {
                $config = $this->config['arguments'][$class] ?? [];
                $subscribers = array_intersect_key($subscribers, $config);
                foreach ($subscribers as $subscriber => &$methods) {
                    $methods = array_intersect_key($methods, $config[$subscriber]);
                }
            }
        }

        return $subscribers;
    }
}
