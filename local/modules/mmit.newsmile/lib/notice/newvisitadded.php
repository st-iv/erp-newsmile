<?

namespace Mmit\NewSmile\Notice;

use Mmit\NewSmile\Helpers;
use Mmit\NewSmile\Visit\VisitTable;

class NewVisitAdded extends Notice
{
    protected function getParamsList()
    {
        return ['VISIT_ID'];
    }

    protected function extendParams()
    {
        $visit = VisitTable::getByPrimary($this->params['VISIT_ID'], [
            'select' => [
                'TIME_START',
                'TIME_END',
                'DATE' => 'DATE_START',
                'CHAIR' => 'WORK_CHAIR.NAME',
                'DOCTOR_NAME' => 'DOCTOR.NAME',
                'DOCTOR_LAST_NAME' => 'DOCTOR.LAST_NAME',
                'DOCTOR_SECOND_NAME' => 'DOCTOR.SECOND_NAME',
            ]
        ])->fetch();

        $visit['DOCTOR'] = Helpers::getFio($visit, 'DOCTOR_');
        $visit['TIME_START'] = $visit['TIME_START']->format('H:i');
        $visit['TIME_END'] = $visit['TIME_END']->format('H:i');


        $this->params = array_merge($this->params, $visit);
    }
}