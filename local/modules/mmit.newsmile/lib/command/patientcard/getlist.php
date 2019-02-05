<?

namespace Mmit\NewSmile\Command\PatientCard;

use Bitrix\Main\ORM\Entity;
use Mmit\NewSmile\Command\OrmGetList;
use Mmit\NewSmile\PatientCardTable;

class GetList extends OrmGetList
{
    public function getDescription()
    {
        return 'Получает список пациентов';
    }

    public function getOrmEntity()
    {
        return PatientCardTable::getEntity();
    }
}