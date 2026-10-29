<?php

declare(strict_types=1);

namespace Sys\Tests;

use PHPUnit\Framework\MockObject\Generator\Generator as MockGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub as PHPUnitStub;
use PHPUnit\Framework\TestCase;
use Sys\Template\Template;
use Sys\Template\TemplateInterface;

class StubTemplate
{
    private ?Template $real = null;

    private array $renderResults = [];

    private array $renderCalls = [];

    private array $globals = [];

    private array $functions = [];

    private ?string $defaultPath = null;

    private ?string $defaultRenderResult = null;

    private ?StubContainer $container = null;

    private ?TestCase $testCase = null;

    private ?object $mock = null;

    /**
     * @param TestCase|null $testCase  Если передан — создаст mock/createStub
     * @param StubContainer|null $container  Если передан — зарегистрирует mock в контейнере
     */
    public function __construct(
        ?TestCase $testCase = null,
        ?StubContainer $container = null
    ) {
        $this->testCase = $testCase;
        $this->container = $container;
    }

    // ---------------------------------------------------------------
    // Режимы работы
    // ---------------------------------------------------------------

    /**
     * Использовать реальный Template (с Twig и т.п.).
     */
    public function useReal(Template $template): self
    {
        $clone = clone $this;
        $clone->real = $template;

        return $clone;
    }

    /**
     * Использовать PHPUnit mock (expect-подход).
     */
    public function useMock(): self
    {
        $mock = $this->createMockObject(Template::class);
        $this->setupMockDefaults($mock);

        $this->registerMock($mock);

        return $this;
    }

    /**
     * Использовать PHPUnit stub (auto-respond подход).
     */
    public function useStub(): self
    {
        $stub = $this->createStubObject(Template::class);
        $this->setupMockDefaults($stub);

        $this->registerMock($stub);

        return $this;
    }

    /**
     * Зарегистрировать готовый mock/stub объект.
     */
    public function useMockObject(object $mock): self
    {
        $this->registerMock($mock);

        return $this;
    }

    // ---------------------------------------------------------------
    // Настройка render()
    // ---------------------------------------------------------------

    /**
     * Установить результат render() по умолчанию.
     */
    public function withDefaultResult(string $result): self
    {
        $clone = clone $this;
        $clone->defaultRenderResult = $result;

        return $clone;
    }

    /**
     * Установить результат render() для конкретного view.
     *
     * Пример: ->withRenderResult('index', '<html>home</html>')
     */
    public function withRenderResult(string $view, string $result): self
    {
        $clone = clone $this;
        $clone->renderResults[$view] = $result;

        return $clone;
    }

    /**
     * Установить результат render() для конкретного view + params.
     *
     * Пример: ->withRenderResultExact('user/profile', '<div>profile</div>', ['id' => 42])
     */
    public function withRenderResultExact(string $view, string $result, array $params = []): self
    {
        $key = $this->renderKey($view, $params);
        $clone = clone $this;
        $clone->renderResults[$key] = $result;

        return $clone;
    }

    /**
     * Установить результат render() через callback.
     *
     * Пример: ->withRenderCallback(fn($view, $params) => '<html>' . $view . '</html>')
     */
    public function withRenderCallback(callable $callback): self
    {
        $clone = clone $this;
        $clone->renderResults['__callback__'] = $callback;

        return $clone;
    }

    // ---------------------------------------------------------------
    // Ожидания (только для mock-режима)
    // ---------------------------------------------------------------

    /**
     * Ожидать вызов render() N раз.
     *
     * Пример: ->expectRenderTimes(1)
     */
    public function expectRenderTimes(int $times): self
    {
        if (!$this->testCase) {
            throw new \RuntimeException(
                'StubTemplate::expectRenderTimes() requires TestCase in constructor.'
            );
        }

        $this->renderCalls[] = ['times' => $times];

        return $this;
    }

    /**
     * Ожидать вызов render() с конкретным view.
     *
     * Пример: ->expectRenderView('index')
     */
    public function expectRenderView(string $view): self
    {
        if (!$this->testCase) {
            throw new \RuntimeException(
                'StubTemplate::expectRenderView() requires TestCase in constructor.'
            );
        }

        $this->renderCalls[] = ['view' => $view];

        return $this;
    }

    /**
     * Ожидать вызов render() с конкретными params.
     */
    public function expectRenderParams(array $params): self
    {
        if (!$this->testCase) {
            throw new \RuntimeException(
                'StubTemplate::expectRenderParams() requires TestCase in constructor.'
            );
        }

        $this->renderCalls[] = ['params' => $params];

        return $this;
    }

    // ---------------------------------------------------------------
    // Настройка других методов
    // ---------------------------------------------------------------

    /**
     * Подменить результат addGlobal().
     */
    public function withAddGlobal(string $name, mixed $value): self
    {
        $clone = clone $this;
        $clone->globals[$name] = $value;

        return $clone;
    }

