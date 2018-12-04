<?


namespace Mmit\NewSmile\Command\Visit;

use Mmit\NewSmile\Command\Base;

class ChangeDoctor extends Base
{
    protected static $name = 'Изменить врача';

    protected function doExecute()
    {

    }

    public function getParamsMap()
    {
        return [
            'timeStart' => [
                'TITLE' => 'Начало интервала',
                'REQUIRED' => true
            ],
            'timeEnd' => [
                'TITLE' => 'Конец интервала',
                'REQUIRED' => true
            ],
            'chairId' => [
                'TITLE' => 'id кресла',
                'REQUIRED' => true
            ],
            'date' => [
                'TITLE' => 'дата',
                'REQUIRED' => true
            ],
            'doctorId' => [
                'TITLE' => 'id врача',
                'REQUIRED' => true
            ]
        ];
    }
}