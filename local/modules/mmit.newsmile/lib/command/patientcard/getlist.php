<?

namespace Mmit\NewSmile\Command\PatientCard;

use Bitrix\Main\ORM\Entity;
use Mmit\NewSmile\Command\OrmGetList;
use Mmit\NewSmile\PatientCardTable;

class GetList extends OrmGetList
{
    protected function getOrmEntity()
    {
        return PatientCardTable::getEntity();
    }

}