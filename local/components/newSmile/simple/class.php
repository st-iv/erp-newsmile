<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

class SimpleComponent extends \CBitrixComponent
{
	public function executeComponent()
	{
        $this->includeComponentTemplate();

        if($this->arResult['RETURN'])
        {
            return $this->arResult['RETURN'];
        }
	}
}
?>