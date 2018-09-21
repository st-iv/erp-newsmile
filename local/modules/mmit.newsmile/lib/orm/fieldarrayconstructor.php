<?

namespace Mmit\NewSmile\Orm;

use Bitrix\Main\Entity\ScalarField;
use Bitrix\Main\ORM\Fields;
use Mmit\NewSmile\MaterialTable;

/**
 * Подготавливает информацию о поле в виде массива
 * Class FieldArrayConstructor
 * @package Mmit\NewSmile\Orm
 */
class FieldArrayConstructor extends FieldsVisitor
{
    protected $result = array();

    public function getResultArray()
    {
        return $this->result;
    }

    protected function visitField(Fields\Field $field)
    {
        $this->result = array();
        $fieldName = $field->getName();

        $isEditable = !$this->params['EDITABLE_FIELDS'] || in_array($fieldName, $this->params['EDITABLE_FIELDS']);
        $isSelected = $isEditable || (!$this->params['SELECT_FIELDS'] || in_array($fieldName, $this->params['SELECT_FIELDS']));

        if(!$isSelected) return false;

        $this->result = array(
            'NAME' => $fieldName,
            'TITLE' => $field->getTitle(),
            'TYPE' => Helper::getFieldType($field),
            'EDITABLE' => $isEditable,
            'INPUT_NAME' => $fieldName
        );

        if($field instanceof Fields\ScalarField)
        {
            $this->result['REQUIRED'] = $field->isRequired();
            $this->result['DEFAULT'] = $field->getDefaultValue();
        }

        return true;
    }

    protected function visitEnumField(Fields\EnumField $field)
    {
        if($field instanceof ExtendedFieldsDescriptor)
        {
            $this->result['VALUES'] = $field->getEnumVariantsTitles($field->getName());
        }
        else
        {
            foreach ($field->getValues() as $value)
            {
                $this->result['VALUES'][$value] = $value;
            }
        }
    }
    
    protected function visitBooleanField(Fields\BooleanField $field)
    {
        $fieldValues = $field->getValues();
        $this->result['TRUE_VALUE'] = $fieldValues[1];
    }
    
    protected function visitReference(Fields\Relations\Reference $field)
    {
        $keyName = Helper::getReferenceExternalKeyName($field);
        $externalKeyField = $field->getEntity()->getField($keyName);

        $this->result['REFERENCE_ENTITY_CLASS'] = $field->getRefEntity()->getDataClass();
        $this->result['REFERENCE_KEY_NAME'] = $keyName;
        $this->result['INPUT_NAME'] = $keyName;
        $this->result['VARIANTS'] = $this->getReferenceElements($field);
        $this->result['TITLE'] = $externalKeyField->getTitle();

        if($externalKeyField instanceof ScalarField)
        {
            $this->result['DEFAULT'] = $externalKeyField->getDefaultValue();
            $this->result['REQUIRED'] = $externalKeyField->isRequired();
            if($this->result['REQUIRED'] && !$this->result['DEFAULT'] && $this->result['VARIANTS'])
            {
                $referenceItems = $this->result['VARIANTS'];
                $this->result['DEFAULT'] = array_shift($referenceItems)['ID'];
            }
        }
    }

    protected function getReferenceElements(Fields\Relations\Reference $field)
    {
        $result = array();

        $entityClass = $field->getRefEntity()->getDataClass();
        $select = $this->getReferenceSelectFields($field);

        if(Helper::isOrmEntityClass($entityClass) && $select)
        {
            $dbElements = $entityClass::getList(array(
                'select' => $select
            ));

            while($element = $dbElements->fetch())
            {
                $result[$element['ID']] = $element;
            }
        }

        return $result;
    }

    protected function getReferenceSelectFields(Fields\Relations\Reference $referenceField)
    {
        $selectFields = array();

        if($this->params['SELECT_FIELDS'])
        {
            $referenceFieldName = $referenceField->getName();

            foreach ($this->params['SELECT_FIELDS'] as $showFieldName)
            {
                if(preg_match('/^' . $referenceFieldName . '\.([A-z0-9_]+)$/', $showFieldName, $matches))
                {
                    $selectFields[] = $matches[1];
                }
            }
        }

        $selectFields[] = 'ID';

        return $selectFields;
    }
}