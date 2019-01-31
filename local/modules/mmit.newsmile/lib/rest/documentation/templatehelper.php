<?

namespace Mmit\NewSmile\Rest\Documentation;

use Mmit\NewSmile\CommandVariable;

class TemplateHelper
{
    public static function getResultFieldHtml(CommandVariable\Base $field, $eol = ',<br>&emsp;&emsp;')
    {
        return  '<span class="field-code">' . $field->getCode() . '</span>' . ': ' . static::getFieldValueHtml($field) . $eol;
    }

    protected static function getSimpleFieldValueHtml(CommandVariable\Base $field)
    {
        return sprintf(
            '<span class="field-value"><%s, (%s%s)></span>',
            $field->getDescription(),
            $field->getTypeName(),
            $field->isRequired() ? '' : ', не обязательное'
        );
    }

    protected static function getObjectFieldValueHtml(CommandVariable\Object $field)
    {
        $result = [];

        foreach ($field->getShape() as $childField)
        {
            $result[] = static::getResultFieldHtml($childField);
        }

        return sprintf('%s{<br>%s}', static::getSimpleFieldValueHtml($field), implode(',<br>', $result));
    }

    protected static function getFieldValueHtml(CommandVariable\Base $field)
    {
        if($field instanceof CommandVariable\ArrayParam)
        {

        }
        elseif($field instanceof CommandVariable\Object)
        {
            $result = static::getObjectFieldValueHtml($field);
        }
        else
        {
            $result = static::getSimpleFieldValueHtml($field);
        }

        return $result;
    }
}