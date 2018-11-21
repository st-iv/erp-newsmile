<?

namespace Mmit\NewSmile\Notice;

use Mmit\NewSmile\Helpers;
use Mmit\NewSmile\PatientCardTable;

class WaitingListSuggest extends Notice
{
    protected function getParamsList()
    {
        return ['PATIENT_ID', 'FREE_TIME'];
    }
    
    protected function extendParams()
    {
        if($this->params['PATIENT_ID'])
        {
            $dbPatient = PatientCardTable::getByPrimary($this->params['PATIENT_ID'], array(
                'select' => ['NAME', 'LAST_NAME', 'SECOND_NAME', 'PERSONAL_PHONE']
            ));

            if($patient = $dbPatient->fetch())
            {
                $this->params['PATIENT_PHONE'] = $patient['PERSONAL_PHONE'];
                $this->params['PATIENT_FIO'] = Helpers::getFio($patient);
            }
        }
    }
}