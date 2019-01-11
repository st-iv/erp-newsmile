<?

namespace Mmit\NewSmile\Notice;

use Mmit\NewSmile\Helpers;
use Mmit\NewSmile\PatientCardTable;
use Mmit\NewSmile\Service\ServiceTable;
use Mmit\NewSmile\VisitRequestTable;

class NewVisitRequest extends Notice
{
    protected function getParamsList()
    {
        return ['VISIT_REQUEST_ID'];
    }
    
    protected function extendParams()
    {
        if(!$this->params['VISIT_REQUEST_ID']) return;

        $visitRequest = VisitRequestTable::getByPrimary($this->params['VISIT_REQUEST_ID'], [
            'select' => [
                '*',
                'PATIENT_NAME' => 'PATIENT.NAME',
                'PATIENT_LAST_NAME' => 'PATIENT.LAST_NAME',
                'PATIENT_SECOND_NAME' => 'PATIENT.SECOND_NAME',
                'SERVICE_NAME' => 'SERVICE.NAME',
            ]
        ])->fetch();

        if($visitRequest['SERVICE_ID'])
        {
            $this->params['SERVICE'] = $visitRequest['SERVICE_NAME'];
        }
        else
        {
            $this->params['SERVICE'] = 'услуга не указана';
        }

        if($visitRequest['PATIENT_ID'])
        {
            $this->params['PATIENT'] = Helpers::getFio($visitRequest, 'PATIENT_');
        }

        if($visitRequest['NEAR_FUTURE'])
        {
            $this->params['DATE'] = 'ближайшее время';
        }
        else
        {
            $this->params['DATE'] = $visitRequest['DATE'] ?: '(дата не указана)';
        }

        $this->params['COMMENT'] = $visitRequest['COMMENT'];
    }
}