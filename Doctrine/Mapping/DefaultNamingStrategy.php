<?php

namespace Msi\CmfBundle\Doctrine\Mapping;

use Doctrine\ORM\Mapping\NamingStrategy;

class DefaultNamingStrategy implements NamingStrategy
{
    public function classToTableName($className)
    {
        $prefix = 'msi_';

        if (strpos($className, '\\') !== false) {
            $className = substr($className, strrpos($className, '\\') + 1);
        }

        $className = lcfirst($className);
        $className = preg_replace_callback('|([A-Z])|', function($matches) {
            return '_'.strtolower($matches[0]);
        }, $className);

        return $prefix.$className;
    }

    public function propertyToColumnName($propertyName, $className = null)
    {
        $propertyName = preg_replace_callback('|([A-Z])|', function($matches) {
            return '_'.strtolower($matches[0]);
        }, $propertyName);

        return $propertyName;
    }

    public function referenceColumnName()
    {
        return 'id';
    }

    public function joinColumnName($propertyName)
    {
        return $this->propertyToColumnName($propertyName).'_'.$this->referenceColumnName();
    }

    public function joinTableName($sourceEntity, $targetEntity, $propertyName = null)
    {
        return strtolower($this->classToTableName($sourceEntity).'_'.$this->propertyToColumnName($propertyName));
    }

    public function joinKeyColumnName($entityName, $referencedColumnName = null)
    {
        return strtolower($this->classToTableName($entityName).'_'.($referencedColumnName ?: $this->referenceColumnName()));
    }
}
