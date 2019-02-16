<?php

namespace App\Controller;

use App\Bitrix\B24App;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends AbstractController
{

    public function test(B24App $b24App)
    {
        $result = [];
        try {
            $user = new \Bitrix24\User\User($b24App);
            $userResponse = $user->current();

            $activityData = [
                'CODE' => 'helloWorld', // символьный код нашего действия
                'HANDLER' => 'https://' . $_ENV['APP_DOMAIN'] . '/activities/test',// скрипт-обработчик действия
                'AUTH_USER_ID' => $userResponse['result']['ID'], // ID пользователя, токен которого будет передан приложению.
                'USE_SUBSCRIPTION' => '',
                'NAME' => [
                    'ru' => 'Hello World!' // название действия в редакторе БП
                ],
                'DESCRIPTION' => [
                    'ru' => 'Тестовое действие' // описание действия в редакторе БП
                ],
                'PROPERTIES' => [// массив входных параметров
                    'document_id' => [
                        'Name' => [
                            'ru' => 'ID документа'
                        ],
                        'Description' => [
                            'ru' => 'ID текущего документа'
                        ],
                        'Type' => 'int',
                        'Required' => 'Y',
                        'Multiple' => 'N',
                        'Default' => '',
                    ]
                ],
                'RETURN_PROPERTIES' => [// массив выходных параметров
                    'message' => [
                        'Name' => [
                            'ru' => 'Сообщение из приложения'
                        ],
                        'Type' => 'text',
                        'Multiple' => 'N',
                        'Default' => null
                    ],
                ]
            ];
            $result = $b24App->call('bizproc.activity.add', $activityData);
        } catch (\Exception $exception) {
            $result['error'] = $exception->getMessage();
        }

        return new Response('<pre>' . print_r($result, true) . '</pre>');
    }

    public function settings(): Response
    {
        return $this->render('settings.html.twig');
    }

    public function install(Request $request, B24App $B24App): Response
    {
        $errorsList = [];
        try {

            $data = [
                'domain' => $request->get('DOMAIN'),
                'access_token' => $request->get('AUTH_ID'),
                'refresh_token' => $request->get('REFRESH_ID'),
                'member_id' => $request->get('member_id')
            ];

            $B24App->addNewPortal($data);

        } catch (\Exception $exception) {
            $errorsList[] = $exception->getMessage();
        }

        return $this->render('install.html.twig', ['errorsList' => $errorsList]);
    }
}