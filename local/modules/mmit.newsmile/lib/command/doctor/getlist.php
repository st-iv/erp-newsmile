<?

namespace Mmit\NewSmile\Command\Doctor;

use Mmit\NewSmile\Command\OrmGetList;
use Mmit\NewSmile\DoctorTable;

class GetList extends OrmGetList
{
    protected static $name = 'Получить список врачей';

    protected function getOrmEntity()
    {
        return DoctorTable::getEntity();
    }
}