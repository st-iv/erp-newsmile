<?


namespace Mmit\NewSmile\Command\PatientCard;

use Mmit\NewSmile\Command;
use Mmit\NewSmile\PatientCardTable;

class Add extends Command\OrmEntityAdd
{
    protected function getOrmEntity()
    {
        return PatientCardTable::getEntity();
    }
}