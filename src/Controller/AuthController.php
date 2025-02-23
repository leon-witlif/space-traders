<?php

declare(strict_types=1);

namespace App\Controller;

use App\SpaceTrader\GlobalApi;
use App\Storage\AgentStorage;
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
        private readonly GlobalApi $globalApi,
        private readonly AgentStorage $agentStorage,
    ) {
    }

    #[Route('/', 'app.auth.index')]
    public function indexAction(): Response
    {
        $parameters = [
            'registerAgentForm' => $this->createRegisterForm(),
            'loginAgentForm' => $this->createLoginForm(),
        ];

        return $this->render('auth.html.twig', $parameters);
    }

    #[Route('/register', 'app.auth.register', methods: ['POST'])]
    public function registerAction(Request $request): Response
    {
        $registerAgentForm = $this->createRegisterForm();

        $registerAgentForm->handleRequest($request);

        if ($registerAgentForm->isSubmitted() && $registerAgentForm->isValid()) {
            $data = $registerAgentForm->getData();

            $agentToken = $this->globalApi->register($data['faction'], $data['symbol'])['token'];

            $this->agentStorage->add(['token' => $agentToken, 'symbol' => $data['symbol']]);
        }

        return $this->redirectToRoute('app.auth.index');
    }

    #[Route('/login', 'app.auth.login', methods: ['POST'])]
    public function loginAction(Request $request): Response
    {
        $loginAgentForm = $this->createLoginForm();

        $loginAgentForm->handleRequest($request);

        if ($loginAgentForm->isSubmitted() && $loginAgentForm->isValid()) {
            $data = $loginAgentForm->getData();
            $agent = $this->agentStorage->get($data['symbol']);

            $request->getSession()->set('agentToken', $agent['token']);

            return $this->redirectToRoute('app.agent.detail');
        }

        return $this->render('app.auth.index');
    }

    #[Route('/logout', 'app.auth.logout')]
    public function logoutAction(Request $request): Response
    {
        $request->getSession()->remove('agentToken');

        return $this->redirectToRoute('app.auth.index');
    }

    private function createRegisterForm(): FormInterface
    {
        return $this->createFormBuilder(options: ['action' => $this->generateUrl('app.auth.register')])
            ->add('symbol', TextType::class)
            ->add('faction', ChoiceType::class, ['choices' => ['COSMIC' => 'COSMIC']])
            ->add('submit', SubmitType::class, ['label' => 'Register'])
            ->getForm();
    }

    private function createLoginForm(): FormInterface
    {
        $agentChoices = [];

        foreach ($this->agentStorage->list() as $agent) {
            $agentChoices[$agent['symbol']] = $agent['symbol'];
        }

        return $this->createFormBuilder(options: ['action' => $this->generateUrl('app.auth.login')])
            ->add('symbol', ChoiceType::class, ['choices' => $agentChoices])
            ->add('submit', SubmitType::class, ['label' => 'Login'])
            ->getForm();
    }
}
