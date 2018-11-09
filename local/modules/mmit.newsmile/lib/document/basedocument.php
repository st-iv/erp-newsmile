<?

namespace Mmit\NewSmile\Document;

use Mmit\NewSmile\Helpers;

abstract class BaseDocument
{
    const TEMPLATE_FOLDER = __DIR__ . '/template';

    protected $template;
    protected $templateData;
    protected $id;
    protected $data = [];

    public function __construct($template = '')
    {
        $this->template = $template ?: 'main';
    }

    /**
     * Возвращает тип документа
     * @return string
     */
    public function getType()
    {
        return Helpers::getShortClassName(static::class);
    }

    /**
     * Загружает документ из базы данных
     * @param int $id - id документа
     *
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function load($id)
    {
        $this->id = $id;

        $dbDocument = DocumentTable::getByPrimary($id, [
            'filter' => [
                'TYPE' => $this->getType()
            ]
        ]);

        if($document = $dbDocument->fetch())
        {
            $this->templateData = $document['DATA'];
        }
        else
        {
            throw new \Exception($this->getName() . ' с id '. $id . ' не найден');
        }
    }

    /**
     * Записывает данные шаблона в поле класса, при записи провеяет установлены ли все параметры, которые требуются документу.
     * Если установлены не все - выдает исключение.
     *
     * @param array | \ArrayAccess $data - массив данных
     *
     * @throws \Exception
     */
    public function loadData($data)
    {
        foreach ($this->getParamsMap() as $paramName => $paramTitle)
        {
            if(isset($data[$paramName]))
            {
                $this->data[$paramName] = $data[$paramName];
            }
            else
            {
                throw new \Exception('Не указан параметр "' . $paramTitle . '" (' . $paramName . ')');
            }
        }
    }

    /**
     * Выводит документ на экран
     */
    public function printDoc()
    {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title><?=$this->getName()?></title>
        </head>
        <body>
            <?$this->printTemplate($this->template)?>
        </body>
        </html>
        <?
    }

    /**
     * Выводит документ на экран, используя указанный шаблон
     * @param string $template - код шаблона
     */
    public function printTemplate($template)
    {
        $templateFileName = strtolower(Helpers::getShortClassName(static::class)) . '_' . $template . '.php';

        $data = $this->templateData ?: $this->getTemplateData();
        include static::TEMPLATE_FOLDER . '/' . $templateFileName;
    }

    /**
     * Сохраняет документ в базу данных
     *
     * @return bool|int - id документа в случае успешного сохранения, иначе false
     * @throws \Exception
     */
    public function save()
    {
        $addResult = DocumentTable::add([
            'TYPE' => $this->getType(),
            'DATA' => $this->getTemplateData()
        ]);

        if($addResult->isSuccess())
        {
            return $addResult->getId();
        }
        else
        {
            return false;
        }
    }

    /**
     * Возвращает название документа
     * @return string
     */
    abstract public function getName();

    /**
     * Возвращает массив требуемых парметров в формате <код параметра> => <название параметра>
     * @return array
     */
    abstract public function getParamsMap();

    /**
     * Возвращает данные для отображения в шаблоне (переменная $data в файле шаблона)
     * @return mixed
     */
    abstract protected function getTemplateData();
}