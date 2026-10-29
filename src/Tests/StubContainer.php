<?php

declare(strict_types=1);

namespace Sys\Tests;

use Psr\Container\ContainerInterface;

class StubContainer implements ContainerInterface
{
    private array $definitions = [];

    private array $instances = [];

    private array $aliases = [];

    /**
     * @param array<string, mixed|callable> $definitions  Массив id => value|factory
     */
    public function __construct(array $definitions = [])
    {
        foreach ($definitions as $id => $definition) {
            $this->definitions[$id] = $definition;
        }
    }

    /**
     * Добавить определение (возвращает клон — immutable).
     *
     * @param string           $id      ID сервиса
     * @param mixed|callable   $definition  Значение или factory-коллбэк
     */
    public function addDefinition(string $id, mixed $definition): self
    {
        $clone = clone $this;
        $clone->definitions[$id] = $definition;

        return $clone;
    }

    /**
     * Добавить определение пачкой.
     *
     * @param array<string, mixed|callable> $definitions
     */
    public function addDefinitions(array $definitions): self
    {
        $clone = clone $this;
        foreach ($definitions as $id => $definition) {
            $clone->definitions[$id] = $definition;
        }

        return $clone;
    }

    /**
     * Зарегистрировать алиас (id => resolved_id).
     */
    public function addAlias(string $alias, string $id): self
    {
        $clone = clone $this;
        $clone->aliases[$alias] = $id;

        return $clone;
    }

    /**
     * Зарегистрировать готовый экземпляр (синглтон).
     */
    public function addInstance(string $id, object $instance): self
    {
        $clone = clone $this;
        $clone->instances[$id] = $instance;

        return $clone;
    }

    /**
     * Зарегистрировать factory-коллбэк.
     */
    public function addFactory(string $id, callable $factory): self
    {
        return $this->addDefinition($id, $factory);
    }

    /**
     * Получить сервис.
     *
     * Порядок разрешения:
     * 1. Готовый экземпляр (addInstance)
     * 2. Алиас → resolved id
     * 3. Factory (callable)
     * 4. Прямое значение
     * 5. throw NotFoundException
     */
    public function get(string $id): mixed
    {
        // 1. Готовый экземпляр
        if (array_key_exists($id, $this->instances)) {
            return $this->instances[$id];
        }

        // 2. Алиас
        if (isset($this->aliases[$id])) {
            $id = $this->aliases[$id];
        }

        // 3. Factory
        if (isset($this->definitions[$id]) && is_callable($this->definitions[$id])) {
            $result = ($this->definitions[$id])($this);
            // кешируем результат factory
            $this->instances[$id] = $result;

            return $result;
        }

        // 4. Прямое значение
        if (array_key_exists($id, $this->definitions)) {
            return $this->definitions[$id];
        }

        throw new \Psr\Container\NotFoundException($id . ' not found in StubContainer');
    }

    /**
     * Проверить наличие сервиса.
     */
    public function has(string $id): bool
    {
        // алиас → resolved id
        if (isset($this->aliases[$id])) {
            $id = $this->aliases[$id];
        }

        return array_key_exists($id, $this->definitions)
            || array_key_exists($id, $this->instances);
    }

    /**
     * Получить все определения.
     *
     * @return array<string, mixed|callable>
     */
    public function getDefinitions(): array
    {
        return $this->definitions;
    }

    /**
     * Получить готовый экземпляр (если есть).
     */
    public function getInstance(string $id): mixed
    {
        return $this->instances[$id] ?? null;
    }

    /**
     * Проверить, есть ли готовый экземпляр.
     */
    public function hasInstance(string $id): bool
    {
        return array_key_exists($id, $this->instances);
    }

    // ---------- convenience static factories ----------

    /**
     * Пустой контейнер.
     */
    public static function empty(): self
    {
        return new self();
    }

    /**
     * Контейнер с определениями.
     *
     * @param array<string, mixed|callable> $definitions
     */
    public static function withDefinitions(array $definitions): self
    {
        return new self($definitions);
    }

    /**
     * Контейнер с моками (PHPUnit).
     *
     * @param class-string[] $classes  Классы для моков
     */
    public static function withMocks(array $classes, \PHPUnit\Framework\TestCase $testCase): self
    {
        $container = new self();

        foreach ($classes as $class) {
            $mock = $testCase->createMock($class);
            $container = $container->addInstance($class, $mock);
        }

        return $container;
    }

    /**
     * Контейнер с заглушками (PHPUnit).
     *
     * @param class-string[] $classes  Классы для stubs
     */
    public static function withStubs(array $classes, \PHPUnit\Framework\TestCase $testCase): self
    {
        $container = new self();

        foreach ($classes as $class) {
            $stub = $testCase->createStub($class);
            $container = $container->addInstance($class, $stub);
        }

        return $container;
    }
}
