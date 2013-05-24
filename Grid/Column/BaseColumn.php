<?php

namespace Msi\CmfBundle\Grid\Column;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

abstract class BaseColumn
{
    protected $name;
    protected $object;
    protected $value;
    protected $type;
    protected $options = array();
    protected $translationValues = array();

    public function __construct($field)
    {
        $this->name = $field['name'];
        $this->type = $field['type'];

        $resolver = new OptionsResolver();
        $resolver->setDefaults(array(
            'attr' => array(),
            'label' => $this->name,
        ));
        $this->setDefaultOptions($resolver);
        $this->options = $resolver->resolve($field['options']);
    }

    abstract public function setDefaultOptions(OptionsResolverInterface $resolver);

    public function resolveRow($row)
    {
        $this->object = $row;

        // If it's not the action column
        if ($this->name) {
            $pieces = explode('.', $this->name);
            $getter = 'get'.ucfirst($pieces[0]);

            // If the getter gets an array key (ex: settings in block)
            if (isset($pieces[1])) {
                $this->value = $this->object->$getter($pieces[1]);
            // Else translation
            } else if (!property_exists($this->object, $this->name) && !method_exists($this->object, 'get'.ucfirst($this->name))) {
                // translation fallback
                $this->value = $this->object->getTranslation()->$getter();
                if (!$this->value) {
                    foreach ($this->object->getTranslations() as $translation) {
                        if ($this->value = $translation->$getter()) {
                            break;
                        }
                    }
                }
                foreach ($this->object->getTranslations() as $translation) {
                    $this->translationValues[$translation->getLocale()] = $translation->$getter();
                }
                // order translation in the good order par rapport a la request locale
                $requestLocale = $this->object->getRequestLocale();
                if (isset($this->translationValues[$requestLocale])) {
                    $foo = $this->translationValues[$requestLocale];
                    unset($this->translationValues[$requestLocale]);
                    $this->translationValues[$requestLocale] = $foo;
                    $this->translationValues = array_reverse($this->translationValues);
                }
            // Else normal value
            } else {
                if (is_array($this->object->$getter())) {
                    $this->value = implode(', ', $this->object->$getter());
                } else {
                    $this->value = $this->object->$getter();
                }
            }

            if (null !== $this->value) {
                $this->fixValue();
            }
        }

        return $this;
    }

    public function getObject()
    {
        return $this->object;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getTranslationValues()
    {
        return $this->translationValues;
    }

    public function get($name)
    {
        return $this->options[$name];
    }

    public function set($name, $val)
    {
        $this->options[$name] = $val;
    }

    public function fixValue()
    {
    }
}
