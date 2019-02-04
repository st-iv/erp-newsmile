<?


namespace Mmit\NewSmile\Command\Command;

use Mmit\NewSmile\Command;
use Mmit\NewSmile\CommandVariable\ArrayParam;
use Mmit\NewSmile\CommandVariable\Object;
use Mmit\NewSmile\CommandVariable\String;

class GetList extends Command\Base
{
    public function getDescription()
    {
        return 'Возвращает информацию о командах, указанных в параметре commands';
    }

    public function getParamsMap()
    {
        return [
            (new ArrayParam('commands', 'список команд', true))->setContentType(
                (new Object('', ''))->setShape([
                    new String('code', 'код команды', true),
                    new Object('params', 'параметры команды', true),
                    new String('varyParam', 'код вариативного параметра')
                ])
            )
        ];
    }

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
}