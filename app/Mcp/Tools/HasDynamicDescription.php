<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Models\McpToolConfig;

trait HasDynamicDescription
{
    /**
     * Get the tool description. If a custom description is configured in the database,
     * it will be returned; otherwise, it falls back to the default description.
     */
    public function description(): string
    {
        $custom = McpToolConfig::getCustomDescription($this->name());

        if ($custom !== null && trim($custom) !== '') {
            return trim($custom);
        }

        return $this->defaultDescription();
    }

    /**
     * The default hardcoded description for this tool.
     */
    abstract public function defaultDescription(): string;
}
