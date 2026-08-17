<?php

namespace Sys\Request;

use Exception;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Promise\Utils;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class HttpPool
{
    /** @var ServiceClientInterface[] */
    private array $clients = [];

    // Глобальные заголовки и куки для всего пула
    private array $globalHeaders = [];
    private array $globalCookies = [];

    /**
     * Навесить заголовки на весь пул запросов
     */
    public function headers(array $headers): self
    {
        $this->globalHeaders = array_merge($this->globalHeaders, $headers);
        return $this;
    }

    /**
     * Навесить куки на весь пул запросов
     */
    public function cookies(array $cookies): self
    {
        $this->globalCookies = array_merge($this->globalCookies, $cookies);
        return $this;
    }

    /**
     * Добавление запроса в пул
     */
    public function url(string $url): ServiceClientInterface
    {
        $client = Http::client($url);
        $this->clients[] = $client;
        return $client;
    }

    /**
     * Выполнение всех накопленных запросов
     */
    public function execute(): array
    {
        $promises = [];
        $responses = [];

        // Шаг 1: Устанвливаем заголовки для всех запросов пула
        foreach ($this->clients as $client) {
            if (!empty($this->globalHeaders)) {
                $client->headers($this->globalHeaders);
            }

            if (!empty($this->globalCookies)) {
                $client->cookies($this->globalCookies);
            }
        }

        // Шаг 2: Запускаем внешние запросы асинхронно
        foreach ($this->clients as $key => $client) {
            if ($client instanceof ExternalServiceClient) {
                $promise = $client->sendAsync();
                // Навешиваем обработчики успеха и ошибки прямо на промис
                $promises[$key] = $promise->then(
                    // 1. On Fulfilled
                    function (ResponseInterface $response) use ($client) {
                        if ($client->formatSuccess && is_callable($client->formatSuccess)) {
                            return ($client->formatSuccess)($response);
                        }

                        return $this->formatSuccess($response);
                    },
                    // 2. On Rejected
                    function (Throwable $exception) use ($client) {
                        if ($client->formatError && is_callable($client->formatError)) {
                            return ($client->formatError)($exception);
                        }

                        return $this->formatError($exception);
                    }
                );
            }
        }

        // Шаг 3: Пока выполняются внешние запросы, запускаем очередь внутренних запросов
        foreach ($this->clients as $key => $client) {
            if ($client instanceof InternalServiceClient) {
                try {
                    $response =  $client->send();

                    if ($response->getStatusCode() >= 400) {
                        $exception = RequestException::create($client->getServerRequest(), $response);

                        if ($client->formatError && is_callable($client->formatError)) {
                            $responses[$key] = ($client->formatError)($exception);
                        } else {
                            $responses[$key] = $this->formatError($exception);

                        }
                    } else {
                        if ($client->formatSuccess && is_callable($client->formatSuccess)) {
                            $responses[$key] = ($client->formatSuccess)($response);
                        } else {
                            $responses[$key] = $this->formatSuccess($response);
                        }
                    }

                } catch (Throwable $exception) {
                    if ($client->formatError && is_callable($client->formatError)) {
                        $responses[$key] = ($client->formatError)($exception);
                    }

                    $responses[$key] = $this->formatError($exception);
                }
            }
        }

        // Шаг 4: Безопасно дожидаемся завершения всех внешних запросов
        if (!empty($promises)) {
            $externalResponses = Utils::unwrap($promises);

            foreach ($externalResponses as $key => $formattedResult) {
                $responses[$key] = $formattedResult;
            }
        }

        // Шаг 5: Восстанавливаем исходный порядок запросов
        ksort($responses);

        return $responses;
    }

    private function formatSuccess(ResponseInterface $response): array|string|null
    {
        $body = $response->getBody()->getContents();
        $data = json_decode($body, true);

        return ($data !== null && json_last_error() === JSON_ERROR_NONE) ? $data : $body;
    }

    private function formatError(Throwable $exception)
    {
        $response = $exception->getResponse();
        $body = $response->getBody()->getContents();
        $data = json_decode($body, true);
        $error = ($data !== null && json_last_error() === JSON_ERROR_NONE) ? $data : null;

        if ($error) {
            return $error;
        }

        $code = $response->getStatusCode();
        $message = $response->getReasonPhrase();

        return [
            // 'error' => [
            'code' => $statusCode,
            'message' => $message,
            // ]
        ];

        // return [
        //     'success' => false,
        //     'error' => [
        //         'code' => $code,
        //         'message' => $message
        //     ]
        // ];
    }
}
