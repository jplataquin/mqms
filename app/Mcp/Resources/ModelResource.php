<?php

namespace App\Mcp\Resources;

use Laravel\Mcp\Server\Resource;

class ModelResource extends Resource
{
    protected string $description = 'A description of what this resource contains.';

    /**
     * Return the resource contents.
     */
    public function read(): string
    {
        return 'Implement resource retrieval logic here';
    }
}
