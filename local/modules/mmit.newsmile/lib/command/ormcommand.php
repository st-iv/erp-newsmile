<?

namespace Mmit\NewSmile\Command;

use Bitrix\Main\ORM\Entity;

interface OrmCommand
{
    /**
     * Возвращает ORM сущность, с которой будет работать команда
     * @return Entity
     */
    public function getOrmEntity();
}