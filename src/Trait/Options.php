<?php

namespace Sys\Trait;

trait Options
{
    public function options(?string $configFile = null): void
    {
        if (!$configFile) {
            $configFile = strtolower(basename(str_replace('\\', '/', __CLASS__)));
        }

        $this->setOptions(config($configFile));
    }

    public function setOptions(array $options): void
    {
        foreach ($options as $key => $value) {     
            if (is_array($value)) {
                $this->$key = array_replace_recursive($this->$key, $value);
            } else {
                if (isset(static::$$key)) {
                    static::$$key = $value;
                } else {
                    $this->$key = $value;
                }
            }        
        }
    }
}
