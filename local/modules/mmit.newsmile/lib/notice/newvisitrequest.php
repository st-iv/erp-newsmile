<?

namespace Mmit\NewSmile\Notice;

use Mmit\NewSmile\Helpers;
use Mmit\NewSmile\PatientCardTable;
use Mmit\NewSmile\Service\ServiceTable;

class NewVisitRequest extends Notice
{
    protected function getParamsList()
    {
        return ['SERVICE_ID', 'PATIENT_ID', 'DATE', 'NEAR_FUTURE', 'COMMENT'];
    }
    
    protected function extendParams()
    {
        if($this->params['SERVICE_ID'])
        {
            $dbService = ServiceTable::getByPrimary($this->params['SERVICE_ID'], [
                'select' => ['NAME']
            ]);

            if($service = $dbService->fetch())
            {
                $this->params['SERVICE'] = $service['NAME'];
            }
        }
        else
        {
            $this->params['SERVICE'] = 'услуга не указана';
        }

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

        if($this->params['NEAR_FUTURE'])
        {
            $this->params['DATE'] = 'ближайшее время';
        }
    }
}