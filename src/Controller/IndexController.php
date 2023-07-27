<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response as Response;
use Symfony\Component\Routing\Annotation\Route as Route;

class IndexController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return new Response('<html><body>Hello World</body></html>');
    }
}
