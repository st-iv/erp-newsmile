<?


namespace Mmit\NewSmile\Command\PatientCard;

use Mmit\NewSmile\Application;
use Mmit\NewSmile\Command\Base;
use Mmit\NewSmile\DoctorTable;
use Mmit\NewSmile\Helpers;
use Mmit\NewSmile\PatientCardTable;

class Detail extends Base
{
    protected static $name = 'Получить детальную информацию о пациенте';

    protected function doExecute()
    {
        $dbPatientCard = PatientCardTable::getByPrimary(Application::getInstance()->getUser()->getId(), [
            'select' => [
                'NAME',
                'LAST_NAME',
                'SECOND_NAME',
                'phone' => 'PERSONAL_PHONE',
                'phone2' => 'ADDITIONAL_PHONES',
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
        $patientCard['phone2'] = implode(', ', $patientCard['phone2']);

        $this->result = Helpers::strtolowerKeys($patientCard);
        $this->result['doctor_list'] = $doctors;
    }

    public function getParamsMap()
    {
        return [];
    }
}