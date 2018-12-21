<?


namespace Mmit\NewSmile\Command;

use Bitrix\Main\Diag\Debug;
use Mmit\NewSmile\CommandParam\Date;
use Mmit\NewSmile\CommandParam\DateTime;
use Mmit\NewSmile\CommandParam\Time;
use Mmit\NewSmile\Error;
use Mmit\NewSmile\Helpers;
use Mmit\NewSmile\PatientCardTable;

abstract class OrmEntityAdd extends OrmEntityEdit
{
    protected function doExecute()
    {
        $data = [];

        $paramsMap = $this->getParamsMapAssoc();

        foreach ($this->params as $paramKey => $paramValue)
        {
            /**
             * @var \Mmit\NewSmile\CommandParam\Base $param
             */
            $param = $paramsMap[$paramKey];

            if($param instanceof Date)
            {
                $paramValue = new \Bitrix\Main\Type\Date($paramValue, 'Y-m-d');
            }
            elseif($param instanceof DateTime)
            {
                $paramValue = new \Bitrix\Main\Type\DateTime($paramValue, 'Y-m-d H:i:s');
            }


            $data[Helpers::getSnakeCase($paramKey)] = $paramValue;
        }

        Debug::dumpToFile($data, 'data!');

        $addResult = PatientCardTable::add($data);

        if(!$addResult->isSuccess())
        {
            throw new Error(
                'Ошибка добавления записи: ' . implode('; ', $addResult->getErrorMessages()),
                'ADD_RECORD_ERROR'
            );
        }
    }

    public function getParamsMap()
    {
        return array_filter(parent::getParamsMap(), function(\Mmit\NewSmile\CommandParam\Base $param)
        {
            return ($param->getCode() != 'id');
        });
    }
}