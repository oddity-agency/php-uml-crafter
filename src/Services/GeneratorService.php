<?php

namespace WongDoody\PhpUmlCrafter\Services;

use Illuminate\Support\Arr;

class GeneratorService
{
    public function execute($data, ?bool $lightMode = false): string
    {
        $umlSyntax = "@startuml\n\n";

        if(!$lightMode) {
            $umlSyntax .= " skinparam ranksep 350\n";
            $umlSyntax .= " skinparam backgroundColor #343434\n";
            $umlSyntax .= " skinparam roundcorner 20\n";
            $umlSyntax .= " skinparam ArrowColor SeaGreen\n";
            $umlSyntax .= " skinparam classFontColor white\n";

            $umlSyntax .= "skinparam class {\n";
            $umlSyntax .= "BackgroundColor #545454\n";
            $umlSyntax .= "}\n";
        }

        $umlSyntax .= "skinparam package {\n";
        $umlSyntax .= "skinparam packageFontColor SpringGreen\n";
        $umlSyntax .= "}\n";


        foreach ($data as $className => $classInfo) {
            $escapedClassName = str_replace('\\', '_', $className);
            // Extract short class name without namespace
            $shortClassName = class_basename($className);

            $umlSyntax .= "class \"$shortClassName\" as $escapedClassName";

            // Add parent class, if any
            if ($classInfo['relations']['parent_class']) {
                $escapedParentClass = str_replace('\\', '_', $classInfo['relations']['parent_class']);
                $umlSyntax .= " extends $escapedParentClass";
            }

            // Add interfaces and traits, if any
            if (!empty($classInfo['relations']['traits_and_interfaces'])) {
                $shortTraitsAndInterfaces = array_map(
                    'class_basename', $classInfo['relations']['traits_and_interfaces']
                );
                Arr::map($shortTraitsAndInterfaces, function ($className) {
                    return str_replace('\\', '_', $className);
                });
                $umlSyntax .= ' implements ' . implode(', ', $shortTraitsAndInterfaces);
            }

            $umlSyntax .= " {\n";
            $umlSyntax .= "$className\n";
            $umlSyntax .= "..\n";

            // Add attributes
            foreach ($classInfo['attributes'] as $attributeName => $attributeData) {
                $umlSyntax .= "    {$attributeData['visibility']}$attributeName\n";
            }

            $umlSyntax .= "__\n";

            // Add methods
            foreach ($classInfo['methods'] as $methodName => $methodInfo) {
                // Extract short return type without namespace
                $shortReturnType = class_basename($methodInfo['return_type']);

                $umlSyntax .= "    {$methodInfo['visibility']}$methodName(): $shortReturnType\n";
            }

            $umlSyntax .= "}\n\n";
        }

        $umlSyntax .= "@enduml";

        return $umlSyntax;
    }

}
