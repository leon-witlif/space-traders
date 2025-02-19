<?php

declare(strict_types=1);

namespace App\Controller;

use App\SpaceTrader\APIClient as SpaceTraderAPI;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/contract/', 'app.contract.')]
class ContractController extends AbstractController
{
    public function __construct(
        private readonly SpaceTraderAPI $spaceTraderApi,
    ) {
    }

    #[Route('accept/{contract}', 'accept')]
    public function acceptAction(Request $request): Response
    {
        $agent = $request->getSession()->get('agentToken');
        $contract = $request->get('contract');

        $this->spaceTraderApi->acceptContract($agent, $contract);

        return $this->redirectToRoute('app.index');
    }
}
