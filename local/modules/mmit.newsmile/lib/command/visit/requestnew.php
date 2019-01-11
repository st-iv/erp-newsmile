<?

namespace Mmit\NewSmile\Command\Visit;

use Bitrix\Main\Entity\Field;
use Mmit\NewSmile\Command\OrmEntityAdd;
use Mmit\NewSmile\Helpers;
use Mmit\NewSmile\Notice;
use Mmit\NewSmile\Application;
use Mmit\NewSmile\VisitRequestTable;

class RequestNew extends OrmEntityAdd
{
    protected function doExecute()
    {
        parent::doExecute();

        $notice = new Notice\NewVisitRequest([
            'VISIT_REQUEST_ID' => $this->result['primary']['id']
        ]);
        $notice->push(['admin']);
    }

    protected function getOrmEntity()
    {
        return VisitRequestTable::getEntity();
    }

    protected function filterField(Field $field)
    {
        return !in_array($field->getName(), ['PATIENT_ID', 'STATUS']);
    }

    protected function getParamNameByField(Field $field)
    {
        return strtolower($field->getName());
    }

    protected function getFieldsValues()
    {
        $values = parent::getFieldsValues();
        $values['PATIENT_ID'] = Application::getInstance()->getUser()->getId();
        return $values;
    }
}