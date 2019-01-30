<?

namespace Mmit\NewSmile\Command;

abstract class EntityDescriptor
{
    public function getCode()
    {
        $reflector = new \ReflectionClass(static::class);
        return pathinfo(dirname($reflector->getFileName()), PATHINFO_FILENAME);
    }

    abstract public function getDescription();
}