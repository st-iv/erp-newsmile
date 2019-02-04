<?

namespace Mmit\NewSmile\Command;

use Bitrix\Main\Entity\Field;
use Bitrix\Main\Entity\FloatField;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\ScalarField;
use Bitrix\Main\Entity\StringField;
use Bitrix\Main\ORM\Entity;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use Mmit\NewSmile\Error;
use Mmit\NewSmile\Helpers;
use Mmit\NewSmile\Orm\Helper;
use Mmit\NewSmile\CommandParam;

/**
 * Составляет карту параметров по полям Entity, возвращаемой методом getOrmEntity
 *
 * Class OrmEntityAdd
 * @package Mmit\NewSmile\Command
 */
abstract class OrmEntityEdit extends OrmEntityWrite
{
    protected function doExecute()
    {
        $dataManager = $this->getOrmEntity()->getDataClass();
        $updateResult = $dataManager::update($this->params['id'], $this->getFieldsValues());

        if(!$updateResult->isSuccess())
        {
            throw new Error(
                'Ошибка редактирования записи: ' . implode('; ', $updateResult->getErrorMessages()),
                'UPDATE_RECORD_ERROR'
            );
        }
    }

    public function getResultFormat()
    {
        return new ResultFormat([]);
    }
}