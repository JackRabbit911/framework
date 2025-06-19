<?php

namespace Sys\Model;

use Exception;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Sys\Model\Interface\Saveble;
use ReflectionObject;
use ReflectionAttribute;
use SplObjectStorage;

final class CommitListener
{
    private static SplObjectStorage $storage;
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        if (!isset(self::$storage)) {
            self::$storage = new SplObjectStorage;
        }

        $this->container = $container;
    }

    public static function update($entity, $model = null)
    {
        if (!isset(self::$storage)) {
            self::$storage = new SplObjectStorage;
        }

        self::$storage->attach($entity, $model);
    }

    public function handle(ResponseInterface $response): ResponseInterface
    {
        $saveProvider = config('saverProvider');

        foreach (self::$storage as $entity) {
            $model = self::$storage->offsetGet($entity);

            if (!$model) {
                $model = $saveProvider[get_class($entity)] ?? $this->getByAttribute($entity);
            }

            if (is_string($model)) {
                $model = $this->container->get($model);
            }

            if (!method_exists($model, 'save')) {
                throw new Exception('Method ' . get_class($model) . ' does not exists');
            }
            
            $model->save($entity);
        }
        
        return $response;
    }

    private function getByAttribute($entity)
    {
        $reflection = new ReflectionObject($entity);
        $attribute = $reflection->getAttributes(Saveble::class, ReflectionAttribute::IS_INSTANCEOF)[0]
        ?? null;

        if (!$attribute) {
            throw new Exception('Attribute <modelClass> implements Saveble not found');
        }

        return $attribute->getName();
    }
}
