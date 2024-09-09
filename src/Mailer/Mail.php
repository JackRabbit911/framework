<?php

namespace Sys\Mailer;

use Sys\Template\Template;
use Iterator;
use Sys\Trait\DataIterator;
use Sys\Trait\FromArray;
use Sys\Trait\ToArray;
use Sys\Trait\ToJson;
use Exception;
use JsonSerializable;
use Sys\Trait\FromJson;

class Mail implements Iterator, JsonSerializable
{
    use DataIterator;
    use FromArray;
    use ToArray;
    use ToJson;
    use FromJson;
    
    protected array $_data = [];
    public array $tplPath = [];
    public string $tplName;
    private string $draft;
    private array $data = [];

    #[\ReturnTypeWillChange]
    public function jsonSerialize(): mixed {
        return [
            'draft' => $this->draft,
            '_data' => $this->_data,
            'data' => $this->data,
        ];
    }

    public function tplPath(string $tplPath, string $namespace = ''): self
    {
        $this->tplPath = [$tplPath, $namespace];
        return $this;
    }

    public function tplName(string $tplName): self
    {
        $this->tplName = $tplName;
        return $this;
    }

    public function setDraft($file)
    {
        $this->draft = $file;
        return $this;
    }

    public function data(array $data = [])
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    public function to(string $email, string $name = '')
    {
        $this->_data['addAddress'] = (isset($this->_data['addAddress']))
        ? array_merge($this->_data['addAddress'], [[$email, $name]]) : [[$email, $name]];
        return $this;
    }

    public function from(string $email, string $name)
    {
        $this->_data['setFrom'] = [$email, $name];
        return $this;
    }

    public function subject(string $string)
    {
        $this->_data['Subject'] = $string;
        return $this;
    }

    public function render(): self
    {
        if (!isset($this->_data['Body']) && isset($this->tplPath) 
            && isset($this->tplName) && !empty($this->data)) {
            $path = APPPATH . $this->tplPath[0];
            $namespace = $this->tplPath[1] ?? null;

            $tpl = container()->get(Template::class);

            $tpl->getEngine()->getLoader()
                ->addPath($path, $namespace);

                

            $this->_data['Body'] = $tpl->render($this->tplName, $this->data);
        }

        return $this;
    }

    public function getBody()
    {
        return $this->_data['Body'];
    }

    public function __call($name, $arguments)
    {
        if (!isset($this->_data[$name])) {
            $this->_data[$name] = [];
        }

        $this->_data[$name] = $arguments;
        return $this;
    }

    public function &__get($key): mixed
    {
        $null = null;

        if (property_exists($this, $key)) {
            return $this->$key;
        } elseif (array_key_exists($key, $this->_data)) {
            return $this->_data[$key];
        } elseif (DISPLAY_ERRORS) {
            throw new Exception(sprintf('property "%s" is not defined', $key));
        } else {
            return $null;
        }
    }

    public function setData()
    {
        if (empty($this->draft)) {
            return;
        }
        
        $data = config($this->draft);

        foreach ($data as $key => $val) {
            if (property_exists($this, $key)) {
                if (is_array($val)) {
                    $this->$key = array_merge($this->$key, $val);
                } elseif (empty($this->$key)) {
                    $this->$key = $val;
                }
            } else {
                if (is_array($val)) {
                    $this->_data[$key] = (isset($this->_data[$key]))
                    ? array_merge($this->_data[$key], $val) : $val;
                } elseif (empty($this->_data[$key])) {
                    $this->_data[$key] = $val;
                }
            }
        }
    }
}
