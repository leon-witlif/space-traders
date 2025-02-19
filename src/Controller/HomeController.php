<?php

declare(strict_types=1);

namespace App\Controller;

use App\SpaceTrader\APIClient as SpaceTraderAPI;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private readonly SpaceTraderAPI $spaceTraderApi,
    ) {
    }

    #[Route('/', 'app.index')]
    public function indexAction(): Response
    {
        // dd($this->spaceTraderApi->registerAgent('SP4CE_TR4DER'));
        $agent = $this->spaceTraderApi->loadAgent('SP4CE_TR4DER');
        dd($this->spaceTraderApi->loadContracts($agent));

        return $this->render('base.html.twig');
    }
}
