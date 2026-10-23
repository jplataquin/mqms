<?php

namespace App\Protocols;

class ProtocolRegistry
{
    /**
     * Map of registered protocols (slug => class name).
     *
     * @var array<string, class-string<ProtocolInterface>>
     */
    protected static array $protocols = [
        LevelOneBudgetDiagnostic::SLUG => LevelOneBudgetDiagnostic::class,
    ];

    /**
     * Get all registered protocol classes.
     *
     * @return array<string, class-string<ProtocolInterface>>
     */
    public static function all(): array
    {
        return self::$protocols;
    }

    /**
     * Register a new protocol dynamically.
     *
     * @param string $slug
     * @param class-string<ProtocolInterface> $class
     * @return void
     */
    public static function register(string $slug, string $class): void
    {
        self::$protocols[$slug] = $class;
    }

    /**
     * Find a protocol class by slug or name.
     *
     * @param string $identifier
     * @return class-string<ProtocolInterface>|null
     */
    public static function get(string $identifier): ?string
    {
        $normalized = strtolower(trim(str_replace(['_', ' '], '-', $identifier)));

        if (isset(self::$protocols[$normalized])) {
            return self::$protocols[$normalized];
        }

        // Search by class constant or class name
        foreach (self::$protocols as $slug => $class) {
            if (
                (defined("{$class}::SLUG") && strtolower($class::SLUG) === $normalized) ||
                (defined("{$class}::NAME") && strtolower($class::NAME) === strtolower($identifier))
            ) {
                return $class;
            }
        }

        return null;
    }

    /**
     * Run a protocol by slug or name.
     *
     * @param string $identifier
     * @param array $params
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    public static function run(string $identifier, array $params = []): array
    {
        $class = self::get($identifier);

        if (!$class || !class_exists($class)) {
            throw new \InvalidArgumentException("Protocol '{$identifier}' is not registered or found.");
        }

        /** @var ProtocolInterface $instance */
        $instance = new $class();

        return $instance->run($params);
    }
}
