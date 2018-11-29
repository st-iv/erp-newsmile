<?

namespace Mmit\NewSmile\Access;

interface AccessibleEntity
{
    public static function doOperation($code, $variant = null);
    public static function isOperationAvailable($code);
    public static function getOperationsList();
    public static function getOperationVariants($code);
}