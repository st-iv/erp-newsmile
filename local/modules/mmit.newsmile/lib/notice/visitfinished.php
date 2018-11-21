<?

namespace Mmit\NewSmile\Notice;

use Mmit\NewSmile\VisitTable;
use Mmit\NewSmile\Date;

class VisitFinished extends Notice
{
    protected function getParamsList()
    {
        return ['VISIT_ID'];
    }
    
    protected function extendParams()
    {
        if($this->params['VISIT_ID'])
        {
            $dbVisit = VisitTable::getByPrimary($this->params['VISIT_ID'], array(
                'select' => array(
                    'PATIENT_NAME' => 'PATIENT.NAME',
                    'PATIENT_LAST_NAME' => 'PATIENT.LAST_NAME',
                    'PATIENT_SECOND_NAME' => 'PATIENT.SECOND_NAME',
                    'PATIENT_BIRTHDAY' => 'PATIENT.PERSONAL_BIRTHDAY',
                    'DOCTOR_NAME' => 'DOCTOR.NAME',
                    'DOCTOR_LAST_NAME' => 'DOCTOR.LAST_NAME',
                    'DOCTOR_SECOND_NAME' => 'DOCTOR.SECOND_NAME',
                    'DOCTOR_COLOR' => 'DOCTOR.COLOR',
                )
            ));

            if($visit = $dbVisit->fetch())
            {
                $this->params['PATIENT_AGE'] = Date\Helper::getAge($visit['PATIENT_BIRTHDAY']);
                unset($visit['PATIENT_BIRTHDAY']);

                $this->params = array_merge($this->params, $visit);
            }
        }
    }
}