<?

namespace Mmit\NewSmile\Notice\Sender;

use Bitrix\Main\Diag\Debug;
use Mmit\NewSmile\User;
use Mmit\NewSmile\Notice;

/**
 * Отправляет уведомления в мобильное приложение
 * Class Mobile
 * @package Mmit\NewSmile\Notice\Sender
 */
class Mobile implements Sender
{
    const SERVER_KEY = 'AAAAkb2-eZs:APA91bEsXejLf5YAeq6VjSWWY8vm5fehtoRU6VkDhxVRIrbbC8noKYV-enDkJ6vwnVG2lLU82aV0Lh_CqrcDUq0XX29LHQYkFzucFYoXlzq67ImP5NrOBi-Z33vABzOJKYLhcEPaLjOn';

    public function send(array $noticeData, User $user)
    {
        $userFields = $user->getBitrixUserFields(['UF_FIREBASE_TOKEN']);
        $tokens = $userFields['UF_FIREBASE_TOKEN'];

        if(!$tokens) return;


        $multiChannel = curl_multi_init();
        $channels = [];

        $this->prepareNoticeParams();

        /*
         * Подготавливаем запросы для отправки уведомления на каждое устройство пользователя
         * */
        foreach ($tokens as $token)
        {
            $tokenInfo = Notice\Helper::getNotificationTokenInfo($token);
            $queryFields = [
                'to' => $tokenInfo['CLEAN_TOKEN'],
                'data' => [
                    'notification_type' => $noticeData['TYPE'],
                    'params' => $noticeData['PARAMS']
                ]
            ];

            switch ($tokenInfo['DEVICE'])
            {
                case 'android':
                    $queryFields['data']['title'] = $noticeData['TITLE'];
                    $queryFields['data']['description'] = $noticeData['TEXT'];
                    break;

                case 'ios':
                    $queryFields['notification']['title'] = $noticeData['TITLE'];
                    $queryFields['notification']['body'] = $noticeData['TEXT'];
            }

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, [
                'Authorization: key=' . static::SERVER_KEY,
                'Content-Type: application/json'
            ]);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($queryFields));

            curl_multi_add_handle($multiChannel, $curl);
            $channels[] = $curl;
        }

        $active = null;

        /*
         * запускаем запросы и ждём их выполнения
         * */
        do
        {
            $mrc = curl_multi_exec($multiChannel, $active);

        }
        while ($mrc == CURLM_CALL_MULTI_PERFORM);


        while ($active && $mrc == CURLM_OK)
        {
            if (curl_multi_select($multiChannel) != -1)
            {
                do
                {
                    $mrc = curl_multi_exec($multiChannel, $active);
                }
                while ($mrc == CURLM_CALL_MULTI_PERFORM);
            }
        }

        /* закрываем соединение */

        foreach ($channels as $channel)
        {
            curl_multi_remove_handle($multiChannel, $channel);
        }

        curl_multi_close($multiChannel);
    }

    protected function prepareNoticeParams($params)
    {

    }
}