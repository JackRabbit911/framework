<?php

namespace Sys\Template;

use Az\Session\SessionInterface;

trait ComponentForm
{
    private function _render()
    {
        $data = $this->validate($this->attributes);
        $data = $this->prepare($data);
        return view($this->view, $data + $this->data);
    }

    private function prepare($attributes)
    {
        $pattern = '[\[|\]|\.]';
        $attrs = [];

        foreach ($attributes as $key => &$attribute) {
            $arr_keys = preg_split($pattern, $key, -1, PREG_SPLIT_NO_EMPTY);

            while (!empty($arr_keys)) {
                $last_key = array_pop($arr_keys);
                $res[$last_key] = $attribute;
                $attribute = $res;
                $res = [];
            }

            $attrs = array_merge_recursive($attrs, $attribute);
        }

        return $attrs;
    }

    private function validate($data)
    {
        $session = container()->get(SessionInterface::class);

        $validationResponse = $session->pull('validation');

        if ($validationResponse) {
            foreach ($data as $key => &$attribute) {
                if (isset($validationResponse[$key])) {
                    $attribute = $this->attributeValidation($attribute, $validationResponse[$key]);
                }
            }
        }

        return $data;
    }

    private function attributeValidation($attribute, $validationResponse)
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
                    || ($attribute['value'] == $validationResponse['value'])) {
                    $attribute['checked'] = true;
                } else {
                    $attribute['checked'] = false;
                }
            }

            return (array_replace($validationResponse, $attribute));           
        }

        foreach ($attribute as $k => &$attr) {
            $attr = $this->attributeValidation($attr, $validationResponse);
        }

        return $attribute;
    }
}
