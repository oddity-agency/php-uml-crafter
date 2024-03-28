<?php

namespace WongDoody\PhpUmlCrafter\Services;

use Illuminate\Database\Eloquent\Model;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionProperty;
use ReflectionUnionType;

class ScannerService
{
    /**
     * @throws ReflectionException
     */
    public function execute(string $projectPath, array $excludeFiles, array $excludeDirectories, ?bool $includeTraitsAndInterfaces = false): array
    {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($projectPath)
        );

        $classes = [];

        $excludeDirectories = array_map(fn($v) => $projectPath . DIRECTORY_SEPARATOR . $v, $excludeDirectories);
        $excludeFiles = array_map(fn($v) => $projectPath . DIRECTORY_SEPARATOR . $v, $excludeFiles);

        foreach ($files as $file) {
            if ($file->isDir() || $file->getExtension() !== 'php' || $this->shouldExclude(
                    $file->getPathname(), $excludeFiles, $excludeDirectories
                )) {
                continue;
            }

            $filePath = $file->getRealPath();

            $namespace = $this->getNamespace($filePath);
            $class = $this->getClass($filePath, $namespace);

            if ($class && class_exists($class)) {
                $classes[$class] = $this->getClassInfo($class, $includeTraitsAndInterfaces);
            }
        }

        return $classes;
    }

    private function shouldExclude($filePath, $excludeFiles, $excludeDirectories): bool
    {
        foreach ($excludeFiles as $excludeFile) {
            if ($filePath === $excludeFile) {
                return true;
            }
        }

        foreach ($excludeDirectories as $excludeDir) {
            if (str_starts_with($filePath, $excludeDir)) {
                return true;
            }
        }

        return false;
    }

    private function getNamespace($filePath): ?string
    {
        $content = file_get_contents($filePath);
        $namespaceRegex = '/namespace (.*?);/';
        preg_match($namespaceRegex, $content, $matches);

        return $matches[1] ?? null;
    }

    private function getClass($filePath, $namespace): ?string
    {
        $content = file_get_contents($filePath);
        $classRegex = '/\bclass\s+([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)/';
        preg_match($classRegex, $content, $matches);

        return isset($matches[1]) ? $namespace . '\\' . $matches[1] : null;
    }

    /**
     * @throws ReflectionException
     */
    private function getClassInfo($class, ?bool $includeTraitsAndInterfaces = false): array
    {
        $reflection = new ReflectionClass($class);
        $methods = [];
        $parentClass = $reflection->getParentClass();

        foreach ($reflection->getMethods() as $method) {
            $methodName = $method->getName();
            $declaringClass = $method->getDeclaringClass()->getName();

            if ($declaringClass === $class && !$this->isMethodFromTrait($method)) {
                $methodInfo = [
                    'parameters' => $this->getMethodParameters($method),
                    'return_type' => $this->getReturnType($method),
                    'visibility' => $this->getMethodVisibility($method),
                ];

                $methods[$methodName] = $methodInfo;
            }
        }

        $attributes = $this->isModelClass($class) ? $this->getAttributes($class) : [];

        return [
            'methods' => $methods,
            'attributes' => $attributes,
            'relations' => [
                'parent_class' => $parentClass ? $parentClass->getName() : null,
                'traits_and_interfaces' => $this->getTraitsAndInterfaces($reflection, $includeTraitsAndInterfaces),
            ],
        ];
    }

    private function getMethodVisibility(ReflectionMethod $method): string
    {
        if ($method->isPublic()) {
            return '+';
        } elseif ($method->isProtected()) {
            return '#';
        } elseif ($method->isPrivate()) {
            return '-';
        }

        return 'unknown'; // Handle other visibility cases as needed
    }

    /**
     * @throws ReflectionException
     */
    private function getAttributes($class): array
    {
        $reflection = new ReflectionClass($class);
        $attributes = [];

        foreach ($reflection->getProperties() as $property) {
            $declaringClass = $property->getDeclaringClass()->getName();

            if ($declaringClass === $class && !$this->isPropertyFromTrait($property)) {
                $propertyName = $property->getName();
                $propertyValue = $this->getPropertyValue($class, $propertyName);

                $attributes[$propertyName] = [
                    'value' => $propertyValue,
                    'visibility' => $this->getAttributeVisibility($property),
                ];
            }
        }

        return $attributes;
    }

    private function getAttributeVisibility(ReflectionProperty $property): string
    {
        if ($property->isPublic()) {
            return '+';
        } elseif ($property->isProtected()) {
            return '#';
        } elseif ($property->isPrivate()) {
            return '-';
        }

        return 'unknown'; // Handle other visibility cases as needed
    }

    /**
     * @throws ReflectionException
     */
    private function getPropertyValue($class, $propertyName)
    {
        $reflection = new ReflectionClass($class);

        if (property_exists($class, $propertyName)) {
            $property = $reflection->getProperty($propertyName);
            if ($property->isInitialized(new $class())) {
                return $property->getValue(new $class());
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    private function getTraitsAndInterfaces(ReflectionClass $class, ?bool $includeTraitsAndInterfaces = false): ?array
    {
        if ($includeTraitsAndInterfaces) {
            $traits = $class->getTraitNames();
            $interfaces = $class->getInterfaceNames();
            return array_merge($traits, $interfaces);
        } else {
            return null;
        }
    }

    private function isModelClass($class): bool
    {
        return is_subclass_of($class, Model::class);
    }

    private function isMethodFromTrait(ReflectionMethod $method): bool
    {
        $declaringClass = $method->getDeclaringClass();

        foreach ($declaringClass->getTraits() as $trait) {
            if ($this->traitHasMethod($trait, $method->getName())) {
                return true;
            }
        }

        return false;
    }

    private function isPropertyFromTrait(ReflectionProperty $property): bool
    {
        $declaringClass = $property->getDeclaringClass();

        foreach ($declaringClass->getTraits() as $trait) {
            if ($this->traitHasMethod($trait, $property->getName())) {
                return true;
            }
        }

        return false;
    }

    private function traitHasMethod($trait, $methodName): bool
    {
        $traitMethods = $trait->getMethods();

        foreach ($traitMethods as $traitMethod) {
            if ($traitMethod->getName() === $methodName) {
                return true;
            }
        }

        return false;
    }

    private function getMethodParameters(ReflectionMethod $method): array
    {
        $parameters = [];

        foreach ($method->getParameters() as $parameter) {
            $parameterInfo = [
                'name' => $parameter->getName(),
                'type' => $this->getParameterType($parameter),
                'default' => $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null,
            ];

            $parameters[] = $parameterInfo;
        }

        return $parameters;
    }

    private function getReturnType(ReflectionMethod $method): ?string
    {
        if (method_exists($method, 'getReturnType') && ($returnType = $method->getReturnType())) {
            if ($returnType instanceof ReflectionNamedType) {
                // Handle named types
                return $returnType->getName();
            }
        }

        return null;
    }

    private function getParameterType(ReflectionParameter $parameter): ?string
    {
        $parameterType = $parameter->getType();

        if ($parameterType instanceof ReflectionUnionType) {
            // Handle union types (PHP 8.0+)
            $typeNames = array_map(fn($type) => $type->getName(), $parameterType->getTypes());

            return implode('|', $typeNames);
        } elseif ($parameterType instanceof ReflectionNamedType) {
            // Handle named types
            return $parameterType->getName();
        }

        return null;
    }
}
