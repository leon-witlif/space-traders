<?php

declare(strict_types=1);

namespace App\Controller;

use App\Handler\AgentHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

class AgentController extends AbstractController
{
    public function __construct(
        private readonly AgentHandler $agentHandler,
    ) {
    }

    #[Route('/agent', 'app.agent.detail')]
    public function detailAction(Request $request): Response
    {
        if ($request->getSession()->has('agentToken')) {
            $agentToken = $request->getSession()->get('agentToken');

            return $this->render('agent.html.twig', $this->agentHandler->load($agentToken));
        }

        throw new NotFoundHttpException();
    }
}
