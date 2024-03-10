<?php
/**
 * For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 *
 * @copyright Harald Ziegler <h.ziegler@sent.com>
 */
declare(strict_types=1);

namespace EventManagerBundle\DependencyInjection\Traits;

use EventManagerBundle\DependencyInjection\Compiler\EventManagerPass;
use EventManagerBundle\Event\Argument\IsAutoSave;
use EventManagerBundle\Event\Argument\SaveVersionOnly;
use EventManagerBundle\Event\Event;
use EventManagerBundle\EventManager;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Trait EventSubscriberConfigurationTrait
 *
 * @package EventManagerBundle\DependencyInjection\Traits
 */
trait ConfigurationTrait
{
    /**
     * Already registered events for EventManager
     *
     * @var array
     */
    private array $registeredEvents = [];

    /**
     * EventManager Definition
     *
     * @var Definition
     */
    private Definition $eventManagerDef;

    /**
     * Configure EventManager
     *
     * @param ContainerBuilder $container
     *
     * @return void
     * @throws \Exception
     */
    private function configureEventSubscriber(ContainerBuilder $container): void
    {
        $this->eventManagerDef = $container->getDefinition(EventManager::class);
        $this->registerEventSubscriberAttributes($container);
    }

    /**
     * Register attributes for autoconfiguration
     *
     * @param ContainerBuilder $container
     *
     * @return void
     * @throws \Exception
     * @internal
     */
    private function registerEventSubscriberAttributes(ContainerBuilder $container): void
    {
        $container->registerAttributeForAutoconfiguration(
            Event::class,
            [$this, 'registerEventAttribute'],
        );

        $container->registerAttributeForAutoconfiguration(
            IsAutoSave::class,
            [$this, 'registerEventArgumentAttribute'],
        );

        $container->registerAttributeForAutoconfiguration(
            SaveVersionOnly::class,
            [$this, 'registerEventArgumentAttribute'],
        );
    }

    /**
     * Process all Event Attributes
     *
     * @param ChildDefinition   $definition
     * @param Event             $attribute
     * @param \ReflectionMethod $reflector
     *
     * @return void
     * @internal
     */
    public function registerEventAttribute(
        ChildDefinition $definition,
        Event $attribute,
        \ReflectionMethod $reflector
    ): void {
        $event = get_object_vars($attribute)['event'];
        $priority = get_object_vars($attribute)['priority'];
        $method = str_replace(['.', '-'], '', $event);
        $className = $reflector->getDeclaringClass()
                               ->getName();
        if (!isset($this->registeredEvents[$event])) {
            $this->eventManagerDef->addTag(
                'kernel.event_listener', //
                ['event' => $event, 'method' => $method, 'priority' => $priority]
            );
            $this->registeredEvents[$event] = true;
        }

        EventManagerPass::addSubscriber(
            $method,
            $className,
            $reflector->getName(),
            $this->getMethodParameters($reflector)
        );
    }

    /**
     * Process all Event Attributes
     *
     * @param ChildDefinition            $definition
     * @param IsAutoSave|SaveVersionOnly $attribute
     * @param \ReflectionMethod          $reflector
     *
     * @return void
     * @internal
     */
    public function registerEventArgumentAttribute(
        ChildDefinition $definition,
        IsAutoSave|SaveVersionOnly $attribute,
        \ReflectionMethod $reflector
    ): void {
        $className = $reflector->getDeclaringClass()
                               ->getName();
        EventManagerPass::addEventArgument($attribute::class, $className, $reflector->getName());
    }

    /**
     * Builds parameters of method with inheritance mapping
     *
     * @param \ReflectionMethod $method
     *
     * @return array
     */
    private function getMethodParameters(\ReflectionMethod $method): array
    {
        $parameters = [];
        foreach ($method->getParameters() as $parameter) {
            $parameters[$parameter->getName()] = match ($parameter->getType()::class) {
                \ReflectionNamedType::class => [
                    $parameter->getType()
                              ->getName(),
                ],
                \ReflectionUnionType::class => array_map(
                    static fn(\ReflectionType $type): string => $type->getName(),
                    $parameter->getType()
                              ->getTypes()
                ),
            };
        }

        return $parameters;
    }
}
