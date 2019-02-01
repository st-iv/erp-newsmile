<?


namespace Mmit\NewSmile\Command\PatientCard;

use Mmit\NewSmile\Application;
use Mmit\NewSmile\Command\Base;
use Mmit\NewSmile\Command\ResultFormat;
use Mmit\NewSmile\DoctorTable;
use Mmit\NewSmile\Helpers;
use Mmit\NewSmile\PatientCardTable;
use Mmit\NewSmile\CommandVariable;

class Detail extends Base
{
    public function getDescription()
    {
        return 'Получает детальную информацию о текущем пациенте (в особом формате для мобильных приложений)';
    }

    public function getResultFormat()
    {
        return new ResultFormat([
            new CommandVariable\String('name', 'имя пациента', true),
            new CommandVariable\String('lastName', 'фамилия пациента', true),
            new CommandVariable\String('secondName', 'отчество пациента', true),
            new CommandVariable\Phone('phone', 'телефон', true),
            new CommandVariable\Phone('phone2', 'дополнительный телефон', true),
            new CommandVariable\String('email', 'адрес электронной почты', true),
            new CommandVariable\Bool('smsNotice', 'флаг получения смс-уведомлений', true),
            new CommandVariable\Integer('cardNumber', 'номер карты пациента', true),
            (new CommandVariable\ArrayParam('doctor_list', 'лечащие врачи пациента', true))->setContentType(
                (new CommandVariable\Object('', ''))->setShape([
                    new CommandVariable\Integer('id', 'id врача', true),
                    new CommandVariable\String('fio', 'ФИО врача', true)
                ])
            )
        ]);
    }

    protected function doExecute()
    {
        $dbPatientCard = PatientCardTable::getByPrimary(Application::getInstance()->getUser()->getId(), [
            'select' => [
                'NAME',
                'LAST_NAME',
                'SECOND_NAME',
                'phone' => 'PERSONAL_PHONE',
                'phone2' => 'ADDITIONAL_PHONE',
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