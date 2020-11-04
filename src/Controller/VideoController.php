<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints\Email;
use App\Entity\User;
use App\Entity\video;
use App\Services\JwtAuth;

class VideoController extends AbstractController
{

    private function resjson($data) {
        // Serializar datos con servicio serializer
        $json = $this->get('serializer')->serialize($data, 'json');

        // Response con httpfoundation
        $response = new Response();

        // Asignar contenido a la respuesta
        $response->setContent($json);

        // Indicar formato de respuesta
        $response->headers->set('Content-Type', 'application/json');

        // Devolver la respuesta
        return $response;
    }

    public function index(): Response
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/VideoController.php',
        ]);
    }

    public function create(Request $request, JwtAuth $jwt_auth) {
        // Recoger el token

        // Comprobar si es correcto

        // Recoger los datos por post

        // Recoger el objeto de usuario identificado

        // Comprobar y validar los datos

        // Guardar el nuevo video favorito en la base de datos

        // Devolver respuesta
        
        $data = [
            'status' => 'error',
            'code' => 400,
            'message' => 'El video no ha podido crearse',
        ];

        return $this->resjson($data);
    }
}
