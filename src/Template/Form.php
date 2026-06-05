<?php

declare(strict_types=1);

namespace Sys\Template;

use Sys\Template\Component;
use Sys\Helper\Facade\Arr;
use Az\Session\SessionInterface;

abstract class Form extends Component
{
    public function render(array $data = [], ?array $validation_response = null): string
    {
        $this->data = array_replace_recursive($this->data, $data);
        $this->validate($validation_response);

        return parent::render();
    }

    private function validate(?array $validationResponse = null): void
    {
        if (!$validationResponse) {
            $session = container()->get(SessionInterface::class);
            $validationResponse = $session->pull('validation');
        }

        if ($validationResponse) {
            foreach ($this->data as $key => &$attribute) {
                if (is_array($attribute) && array_key_exists('name', $attribute) && isset($validationResponse[$attribute['name']])) {
                    $attribute = $this->attributeValidation($attribute, $validationResponse[$attribute['name']]);
                }
            }
        }

        $this->data = Arr::unflatten($this->data);
    }

    private function attributeValidation(array $attribute, array $validationResponse): array
    {
        if (isset($attribute['type'])) {
            if ($attribute['type'] === 'select' && !empty($validationResponse['value'])) {
                foreach ($attribute['options'] as &$option) {
                    if ($option['value'] == $validationResponse['value']) {
                        $option['selected'] = true;
                    } else {
                        $option['selected'] = false;
                    }
                }
            } elseif ($attribute['type'] === 'checkbox' && !empty($validationResponse['value'])) {
                if ((is_array($validationResponse['value'])
                        && in_array($attribute['value'], $validationResponse['value']))
                    || $this->isTrue($validationResponse['value'])
                ) {
                    $attribute['checked'] = true;
                } else {
                    $attribute['checked'] = false;
                }
            }
        }

        if (array_is_list($attribute)) {
            foreach ($attribute as &$attr) {
                $attr = $this->attributeValidation($attr, $validationResponse);
            }

            return $attribute;
        }

        return array_replace($attribute, $validationResponse);
    }
}
