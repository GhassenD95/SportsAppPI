<?php

namespace App\Controller\Frontoffice;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class BaseController extends AbstractController
{
    protected function renderFrontoffice(string $view, array $parameters = []): Response
    {
        return $this->render('frontoffice/' . $view, $parameters);
    }
} 