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
     * Supports escaping square brackets (\[ and \]) and backslashes (\\).
     *
     * @return array
     */
    public function parseSpecificationTemplate(): array
    {
        $template = $this->specification_template;
        if ($template === null || trim((string) $template) === '') {
            return [];
        }

        $template = (string) $template;
        $len = strlen($template);
        $i = 0;
        $segments = [];
        $currentStatic = '';

        $unescapeTemplate = function (string $str): string {
            return preg_replace_callback('/\\\\(.)/s', function ($m) {
                if ($m[1] === '[' || $m[1] === ']' || $m[1] === '\\' || $m[1] === ':') {
                    return $m[1];
                }
                return $m[0];
            }, $str);
        };

        $pushStatic = function (string $text) use (&$segments): void {
            if ($text === '') {
                return;
            }
            $count = count($segments);
            if ($count > 0 && $segments[$count - 1]['type'] === 'static') {
                $segments[$count - 1]['value'] .= $text;
            } else {
                $segments[] = [
                    'type' => 'static',
                    'value' => $text,
                ];
            }
        };

        while ($i < $len) {
            $char = $template[$i];

            if ($char === '\\' && $i + 1 < $len) {
                $next = $template[$i + 1];
                if ($next === '[' || $next === ']' || $next === '\\' || $next === ':') {
                    $currentStatic .= $next;
                } else {
                    $currentStatic .= '\\' . $next;
                }
                $i += 2;
                continue;
            }

            if ($char === '[') {
                $j = $i + 1;
                $foundClose = false;
                $hasNestedOpen = false;

                while ($j < $len) {
                    $c = $template[$j];
                    if ($c === "\r" || $c === "\n") {
                        break;
                    }
                    if ($c === '\\' && $j + 1 < $len) {
                        $j += 2;
                        continue;
                    }
                    if ($c === '[') {
                        $hasNestedOpen = true;
                        break;
                    }
                    if ($c === ']') {
                        $foundClose = true;
                        break;
                    }
                    $j++;
                }

                if ($foundClose && !$hasNestedOpen) {
                    $content = substr($template, $i + 1, $j - ($i + 1));

                    // Find last unescaped colon
                    $colonPos = false;
                    $cLen = strlen($content);
                    $k = 0;
                    while ($k < $cLen) {
                        if ($content[$k] === '\\' && $k + 1 < $cLen) {
                            $k += 2;
                            continue;
                        }
                        if ($content[$k] === ':') {
                            $colonPos = $k;
                        }
                        $k++;
                    }

                    $isValid = true;
                    $label = '';
                    $datatype = 'text';

                    if ($colonPos !== false) {
                        $typePart = strtolower(trim(substr($content, $colonPos + 1)));
                        if ($typePart === 'number' || $typePart === 'text') {
                            $datatype = $typePart;
                            $label = $unescapeTemplate(trim(substr($content, 0, $colonPos)));
                        } else {
                            $isValid = false;
                        }
                    } else {
                        $label = $unescapeTemplate(trim($content));
                    }

                    if ($isValid && strlen($label) > 0) {
                        if (strlen($currentStatic) > 0) {
                            $pushStatic($currentStatic);
                            $currentStatic = '';
                        }
                        $segments[] = [
                            'type' => 'variable',
                            'label' => $label,
                            'datatype' => $datatype,
                        ];
                        $i = $j + 1;
                        continue;
                    }
                }

                $currentStatic .= '[';
                $i++;
                continue;
            }

            $currentStatic .= $char;
            $i++;
        }

        if (strlen($currentStatic) > 0) {
            $pushStatic($currentStatic);
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
