<?

namespace Mmit\NewSmile\Access\Entity;

use Bitrix\Main\AccessDeniedException;
use Mmit\NewSmile\Access\Operation\OperationTable;
use Mmit\NewSmile\Error;
use Mmit\NewSmile\Helpers;
use Mmit\NewSmile\User;

abstract class Controller
{
    /**
     * @var User
     */
    protected $user;
    protected $operations;
    protected $entityCode;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->operations = OperationTable::getByEntityCode($this->entityCode);
        $this->entityCode = Helpers::getSnakeCase(Helpers::getShortClassName(static::class), false, '-');
    }

    public function getEntityCode()
    {
        return $this->entityCode;
    }

    public function processOperation($code, $variant = null)
    {
        $this->checkOperationExists($code);

        if(!$this->isOperationAvailable($code))
        {
            throw new Error('Доступ к операции ' . $code . '(' . $this->entityCode . ')' . ' запрещен', 'OPERATION_ACCESS_DENIED');
        }

        return $this->doOperation($code, $variant);
    }

    protected function checkOperationExists($code)
    {
        if(!isset($this->operations[$code]))
        {
            throw new Error('Операция ' . $code . ' не поддерживается для ' . $this->entityCode, 'UNSUPPORTED_OPERATION');
        }

        return true;
    }

    public function isOperationAvailable($code)
    {
        //$this->user->isOperationAvailable($this->entityCode, $code);
    }

    //abstract public function doOperation($code, $variant);
    //abstract public function isOperationAvailable($code);
    //abstract public function getOperationVariants($code, $entityId = null);
}