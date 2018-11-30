<?


namespace Mmit\NewSmile\Command\Schedule;

use Mmit\NewSmile\Command\Base;

class ChangeDoctor extends Base
{
    public function execute()
    {
        // just do it!
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
            'doctorId' => [
                'TITLE' => 'id врача',
                'REQUIRED' => true
            ]
        ];
    }

    public function getName()
    {
        return 'Изменить врача';
    }

    protected function getOperations()
    {
        return ['change-doctor'];
    }
}