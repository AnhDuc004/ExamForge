<?php

namespace App\Shared\DTOs;

abstract class BaseDTO
{
    public function __construct(array $attributes = [])
    {
        foreach ($attributes as $key => $value) {
            $this->{$key} = $value;
        }
    }
}
