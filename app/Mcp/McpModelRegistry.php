<?php

declare(strict_types=1);

namespace App\Mcp;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class McpModelRegistry
{
    /**
     * Map of normalized model keys to their fully qualified class names.
     */
    protected static array $models = [
        'accomplishment' => \App\Models\Accomplishment::class,
        'comment' => \App\Models\Comment::class,
        'component' => \App\Models\Component::class,
        'component_item' => \App\Models\ComponentItem::class,
        'contract_item' => \App\Models\ContractItem::class,
        'coupon' => \App\Models\Coupon::class,
        'direct_material_canvass' => \App\Models\DirectMaterialCanvass::class,
        'direct_material_quantity_request' => \App\Models\DirectMaterialQuantityRequest::class,
        'direct_material_quantity_request_item' => \App\Models\DirectMaterialQuantityRequestItem::class,
        'direct_purchase_order' => \App\Models\DirectPurchaseOrder::class,
        'direct_purchase_order_item' => \App\Models\DirectPurchaseOrderItem::class,
        'direct_purchase_order_received' => \App\Models\DirectPurchaseOrderReceived::class,
        'direct_purchase_order_received_item' => \App\Models\DirectPurchaseOrderReceivedItem::class,
        'manpower_registry' => \App\Models\ManpowerRegistry::class,
        'material_canvass' => \App\Models\MaterialCanvass::class,
        'material_group' => \App\Models\MaterialGroup::class,
        'material_item' => \App\Models\MaterialItem::class,
        'material_quantity' => \App\Models\MaterialQuantity::class,
        'material_quantity_request' => \App\Models\MaterialQuantityRequest::class,
        'material_quantity_request_item' => \App\Models\MaterialQuantityRequestItem::class,
        'payment_term' => \App\Models\PaymentTerm::class,
        'project' => \App\Models\Project::class,
        'purchase_order' => \App\Models\PurchaseOrder::class,
        'purchase_order_item' => \App\Models\PurchaseOrderItem::class,
        'purchase_order_received' => \App\Models\PurchaseOrderReceived::class,
        'purchase_order_received_item' => \App\Models\PurchaseOrderReceivedItem::class,
        'section' => \App\Models\Section::class,
        'supplier' => \App\Models\Supplier::class,
        'unit' => \App\Models\Unit::class,
        'user' => \App\Models\User::class,
    ];

    /**
     * Explicit list of strictly forbidden models.
     */
    protected static array $forbidden = [
        'role',
        'roles',
        'access_code',
        'access_codes',
        'role_access_code',
        'role_access_codes',
        'user_role',
        'user_roles',
        'api_credential',
        'api_credentials',
        'apicredential',
        'apicredentials',
    ];

    /**
     * Normalize model name into snake_case.
     */
    public static function normalize(string $name): string
    {
        return Str::snake(str_replace('-', '_', trim($name)));
    }

    /**
     * Check if a given model name is allowed.
     */
    public static function isAllowed(string $name): bool
    {
        $normalized = self::normalize($name);

        if (in_array($normalized, self::$forbidden, true)) {
            return false;
        }

        return isset(self::$models[$normalized]);
    }

    /**
     * Get the Eloquent Model class for a given name.
     */
    public static function getModelClass(string $name): ?string
    {
        $normalized = self::normalize($name);

        if (! self::isAllowed($normalized)) {
            return null;
        }

        return self::$models[$normalized] ?? null;
    }

    /**
     * Get array of all allowed model keys.
     */
    public static function getAllowedModelKeys(): array
    {
        return array_keys(self::$models);
    }

    /**
     * Sanitize a model instance or array of attributes, removing any sensitive fields.
     */
    public static function sanitize(Model|array $data): array
    {
        $array = $data instanceof Model ? $data->toArray() : $data;

        // Strip password and any auth/security tokens
        unset(
            $array['password'],
            $array['remember_token'],
            $array['secret_key'],
            $array['api_key'],
            $array['key'],
            $array['secret']
        );

        return $array;
    }
}
