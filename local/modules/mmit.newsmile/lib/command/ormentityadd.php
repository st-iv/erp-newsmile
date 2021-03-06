<?


namespace Mmit\NewSmile\Command;

use Bitrix\Main\Diag\Debug;
use Bitrix\Main\Entity\IntegerField;
use Mmit\NewSmile\CommandVariable\Date;
use Mmit\NewSmile\CommandVariable\DateTime;
use Mmit\NewSmile\CommandVariable\Integer;
use Mmit\NewSmile\CommandVariable\Object;
use Mmit\NewSmile\CommandVariable\Time;
use Mmit\NewSmile\Error;
use Mmit\NewSmile\Helpers;
use Mmit\NewSmile\PatientCardTable;

abstract class OrmEntityAdd extends OrmEntityWrite
{
    protected function doExecute()
    {
        $dataManager = $this->getOrmEntity()->getDataClass();
        $addResult = $dataManager::add($this->getFieldsValues());

        if($addResult->isSuccess())
        {
            $this->result['primary'] = Helpers::strtolowerKeys($addResult->getPrimary());
        }
        else
        {
            throw new Error(
                'Ошибка добавления записи: ' . implode('; ', $addResult->getErrorMessages()),
                'ADD_RECORD_ERROR'
            );
        }
    }

    public function getParamsMap()
    {
        return array_filter(parent::getParamsMap(), function(\Mmit\NewSmile\CommandVariable\Base $param)
        {
            return ($param->getCode() != 'id');
        });
    }

    public function getResultFormat()
    {
        return new ResultFormat([
            (new Object('primary', 'первичный ключ добавленной записи', true))->setShape([
                new Integer('id', 'как правило, именно id является первичным ключом')
            ])->setFlexible(true)
        ]);
    }
}