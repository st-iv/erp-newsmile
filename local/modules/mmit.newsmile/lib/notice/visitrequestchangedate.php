<?

namespace Mmit\NewSmile\Notice;

use Mmit\NewSmile\Helpers;
use Mmit\NewSmile\PatientCardTable;

class VisitRequestChangeDate extends Notice
{
    protected function getParamsList()
    {
        return ['PATIENT_ID', 'OLD_DATE', 'NEW_DATE'];
    }

    protected function extendParams()
    {
        if($this->params['PATIENT_ID'])
        {
            $patient = PatientCardTable::getByPrimary($this->params['PATIENT_ID'], [
                'select' => ['NAME', 'LAST_NAME', 'SECOND_NAME']
            ])->fetch();
            $this->params['PATIENT'] = Helpers::getFio($patient);
        }
    }
}