<?

namespace Mmit\NewSmile\Command\Doctor;

use Mmit\NewSmile\Command\OrmGetList;
use Mmit\NewSmile\DoctorTable;

class GetList extends OrmGetList
{
    public function getOrmEntity()
    {
        return DoctorTable::getEntity();
    }

    public function getDescription()
    {
        return 'Возвращает информацию о врачах';
    }
}