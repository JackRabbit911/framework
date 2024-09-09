<?php
namespace Sys\Template;

use Az\Session\SessionInterface;

class Form
{
    public array $attributes;
    private array $validationResponse;

    public function __construct(SessionInterface $session)
    {
        $this->validationResponse = $session->pull('validation') ?? [];
    }

    public function input(string $name, array $attributes = [])
    {
        if (strpos($name, '[]') !== false) {
            $key = str_replace('[]', '', $name);
            $attributes['multiple'] = true;
        } else {
            $key = $name;
        }
        
        $this->attributes[$key] = $attributes;
        
        $this->attributes[$key]['id'] = $attributes['id'] ?? $key;
        $this->attributes[$key]['name'] = $name;
        $this->attributes[$key]['message'] = $this->validationResponse[$key]['msg'] ?? $attributes['message'] ?? '';
        $this->attributes[$key]['label'] = $attributes['label'] ?? ucfirst(preg_replace('/^(.*?)(\[)(.*?)(\])$/', '$3', $key));
        $this->attributes[$key]['type'] = $attributes['type'] ?? 'text';

        if (!empty($this->validationResponse[$key])) {
            if (isset($this->validationResponse[$key]['value']) && is_array($this->validationResponse[$key]['value'])) {
                $this->attributes[$key]['value'] = $attributes['value'] ?? '';
            } else {
                $this->attributes[$key]['value'] = $this->validationResponse[$key]['value'] ?? '';
            }
        } else {
            $this->attributes[$key]['value'] = $attributes['value'] ?? '';
        }

        if (isset($attributes['selected'])) {
            $this->attributes[$key]['selected'] = $attributes['selected'];
        }

        if (isset($attributes['options'])) {
            $this->attributes[$key]['options'] = $attributes['options'];
        }

        if (isset($attributes['size'])) {
            $this->attributes[$key]['size'] = $attributes['size'];
        }

        if (isset($attributes['rows'])) {
            $this->attributes[$key]['rows'] = $attributes['rows'];
        }
        
        if (isset($attributes['disabled']) && ($this->isTrue($attributes['disabled']) || trim($attributes['disabled']) == 'disabled')) {
            $this->attributes[$key]['disabled'] = 'disabled';
        }

        if (isset($attributes['readonly'])) {
            $this->attributes[$key]['readonly'] = 'readonly';
        }

        if (isset($attributes['autofocus'])) {
            $this->attributes[$key]['autofocus'] = 'autofocus';
        }

        if (isset($attributes['multiple'])) {
            $this->attributes[$key]['multiple'] = 'multiple';
        }

        if (isset($attributes['accept'])) {
            $this->attributes[$key]['accept'] = 'accept=' . $attributes['accept'];
        }

        if (isset($attributes['required'])) {
            $this->attributes[$key]['required'] = 'required';
        }

        $this->attributes[$key]['ok'] = $this->validationResponse[$key]['status'] ?? null;

        return $this->attributes[$key];
    }

    public function checkbox($name, $attributes) {       
        $key = str_replace('[]', '', $name);

        $this->input($name, $attributes);

        $attributes['checked'] = $attributes['checked'] ?? '';

        if (isset($this->validationResponse[$key]['value']) && !empty($this->validationResponse[$key]['value'])) {
            if (is_array($this->validationResponse[$key]['value'])) {
                if (in_array($this->attributes[$key]['value'], $this->validationResponse[$key]['value'])) {
                    $this->attributes[$key]['checked'] = 'on';
                } else {
                    $this->attributes[$key]['checked'] = '';
                }
            } else {
                if ($this->attributes[$key]['value'] == $this->validationResponse[$key]['value'] 
                    || $this->isTrue($this->validationResponse[$key]['value'])) {
                    $this->attributes[$key]['checked'] = 'on';
                } else {
                    $this->attributes[$key]['checked'] = '';
                }
            }
        } else {
            if (isset($this->validationResponse) && !empty($this->validationResponse)) {
                $this->attributes[$key]['checked'] = '';
            } else {
                if (is_array($attributes['checked']) && in_array($this->attributes[$key]['value'], $attributes['checked']) 
                    || !is_array($attributes['checked']) && !empty($attributes['checked']) && $this->attributes[$key]['value'] == $attributes['checked'] 
                    || $this->isTrue($attributes['checked'])) {
                    $this->attributes[$key]['checked'] = 'on';
                } else {
                    $this->attributes[$key]['checked'] = '';
                }
            }
        }

        $this->attributes[$key]['value'] = $attributes['value'] ?? '';
        $this->attributes[$key]['id'] = (isset($this->attributes[$key]['value']) && strpos($name, '[]') !== false) 
            ? $key . '-' . $this->attributes[$key]['value'] : $key;
        return $this->attributes[$key];
    }

    public function select($name, $option)
    {
        $key = str_replace('[]', '', $name);

        if (!empty($this->validationResponse[$key]['value'])) {
            if (is_array($this->validationResponse[$key]['value'])) {
                if (in_array($option['value'], $this->validationResponse[$key]['value'])) {
                    $selected = ' selected';
                } else {
                    $selected = '';
                }
            } else {
                if ($option['value'] == $this->validationResponse[$key]['value']) {
                    $selected = ' selected';
                } else {
                    $selected = '';
                }
            }
        } else {
            if (isset($this->validationResponse) && !empty($this->validationResponse)) {
                $selected = '';
            } else {
                $selected = (isset($option['selected']) && $this->isTrue($option['selected'])) ? ' selected' : '';
            }
        }

        return $selected;
    }

    public function isTrue($attr) {
        $yes = ["1", "yes", "on", "true", "checked", 1];
        return (in_array($attr, $yes) || $attr === true) ? true : false;
    }

    public function helpertext($name)
    {
        return [
            'ok' => $this->attributes[$name]['ok'] ?? null,
            'message' => $this->attributes[$name]['message'] ?? '',
            'value' => $this->attributes[$name]['value'] ?? '',
        ];
    }
}
