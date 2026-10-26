<?php

namespace Az\Validation;

class Response implements ValidationResponseInterface
{
    private array $msgKeys = [];
    private array $messages = [];
    private array $errorData = [];

    public function __construct(private Message $msg) {}

    public function getResponse(array $data): array
    {
        $response = [];

        foreach ($data as $name => $value) {
            if (isset($this->errorData[$name])) {
                $response[$name] = $this->getError($name, $value);
            } else {
                $response[$name] = $this->getSuccess($value);
            }
        }

        return $response;
    }

    public function getApiResponse(array $data): array
    {
        $response = [];

        foreach ($data as $name => $value) {
            if (isset($this->errorData[$name])) {
                $response[] = $this->getApiError($name);
            }
        }

        return $response;
    }

    public function getMessage(string $key): string
    {
        return $this->msg->get($key);
    }

    public function setErrorData(string $key, string|callable $handler, array $params): void
    {
        $this->errorData[$key] = [
            'key' => $handler,
            'params' => $this->flatten($params),
        ];
    }

    public function setMsgKey(string $name, string $key): void
    {
        $this->msgKeys[$name] = $key;
    }

    public function setMessage(string $name, string $msg): void
    {
        $this->messages[$name] = $msg;
    }

    public function addMsgPath(string $path): void
    {
        $this->msg->addMsgPath($path);
    }

    public function setLang(string $lang): void
    {
        $this->msg->setLang($lang);
    }

    private function getSuccess(mixed $value): array
    {
        return [
            'status' => 'success',
            'value' => $value,
        ];
    }

    private function getApiError(string $key): array
    {
        $msg_key = $this->errorData[$key]['key'];
        $params = $this->errorData[$key]['params'];

        return [
            'key' => $key,
            'msg' => $this->getMsg($key, $msg_key, $params),
        ];
    }

    private function getError(string $key, $value): array
    {
        $msg_key = $this->errorData[$key]['key'];
        $params = $this->errorData[$key]['params'];

        return [
            'status' => 'error',
            'value' => $value,
            'msg' => $this->getMsg($key, $msg_key, $params),
        ];
    }

    private function getMsg(string $name, string|array $key, array $params): string
    {
        if (isset($this->messages[$name])) {
            return sprintf($this->messages[$name], ...$params);
        }

        if (isset($this->msgKeys[$name])) {
            $key = $this->msgKeys[$name];
        } elseif (is_array($key) && is_callable($key)) {
            $key = $key[1];
        }

        return $this->msg->get($key, $params);
    }

    private function flatten($array)
    {
        $result = [];

        array_walk_recursive($array, function ($value) use (&$result) {
            $result[] = $value;
        });

        return $result;
    }
}
