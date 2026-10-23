<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\MaterialItem;
use Illuminate\Support\Facades\Auth;

class MaterialGroup extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'material_groups';

    protected $fillable = [
        'name',
        'specification_template',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function Items(): HasMany
    {
        return $this->hasMany(MaterialItem::class);
    }

    /**
     * Check if the group has a configured specification template.
     *
     * @return bool
     */
    public function hasSpecificationTemplate(): bool
    {
        return !empty(trim((string) $this->specification_template));
    }

    /**
     * Parse the specification_template into an array of segments.
     *
     * @return array
     */
    public function parseSpecificationTemplate(): array
    {
        $template = $this->specification_template;
        if ($template === null || trim((string) $template) === '') {
            return [];
        }

        $segments = [];
        $pattern = '/\[([^:\]\r\n]+)(?::(number|text))?\]/';
        $lastOffset = 0;

        if (preg_match_all($pattern, $template, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
            foreach ($matches as $match) {
                $fullMatchText = $match[0][0];
                $fullMatchOffset = $match[0][1];

                // Static text before this token
                if ($fullMatchOffset > $lastOffset) {
                    $staticText = substr($template, $lastOffset, $fullMatchOffset - $lastOffset);
                    $segments[] = [
                        'type' => 'static',
                        'value' => $staticText,
                    ];
                }

                $label = trim($match[1][0]);
                $datatype = isset($match[2][0]) && strtolower($match[2][0]) === 'number' ? 'number' : 'text';

                $segments[] = [
                    'type' => 'variable',
                    'label' => $label,
                    'datatype' => $datatype,
                ];

                $lastOffset = $fullMatchOffset + strlen($fullMatchText);
            }
        }

        // Remaining static text
        if ($lastOffset < strlen($template)) {
            $segments[] = [
                'type' => 'static',
                'value' => substr($template, $lastOffset),
            ];
        }

        return $segments;
    }

    /**
     * Get regular expression pattern to validate against specification_template.
     *
     * @return string|null
     */
    public function getSpecificationRegex(): ?string
    {
        $segments = $this->parseSpecificationTemplate();
        if (empty($segments)) {
            return null;
        }

        $pattern = '^';
        $total = count($segments);
        for ($i = 0; $i < $total; $i++) {
            $segment = $segments[$i];
            if ($segment['type'] === 'static') {
                $pattern .= preg_quote($segment['value'], '/');
            } elseif ($segment['type'] === 'variable') {
                if ($segment['datatype'] === 'number') {
                    $pattern .= '(\d+(?:\.\d+)?)';
                } else {
                    // If it's the last segment or followed by static, match appropriately
                    $pattern .= ($i === $total - 1) ? '([^\r\n]+)' : '([^\r\n]+?)';
                }
            }
        }
        $pattern .= '$';

        return '/' . $pattern . '/u';
    }

    /**
     * Validate a specification string against the group template.
     *
     * @param string|null $specification
     * @return bool
     */
    public function validateSpecification(?string $specification): bool
    {
        if (!$this->hasSpecificationTemplate()) {
            return false;
        }

        if ($specification === null || trim($specification) === '') {
            return false;
        }

        $regex = $this->getSpecificationRegex();
        if (!$regex) {
            return false;
        }

        return preg_match($regex, trim($specification)) === 1;
    }
}
