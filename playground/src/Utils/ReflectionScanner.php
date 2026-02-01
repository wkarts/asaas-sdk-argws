<?php

declare(strict_types=1);

namespace Playground\Utils;

use ReflectionClass;
use ReflectionMethod;

final class ReflectionScanner
{
    private const SDK_PREFIX = 'Asaas\\Sdk\\';

    public function __construct(private string $basePath) {}

    /**
     * @return array{classes: string[], services: string[], methods: array<string, array<int, array<string, mixed>>>}
     */
    public function catalog(): array
    {
        $classes = $this->discoverClasses();
        $services = array_values(array_filter($classes, fn (string $class): bool =>
            str_starts_with($class, self::SDK_PREFIX . 'Service\\') && str_ends_with($class, 'Service')
        ));

        $methods = [];
        foreach ($classes as $class) {
            if (!class_exists($class)) {
                continue;
            }
            $reflection = new ReflectionClass($class);
            $methods[$class] = array_map(
                fn (ReflectionMethod $method) => $this->formatMethod($method),
                array_filter(
                    $reflection->getMethods(ReflectionMethod::IS_PUBLIC),
                    fn (ReflectionMethod $method): bool => !$method->isConstructor() && !$method->isDestructor()
                )
            );
        }

        sort($classes);
        sort($services);

        return [
            'classes' => $classes,
            'services' => $services,
            'methods' => $methods,
        ];
    }

    /**
     * @return string[]
     */
    private function discoverClasses(): array
    {
        $classes = [];
        $classmapPath = $this->basePath . '/vendor/composer/autoload_classmap.php';
        if (is_file($classmapPath)) {
            $classmap = require $classmapPath;
            $classes = array_keys(array_filter(
                $classmap,
                fn (string $path, string $class): bool => str_starts_with($class, self::SDK_PREFIX),
                ARRAY_FILTER_USE_BOTH
            ));
        }

        if (!empty($classes)) {
            return array_values(array_unique($classes));
        }

        $psr4Path = $this->basePath . '/vendor/composer/autoload_psr4.php';
        if (!is_file($psr4Path)) {
            return [];
        }

        $psr4 = require $psr4Path;
        $prefixPaths = $psr4[self::SDK_PREFIX] ?? [];

        foreach ((array) $prefixPaths as $path) {
            $classes = array_merge($classes, $this->scanDirectory($path));
        }

        return array_values(array_unique($classes));
    }

    /**
     * @return string[]
     */
    private function scanDirectory(string $path): array
    {
        $classes = [];
        if (!is_dir($path)) {
            return [];
        }

        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
        foreach ($iterator as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $contents = file_get_contents($file->getPathname());
            if ($contents === false) {
                continue;
            }

            if (!preg_match('/namespace\s+([^;]+);/m', $contents, $nsMatch)) {
                continue;
            }

            if (!preg_match('/class\s+(\w+)/m', $contents, $classMatch)) {
                continue;
            }

            $class = trim($nsMatch[1]) . '\\' . trim($classMatch[1]);
            if (str_starts_with($class, self::SDK_PREFIX)) {
                $classes[] = $class;
            }
        }

        return $classes;
    }

    /**
     * @return array<string, mixed>
     */
    private function formatMethod(ReflectionMethod $method): array
    {
        $params = [];
        foreach ($method->getParameters() as $parameter) {
            $type = $parameter->getType();
            $params[] = [
                'name' => $parameter->getName(),
                'type' => $type ? $type->getName() : null,
                'optional' => $parameter->isOptional(),
                'default' => $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null,
            ];
        }

        return [
            'name' => $method->getName(),
            'params' => $params,
        ];
    }
}
