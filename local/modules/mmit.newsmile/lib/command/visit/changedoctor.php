<?


namespace Mmit\NewSmile\Command\Visit;

use Mmit\NewSmile\Command\Base;
use Mmit\NewSmile\CommandVariable\Date;
use Mmit\NewSmile\CommandVariable\Integer;
use Mmit\NewSmile\CommandVariable\Time;

class ChangeDoctor extends Base
{
    protected static $name = 'Изменить врача';

    protected function doExecute()
    {

    }

    public function getParamsMap()
    {
        return [
            new Time('timeStart', 'Начало интервала', true),
            new Time('timeEnd', 'Конец интервала', true),
            new Integer('chairId', 'id кресла', true),
            new Date('date', 'дата', true),
            new Integer('doctorId', 'id врача', true),
        ];
    }
}