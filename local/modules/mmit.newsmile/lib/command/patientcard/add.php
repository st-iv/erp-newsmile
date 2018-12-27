<?


namespace Mmit\NewSmile\Command\PatientCard;

use Mmit\NewSmile\Command;
use Mmit\NewSmile\Helpers;
use Mmit\NewSmile\PatientCardTable;

class Add extends Command\OrmEntityAdd
{
    protected function getOrmEntity()
    {
        return PatientCardTable::getEntity();
    }

    protected function prepareParamValue($paramCode, $paramValue)
    {
        if($paramCode == 'additionalPhones')
        {
            array_walk($paramValue, function(&$phone)
            {
                $phone = Helpers::preparePhone($phone);
            });
        }

        return $paramValue;
    }
}