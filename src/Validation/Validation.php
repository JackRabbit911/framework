<?php

namespace Az\Validation;

class Validation
{
    public array $data = [];
    private array $rules = [];
    private array $check = [];
    private ?string $userHandler = null;

    public function __construct(private Response $response){}

    public function rule($name, $handler, ...$params)
    {
        $value = [
            'handler' => $handler,
            'params' => $params,
        ];

        if (!isset($this->rules[$name])) {
            $this->rules[$name] = [$value];
        } else {
            $this->rules[$name][] = $value;
        }

        return $this;
    }

    public function reset()
    {
        $this->rules = [];
        $this->data = [];
        $this->check = [];
    }

    public function getRules(): array
    {
        return $this->rules;
    }

    public function check($data, $files = [], $dot_notation = false): bool
    {
        $this->data = $dot_notation ?
            flattenDot($data + $files) :
            flattenBracket($data + $files);

        foreach (array_keys(array_diff_key($this->rules, $this->data)) as $key) {
            $this->data[$key] = null;
        }

        $valid = new ValidationValue(
            $this->response,
            $this->rules,
            $this->data,
            $this->userHandler,
        );

        foreach ($this->data as $key => $value) {
            if (isset($this->rules[$key])) {
                $this->check[$key] = $valid->check($value, $key);
            }
        }

        return in_array(false, $this->check) ? false : true;
    }

    public function getResponse($is_api = false)
    {
        return $is_api ?  $this->response->getApiResponse($this->data) :
            $this->response->getResponse($this->data, array_keys($this->rules));
    }

    public function getMessage($key)
    {
        return $this->response->getMessage($key);
    }

    public function setUserHandler(string $className)
    {
        $this->userHandler = $className;
        return $this;
    }

    public function setMsgKey($name, $key)
    {
        $this->response->setMsgKey($name, $key);
        return $this;
    }

    public function addMsgPath($path)
    {
        $this->response->addMsgPath($path);
        return $this;
    }

    public function setMessage($name, $msg)
    {
        $this->response->setMessage($name, $msg);
        return $this;
    }

    public function setLang($lang)
    {
        $this->response->setLang($lang);
        return $this;
    }
}

require_once 'library.php';
