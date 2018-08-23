<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader,
    Bitrix\Main\Type\Date,
    Bitrix\Main\Type\DateTime,
    Mmit\NewSmile\Status,
    Mmit\NewSmile\PatientCardTable,
    Mmit\NewSmile\DoctorTable;
use Mmit\NewSmile\FileTable;

class PatientCardEditComponent extends \CBitrixComponent
{

    protected function requestResult($request)
    {
        if (!empty($_FILES['FILE'])) {
            $arImage = $_FILES['FILE'];
            $arImage['MODULE_ID'] = 'mmit.newsmile';
            if (strlen($arImage["name"])>0) {
                $fid = CFile::SaveFile($arImage, "patient_card");
                if (intval($fid) > 0) {
                    FileTable::add([
                        'NAME' => $arImage["name"],
                        'FILE_ID' => $fid,
                        'PATIENT_ID' => $this->arParams['ID']
                    ]);
                }
            }
        }
    }

	/**
	 * получение результатов
	 */
	protected function getResult()
	{
        $this->getFiles();
	}

	protected function getFiles()
    {
        $arFiles = [];
        $rsResult = FileTable::getList([
            'filter' => [
                'PATIENT_ID' => $this->arParams['ID']
            ]
        ]);
        while ($arResult = $rsResult->fetch())
        {
            $arFiles[] = $arResult['FILE_ID'];
        }

        if (!empty($arFiles)) {
            $rsFiles = CFile::GetList(
                [],
                [
                    '@ID' => $arFiles,
                    'MODULE_ID' => 'mmit.newsmile'
                ]
            );
            while ($arFile = $rsFiles->Fetch())
            {
                $this->arResult['FILES'][] = $arFile;
            }
        }
    }



	/**
	 * выполняет логику работы компонента
	 */
	public function executeComponent()
	{
        if (!Loader::includeModule('mmit.newSmile')) die();
		try
		{
		    $this->requestResult($this->request);
			$this->getResult();
			$this->includeComponentTemplate();
		}
		catch (Exception $e)
		{
			ShowError($e->getMessage());
		}
	}
}
?>