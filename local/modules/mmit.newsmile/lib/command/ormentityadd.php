<?


namespace Mmit\NewSmile\Command;

abstract class OrmEntityAdd extends OrmEntityEdit
{
    public function getParamsMap()
    {
        return array_filter(parent::getParamsMap(), function(\Mmit\NewSmile\CommandParam\Base $param)
        {
            return ($param->getCode() != 'id');
        });
    }
}