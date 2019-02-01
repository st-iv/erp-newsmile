<?

namespace Mmit\NewSmile\Command\Schedule;

use Mmit\NewSmile\Command\Base;
use Mmit\NewSmile;
use Mmit\NewSmile\CommandVariable;

class GetFilter extends Base
{
    public function getDescription()
    {
        return 'Возвращает информацию для фильтра по расписанию - список врачей и список специализаций';
    }

    public function getResultFormat()
    {
        return new NewSmile\Command\ResultFormat([
            (new CommandVariable\ArrayParam('doctors', 'информация о врачах', true))->setContentType(
                (new CommandVariable\Object('', ''))->setShape([
                    new CommandVariable\Integer('id', 'id', true),
                    new CommandVariable\String('fio', 'ФИО', true),
                    new CommandVariable\Integer('COLOR', 'шестнадцатеричный код цвета вместе с #', true),
                ])
            ),
            (new CommandVariable\ArrayParam('specializations', 'символьные коды всех специализаций врачей', true))->setContentType(
                new CommandVariable\String('', '')
            )
        ]);
    }

    protected function doExecute()
    {
        $this->result = [
            'doctors' => $this->getDoctors(),
            'specializations' => NewSmile\DoctorSpecializationTable::getEnumVariants('SPECIALIZATION'),
        ];
    }

    protected function getDoctors()
    {
        $result = [];

        $rsDoctor = NewSmile\DoctorTable::getList(array(
            'select' => array(
                'ID', 'NAME', 'COLOR', 'LAST_NAME', 'SECOND_NAME'
            ),
            'filter' => [
                'CLINIC_ID' => NewSmile\Config::getClinicId()
            ]
        ));

        while ($doctor = $rsDoctor->fetch())
        {
            $result['doctors'][] = [
                'id' => $doctor['ID'],
                'fio' => NewSmile\Helpers::getFio($doctor),
                'COLOR' => $doctor['COLOR']
            ];
        }

        return $result;
    }


    public function getParamsMap()
    {
        return [];
    }
}