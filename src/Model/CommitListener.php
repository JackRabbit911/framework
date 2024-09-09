<?php

namespace Sys\Model;

use Sys\Model\Interface\Saveble;
use ReflectionObject;
use ReflectionAttribute;
use SplObjectStorage;

final class CommitListener
{
    private static SplObjectStorage $storage;

    public function __construct()
    {
        if (!isset(self::$storage)) {
            self::$storage = new SplObjectStorage;
        }
    }

    public static function update($entity, $model = null)
    {
        if (!isset(self::$storage)) {
            self::$storage = new SplObjectStorage;
        }

        self::$storage->attach($entity, $model);
    }

    public function handle()
    {
        $saveProvider = config('saverProvider');

        foreach (self::$storage as $entity) {
            $model = self::$storage->offsetGet($entity);

            if (!$model) {
                $model = $saveProvider[get_class($entity)] ?? $this->getByAttribute($entity);
            }

            if (is_string($model)) {
                $model = container()->get($model);
            }
            
            $model->save($entity);
        }
    }

    private function getByAttribute($entity)
    {
        $reflection = new ReflectionObject($entity);
        $attribute = $reflection->getAttributes(Saveble::class, ReflectionAttribute::IS_INSTANCEOF)[0] 
        ?? null;
        return $attribute?->getName();
    }
}
