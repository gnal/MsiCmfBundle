<?php

namespace Msi\CmfBundle\Doctrine\Extension\Translatable;

interface TranslatableInterface
{
    function getTranslation($locale = null);

    function getTranslations();

    function setTranslations($translations);

    function setRequestLocale($requestLocale);
}
