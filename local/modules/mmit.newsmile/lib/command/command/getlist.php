<?


namespace Mmit\NewSmile\Command\Command;

use Bitrix\Main\Diag\Debug;
use Mmit\NewSmile\Command;
use Mmit\NewSmile\CommandParam\ArrayParam;

class GetList extends Command\Base
{
    protected function doExecute()
    {
        foreach ($this->params['commands'] as $commandInfo)
        {
            $commandClass = static::getClassByCode($commandInfo['code']);

            /**
             * @var Command\Base $command
             */
            $command = new $commandClass($commandInfo['params'], $commandInfo['varyParam']);

            if($command->isAvailable())
            {
                $result = [
                    'name' => $command::getName(),
                    'variants' => $command->getVariants(),
                    'available' => true
                ];
            }
            else
            {
                $result = [
                    'available' => false
                ];
            }

            $result['code'] = $commandInfo['code'];
            $this->result[] = $result;
        }
    }


    public function getParamsMap()
    {
        return [
            new ArrayParam('commands', 'список команд', '', true)
        ];
    }
}