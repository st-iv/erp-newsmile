<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

class NewSmileDocumentComponent extends CBitrixComponent
{
    const DOCS_NAMESPACE = '\\Mmit\\NewSmile\\Document\\';

    public function executeComponent()
    {
        $docType = $this->request['type'];

        if(!strlen($docType))
        {
            echo 'Не определен тип документа';
            return;
        }

        $documentClass = static::DOCS_NAMESPACE . \Mmit\NewSmile\Helpers::getCamelCase($docType);

        if(!class_exists($documentClass) || !is_subclass_of($documentClass, static::DOCS_NAMESPACE . 'BaseDocument'))
        {
            echo 'Указанный тип документа не найден в системе';
            return;
        }

        /**
         * @var \Mmit\NewSmile\Document\BaseDocument $document
         */
        $document = new $documentClass($this->request['template']);

        if($this->request['id'])
        {
            $document->load($this->request['id']);
        }
        else
        {
            $document->loadData($this->request);
        }

        $document->printDoc();
    }
}