<?php

namespace Lch\SeoBundle\Reflection;

class ClassAnalyzer
{
    /**
     * Return TRUE if the given object use the given trait, FALSE if not
     * @param ReflectionClass $class
     * @param string $traitName
     * @param boolean $isRecursive
     */
    public function hasTrait(\ReflectionClass $class, $traitName, $isRecursive = false)
    {
        $classTraits = $class->getTraitNames();

        // Trait directly present in final class
        if (in_array($traitName, $classTraits)) {
            return true;
        }

        // Check in parents traits
        foreach($classTraits as $classTrait) {
            $traitObject = new \ReflectionClass($classTrait);

            if($this->hasTrait($traitObject, $traitName, $isRecursive)) {
                return true;
            }
        }

        // Check in parents classes
        $parentClass = $class->getParentClass();

        if ((false === $isRecursive) || (false === $parentClass) || (null === $parentClass)) {
            return false;
        }

        return $this->hasTrait($parentClass, $traitName, $isRecursive);
    }
}
