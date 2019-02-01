<?

namespace Mmit\NewSmile\Rest\Documentation;

use Mmit\NewSmile\CommandVariable;

class TemplateHelper
{
    public static function getObjectFieldHtml(CommandVariable\Base $field)
    {
        $bComma = !($field instanceof CommandVariable\Object);

        return  '
            <div class="result-object__field">
                <div class="field-code">' . htmlspecialchars($field->getCode()) . '</div>' . '<div class="field-value ' . ($bComma ? 'field-value--with-comma' : '') . '">' . static::getFieldValueHtml($field) . '</div>
            </div>
        ';
    }

    protected static function getSimpleFieldValueHtml(CommandVariable\Base $field)
    {
        return sprintf(
            '%s, (%s%s)',
            $field->getDescription(),
            $field->getTypeName(),
            $field->isRequired() ? '' : ', не обязательное'
        );
    }

    protected static function getObjectFieldValueHtml(CommandVariable\Object $field, $bWithDescription = true)
    {
        $result = '';
        $shape = $field->getShape();

        if($shape)
        {
            foreach ($shape as $childField)
            {
                $result .= static::getObjectFieldHtml($childField);
            }

            if($field->isFlexible())
            {
                $result .= '<div class="result-object__field">...</div>';
            }

            $result = '
            <div class="result-object">' .
                $result .
            '</div>';
        }


        if($bWithDescription)
        {
            $result = static::getSimpleFieldValueHtml($field) . $result;
        }

        return $result;
    }

    public static function getArrayFieldValueHtml(CommandVariable\ArrayParam $field, $bWithDescription = true)
    {
        $result = $bWithDescription ? static::getSimpleFieldValueHtml($field) : '';
        $contentType = $field->getContentType();

        if(($contentType instanceof CommandVariable\Object) && $contentType->getShape())
        {
            $result .= '<div class="result-array">' . static::getObjectFieldValueHtml($contentType, false) . '</div>';
        }

        return $result;
    }

    protected static function getFieldValueHtml(CommandVariable\Base $field)
    {
        if($field instanceof CommandVariable\ArrayParam)
        {
            $result = static::getArrayFieldValueHtml($field);
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