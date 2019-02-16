<?php

namespace App\Controller;

use App\Bitrix\B24App;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ActivityController extends AbstractController
{
    public function test(B24App $b24App, Request $request): Response
    {
        $message = 'Hello world! Обрабатываем документ - ' . $request->get('properties')['document_id'];

        $requestData = [
            'event_token' => $request->get('event_token'),
            'return_values' => [
                'message' => $message
            ]
        ];

        $b24App->call('bizproc.event.send', $requestData);

        return new Response('Ok');
    }
}