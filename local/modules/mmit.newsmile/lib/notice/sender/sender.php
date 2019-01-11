<?

namespace Mmit\NewSmile\Notice\Sender;

use Mmit\NewSmile\User;

/**
 * Отправляет уведомления на определенный вид устройств
 * Interface Sender
 * @package Mmit\NewSmile\Notice\Sender
 */
interface Sender
{
    public function send(array $noticeData, User $user);
}