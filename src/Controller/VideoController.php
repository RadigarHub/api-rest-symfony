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
use DateTime;

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
        // Respuesta por defecto
        $data = [
            'status' => 'error',
            'code' => 400,
            'message' => 'El video no ha podido crearse',
        ];

        // Recoger el token
        $token = $request->headers->get('Authorization', null);

        // Comprobar si es correcto
        $authCheck = $jwt_auth->checkToken($token);

        if ($authCheck) {
            // Recoger los datos por post
            $json = $request->get('json', null);
            $params = json_decode($json);
    
            // Recoger el objeto de usuario identificado
            $identity = $jwt_auth->checkToken($token, true);
    
            // Comprobar y validar los datos
            if (!empty($json)) {
                $user_id = ($identity->sub != null) ? $identity->sub : null;
                $title = (!empty($params->title)) ? $params->title : null;
                $description = (!empty($params->description)) ? $params->description : null;
                $url = (!empty($params->url)) ? $params->url : null;

                if (!empty($user_id) && !empty($title) && !empty($url)) {
                    // Guardar el nuevo video favorito en la base de datos
                    $entityManager = $this->getDoctrine()->getManager();
                    $user = $this->getDoctrine()->getRepository(User::class)->findOneBy([
                        'id' => $user_id
                    ]);

                    // Crear el objeto video
                    $video = new Video();
                    $video->setUser($user);
                    $video->setTitle($title);
                    $video->setDescription($description);
                    $video->setUrl($url);
                    $video->setStatus('Normal');

                    $createdAt = new \DateTime('now');
                    $updatedAt = new \DateTime('now');
                    $video->setCreatedAt($createdAt);
                    $video->setUpdatedAt($updatedAt);

                    // Guardarlo en la base de datos
                    $entityManager->persist($video);
                    $entityManager->flush();
            
                    // Devolver respuesta
                    $data = [
                        'status' => 'success',
                        'code' => 200,
                        'message' => 'El video se ha guardado correctamente',
                        'video' => $video,
                    ];
                }
            }
        }

        return $this->resjson($data);
    }

    public function videos(Request $request, JwtAuth $jwt_auth) {
        // Respuesta por defecto
        $data = [
            'status' => 'error',
            'code' => 400,
            'message' => 'No se pueden listar los vídeos en este momento',
        ];

        // Recoger la cabecera de autenticación

        // Comprobar si es correcta

        // Conseguir la identidad del usuario

        // Configurar el bundle de paginación

        // Hacer una consulta para paginar

        // Recoger el parámetro page de la url

        // Invocar paginación

        // Preparar array de datos para devolver

        return $this->resjson($data);
    }
}
