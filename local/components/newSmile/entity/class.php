<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

class PatientCardComponent extends \CBitrixComponent
{
	/**
	 * шаблоны путей по умолчанию
	 * @var array
	 */
	protected $defaultUrlTemplates404 = array();
	
	/**
	 * переменные шаблонов путей
	 * @var array
	 */
	protected $componentVariables = array();
	
	/**
	 * страница шаблона
	 * @var string
	 */
	protected $page = '';

	/**
	 * определяет переменные шаблонов и шаблоны путей
	 */
	protected function setSefDefaultParams()
	{
		$this->defaultUrlTemplates404 = array(
		    'groups' => '',
		    'group' => 'view/#SECTION_ID#/',
		    'element' => 'view/#SECTION_ID#/#ELEMENT_ID#/',
            'edit_group' => 'edit/#SECTION_ID#/',
            'edit_element' => 'edit/#SECTION_ID#/#ELEMENT_ID#/',
            'add_group' => 'add_group/',
            'add_element' => 'add_element/',
		);
		$this->componentVariables = array(
		    'ACTION',
		    'ELEMENT_ID',
		    'SECTION_ID'
        );
	}
	
	/**
	 * получение результатов
	 */
	protected function getResult()
	{
		$urlTemplates = array();
		if ($this->arParams['SEF_MODE'] == 'Y')
		{
			$variables = array();
			$urlTemplates = \CComponentEngine::MakeComponentUrlTemplates(
				$this->defaultUrlTemplates404,
				$this->arParams['SEF_URL_TEMPLATES']
			);
			$variableAliases = \CComponentEngine::MakeComponentVariableAliases(
				$this->defaultUrlTemplates404,
				$this->arParams['VARIABLE_ALIASES']
			);

			$engine = new CComponentEngine($this);

			$this->page = $engine->guessComponentPath(
				$this->arParams['SEF_FOLDER'],
				$urlTemplates,
				$variables
			);
		
		    if (strlen($this->page) <= 0)
		        $this->page = 'groups';
		
		    \CComponentEngine::InitComponentVariables(
		    	$this->page,
		    	$this->componentVariables,
                $variableAliases,
		    	$variables
			);
		}
		else
		{
		    $this->page = 'groups';
		}

		if($this->page == 'add_group')
        {
            $this->page = 'edit_group';
        }

        if($this->page == 'add_element')
        {
            $this->page = 'edit_element';
        }
		
		$this->arResult = array(
		   'FOLDER' => $this->arParams['SEF_FOLDER'],
		   'URL_TEMPLATES' => $urlTemplates,
		   'VARIABLES' => $variables,
		   'ALIASES' => $variableAliases
		);
	}
	
	/**
	 * выполняет логику работы компонента
	 */
	public function executeComponent()
	{
		try
		{
			$this->setSefDefaultParams();
			$this->getResult();
			$this->includeComponentTemplate($this->page);
		}
		catch (Exception $e)
		{
			ShowError($e->getMessage());
		}
	}
}
?>