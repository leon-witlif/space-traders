<?php

declare(strict_types=1);

namespace App\Controller;

use App\SpaceTrader\APIClient as SpaceTraderAPI;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AuthController extends AbstractController
{
    public function __construct(
        private readonly SpaceTraderAPI $spaceTraderApi,
    ) {
    }

    #[Route('/', 'app.register')]
    public function registerAction(Request $request): Response
    {
        $registerAgentForm = $this->createRegisterForm();

        $registerAgentForm->handleRequest($request);

        if ($registerAgentForm->isSubmitted() && $registerAgentForm->isValid()) {
            $data = $registerAgentForm->getData();

            $agent = $this->spaceTraderApi->registerAgent($data['symbol'], $data['faction']);
            // Until there is a way to store agents we use the session
            $request->getSession()->set('agentToken', $agent['data']['token']);

            return $this->redirectToRoute('app.agent.detail');
        }

        $parameters = [
            'registerAgentForm' => $registerAgentForm,
        ];

        return $this->render('register.html.twig', $parameters);
    }

    #[Route('/logout', 'app.logout')]
    public function logoutAction(Request $request): Response
    {
        $request->getSession()->remove('agentToken');

        return $this->redirectToRoute('app.register');
    }

    private function createRegisterForm(): FormInterface
    {
        return $this->createFormBuilder()
            ->add('symbol', TextType::class)
            ->add('faction', ChoiceType::class, ['choices' => ['COSMIC' => 'COSMIC']])
            ->add('submit', SubmitType::class)
            ->getForm();
    }
}
