<?

namespace Mmit\NewSmile\Command\PatientCard;


use Bitrix\Main\Entity\Field;
use Bitrix\Main\ORM\Entity;
use Mmit\NewSmile\Command\OrmEntityEdit;
use Mmit\NewSmile\PatientCardTable;

class Edit extends OrmEntityEdit
{
    protected function getOrmEntity()
    {
        return PatientCardTable::getEntity();
    }

    protected function filterField(Field $field)
    {
        return true;
    }
}