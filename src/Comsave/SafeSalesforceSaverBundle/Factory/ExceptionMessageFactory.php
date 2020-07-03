<?php

namespace Comsave\SafeSalesforceSaverBundle\Factory;

class ExceptionMessageFactory
{
    public static function build(object $occurredInObject, string $message): string
    {
        return vsprintf('SafeSalesforceSaver. In `%s` occured `%s`.', [
            substr(strrchr(get_class($occurredInObject), "\\"), 1),
            $message
        ]);
    }
}