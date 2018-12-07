<?

namespace Mmit\NewSmile\Command\Schedule;

use Mmit\NewSmile\Command\Base;
use Mmit\NewSmile;

class GetFilter extends Base
{
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