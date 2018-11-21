<?


namespace Mmit\NewSmile\Notice;

use Mmit\NewSmile\Error;
use Mmit\NewSmile\Helpers;
use Mmit\NewSmile\PatientCardTable;
use Mmit\NewSmile\VisitTable;

class VisitChangeDate extends Notice
{
    protected function getParamsList()
    {
        return ['PATIENT_ID', 'VISIT_ID', 'NEW_DATE'];
    }
    
    protected function extendParams()
    {
        if($this->params['PATIENT_ID'])
        {
            $dbPatient = PatientCardTable::getByPrimary($this->params['PATIENT_ID'], [
                'select' => ['LAST_NAME', 'NAME', 'SECOND_NAME']
            ]);

            if($patient = $dbPatient->fetch())
            {
                $this->params['PATIENT'] = Helpers::getFio($patient);
            }
        }

        if($this->params['VISIT_ID'])
        {
            $dbVisit = VisitTable::getByPrimary($this->params['VISIT_ID'], [
                'select' => ['TIME_START'],
                'filter' => [
                    'PATIENT_ID' => $this->params['PATIENT_ID']
                ]
            ]);

            if($visit = $dbVisit->fetch())
            {
                $this->params['ACTUAL_DATE'] = $visit['TIME_START'];
            }
            else
            {
                throw new Error('Для пользователя не найден прием с указанным id', 'VISIT_NOT_FOUND');
            }
        }
    }
}