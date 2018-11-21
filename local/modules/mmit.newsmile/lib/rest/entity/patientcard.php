<?

namespace Mmit\NewSmile\Rest\Entity;

use Mmit\NewSmile\Application;
use Mmit\NewSmile\DoctorTable;
use Mmit\NewSmile\Helpers;
use Mmit\NewSmile\PatientCardTable;
use Mmit\NewSmile\Status\ToothTable;

class PatientCard extends Controller
{
    public function processDetail()
    {
        if(!$this->checkMethod('GET'))
        {
            return;
        }

        $dbPatientCard = PatientCardTable::getByPrimary(Application::getInstance()->getUser()->getId(), [
            'select' => [
                'NAME',
                'LAST_NAME',
                'SECOND_NAME',
                'phone' => 'PERSONAL_PHONE',
                'phone2' => 'PERSONAL_MOBILE',
                'EMAIL',
                'SMS_NOTICE',
                'card_number' => 'NUMBER',
                'DOCTORS_ID'
            ]
        ]);

        $doctors = [];

        $patientCard = $dbPatientCard->fetch();

        if($patientCard['DOCTORS_ID'])
        {
            $dbDoctors = DoctorTable::getList([
                'filter' => [
                    'ID' => $patientCard['DOCTORS_ID']
                ],
                'select' => ['NAME', 'LAST_NAME', 'SECOND_NAME', 'ID']
            ]);

            while($doctor = $dbDoctors->fetch())
            {
                $doctors[] = [
                    'id' => $doctor['ID'],
                    'fio' => Helpers::getFio($doctor)
                ];
            }
        }

        unset($patientCard['DOCTORS_ID']);
        $patientCard['SMS_NOTICE'] = $patientCard['SMS_NOTICE'] ?: false;

        $this->responseData = Helpers::strtolowerKeys($patientCard);
        $this->responseData['doctor_list'] = $doctors;
    }

    protected function getDefaultAction()
    {
        return 'detail';
    }

    protected function getActionsMap()
    {
        return [
            'detail' => []
        ];
    }
}