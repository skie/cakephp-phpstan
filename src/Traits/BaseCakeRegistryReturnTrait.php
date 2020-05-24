<?php

/**
 * Copyright 2020, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2020, Cake Development Corporation (https://www.cakedc.com)
 *  @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\PHPStan\Traits;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;

trait BaseCakeRegistryReturnTrait
{
    /**
     * @param MethodReflection $methodReflection
     * @param MethodCall $methodCall
     * @param Scope $scope
     * @param string $defaultClass
     * @param string $namespaceFormat
     * @return ObjectType|Type
     * @throws \PHPStan\ShouldNotHappenException
     */
    protected function getRegistryReturnType(
        $methodReflection,
        $methodCall,
        $scope,
        string $defaultClass,
        string $namespaceFormat
    ) {
        if (\count($methodCall->args) === 0) {
            return ParametersAcceptorSelector::selectSingle($methodReflection->getVariants())->getReturnType();
        }

        $argType = $scope->getType($methodCall->args[0]->value);
        if (!method_exists($argType, 'getValue')) {
            return new ObjectType($defaultClass);
        }
        $baseName = $argType->getValue();
        [$plugin, $name] = pluginSplit($baseName);
        $prefixes = $plugin ? [$plugin] : ['Cake', 'App'];
        foreach ($prefixes as $prefix) {
            $namespace = str_replace('/', '\\', $prefix);
            $className = sprintf($namespaceFormat, $namespace, $name);
            if (class_exists($className)) {
                return new ObjectType($className);
            }
        }

        return new ObjectType($defaultClass);
    }
}