    /**
     * Подменить результат addFunction().
     */
    public function withAddFunction(string $name, callable $callback): self
    {
        $clone = clone $this;
        $clone->functions[$name] = $callback;

        return $clone;
    }

    // ---------------------------------------------------------------
    // Создание объекта
    // ---------------------------------------------------------------

    /**
     * Создать и вернуть TemplateInterface.
     */
    public function create(): TemplateInterface
    {
        // Если есть mock/stub — возвращаем его
        if ($this->mock !== null) {
            return $this->mock;
        }

        // Если реальный Template — возвращаем его
        if ($this->real !== null) {
            return $this->real;
        }

        // По умолчанию — stub с дефолтными значениями
        return $this->createDefaultStub();
    }

    /**
     * Создать и вернуть StubContainer с зарегистрированным Template.
     */
    public function createContainer(): StubContainer
    {
        $template = $this->create();

        $container = $this->container ?? StubContainer::empty();

        return $container->addInstance(TemplateInterface::class, $template);
    }

    // ---------------------------------------------------------------
    // Вспомогательные
    // ---------------------------------------------------------------

    /**
     * Получить последний вызов render() (для ассертов).
     *
     * @return array{view: string, params: array}|null
     */
    public function getLastRenderCall(): ?array
    {
        if (empty($this->renderCalls)) {
            return null;
        }

        return end($this->renderCalls);
    }

    /**
     * Получить все вызовы render().
     *
     * @return array<array{view: string, params: array}>
     */
    public function getAllRenderCalls(): array
    {
        return $this->renderCalls;
    }

    // ---------------------------------------------------------------
    // Static factories
    // ---------------------------------------------------------------

    /**
     * Создать StubTemplate с mock-режимом.
     */
    public static function mock(?TestCase $testCase = null, ?StubContainer $container = null): self
    {
        $stub = new self($testCase, $container);
        $stub->useMock();

        return $stub;
    }

    /**
     * Создать StubTemplate с stub-режимом.
     */
    public static function stub(?TestCase $testCase = null, ?StubContainer $container = null): self
    {
        $stub = new self($testCase, $container);
        $stub->useStub();

        return $stub;
    }

    /**
     * Создать StubTemplate с реальным Template.
     */
    public static function real(Template $template): self
    {
        $stub = new self();
        $stub->useReal($template);

        return $stub;
    }

    /**
     * Создать StubTemplate с готовым mock-объектом.
     */
    public static function fromMock(object $mock, ?StubContainer $container = null): self
    {
        $stub = new self();
        $stub->useMockObject($mock);

        return $stub;
    }

    /**
     * Создать StubTemplate с результатом render().
     */
    public static function withResult(
        string $result,
        ?TestCase $testCase = null,
        ?StubContainer $container = null
    ): self {
        return self::stub($testCase, $container)->withDefaultResult($result);
    }

    // ---------------------------------------------------------------
    // Internal
    // ---------------------------------------------------------------

    /**
     * Создать PHPUnit MockObject (createMock-подход).
     */
    private function createMockObject(string $class): MockObject
    {
        return (new MockGenerator())->testDouble(
            $class,
            mockObject: true,
            markAsMockObject: true,
            callOriginalConstructor: false,
            callOriginalClone: false,
            cloneArguments: false,
            allowMockingUnknownTypes: false,
        );
    }

    /**
     * Создать PHPUnit Stub (createStub-подход).
     */
    private function createStubObject(string $class): PHPUnitStub
    {
        return (new MockGenerator())->testDouble(
            $class,
            mockObject: true,
            markAsMockObject: false,
            callOriginalConstructor: false,
            callOriginalClone: false,
            cloneArguments: false,
            allowMockingUnknownTypes: false,
            returnValueGeneration: true,
        );
    }

    private function registerMock(object $mock): void
    {
        $this->mock = $mock;
    }

    private function setupMockDefaults(object $mock): void
    {
        $defaultResult = $this->defaultRenderResult ?? '<html>stubbed</html>';

        $mock
            ->method('render')
            ->willReturn($defaultResult);

        $mock
            ->method('addGlobal')
            ->willReturnSelf();

        $mock
            ->method('addFunction')
            ->willReturnSelf();

        $mock
            ->method('path')
            ->willReturnSelf();
    }

    private function createDefaultStub(): TemplateInterface
    {
        $mock = new class($this->defaultRenderResult) implements TemplateInterface {
            public function __construct(private string $defaultResult) {}

            public function render(string $view, array $params = []): string
            {
                return $this->defaultResult;
            }

            public function addGlobal($name, $value): self { return $this; }
            public function addFunction(string $name, callable $callback): self { return $this; }
            public function getEngine() { return null; }
            public function path($path): self { return $this; }
            public function addPath($path, $namespace): self { return $this; }
        };

        return $mock;
    }

    private function renderKey(string $view, array $params): string
    {
        return $view . '::' . md5(json_encode($params));
    }
}
