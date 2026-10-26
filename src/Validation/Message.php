<?php

namespace Az\Validation;

final class Message
{
    public array $keys = [];
    private array $msgPath = ['messages'];
    private array $messages = [];
    private string $lang = 'en';

    public function addMsgPath(string $path): void
    {
        array_push($this->msgPath, $path);
    }

    public function setLang($lang)
    {
        $this->lang = $lang;
    }

    public function get(string $key, array $params = [], string $default = 'default'): string
    {
        $this->setMessages();

        $message = $this->messages[$key] ?? $this->messages[$default] ?? 'Invalid data';

        return $this->sprintf($message, $params);
    }

    public function setMsgKey($name, $key)
    {
        $this->keys[$name] = $key;
    }

    private function setMessages()
    {
        foreach ($this->msgPath as $path) {
            $file = trim($path, '/') . '/' . $this->lang . '.php';
            $this->messages = array_replace($this->messages, require $file);
        }
    }

    private function sprintf($str, $format)
    {
        if (str_contains($str, '%a')) {
            $replace = '[' . join(', ', $format) . ']';
            $str = str_replace('%a', $replace, $str);
        }

        return vsprintf($str, $format);
    }
}
