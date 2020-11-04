<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints\Email;
use Knp\Component\Pager\PaginatorInterface;
use App\Entity\User;
use App\Entity\Video;
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

    public function create(Request $request, JwtAuth $jwt_auth, $id = null) {
        // Respuesta por defecto
        $data = [
            'status' => 'error',
            'code' => 400,
            'message' => 'El video no ha podido crearse o actualizarse',
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

                    // Guardar nuevo vídeo
                    if ($id == null) {
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

                    // Editar vídeo existente
                    } else {
                        // Obtener el vídeo de la base de datos
                        $video = $this->getDoctrine()->getRepository(Video::class)->findOneBy([
                            'id' => $id,
                            'user' => $identity->sub
                        ]);

                        if ($video && is_object($video)) {
                            // Modificar los valores del vídeo con los parámetros que recibimos de la petición
                            $video->setTitle($title);
                            $video->setDescription($description);
                            $video->setUrl($url);
        
                            $updatedAt = new \DateTime('now');
                            $video->setUpdatedAt($updatedAt);

                            // Actualizar el video en la base de datos
                            $entityManager->persist($video);
                            $entityManager->flush();

                            // Devolver respuesta
                            $data = [
                                'status' => 'success',
                                'code' => 200,
                                'message' => 'El vídeo se ha actualizado correctamente',
                                'vídeo' => $video,
                            ];
                        }
                    }

                }
            }
        }

        return $this->resjson($data);
    }

    public function videos(Request $request, JwtAuth $jwt_auth, PaginatorInterface $paginator) {
        // Respuesta por defecto
        $data = [
            'status' => 'error',
            'code' => 400,
            'message' => 'No se pueden listar los vídeos en este momento',
        ];

        // Recoger la cabecera de autenticación
        $token = $request->headers->get('Authorization');

        // Comprobar si es correcta
        $authCheck = $jwt_auth->checkToken($token);

        if ($authCheck) {
            // Conseguir la identidad del usuario
            $identity = $jwt_auth->checkToken($token, true);

            $entityManager = $this->getDoctrine()->getManager();
            
            // Configurar el bundle de paginación
            // Hecho en el fichero services.yaml

            // Hacer una consulta para paginar
            $dql = "SELECT v FROM App\Entity\Video v WHERE v.user = {$identity->sub} ORDER BY v.id DESC";
            $query = $entityManager->createQuery($dql);
            
            // Recoger el parámetro page de la url
            $page = $request->query->getInt('page', 1);
            $items_per_page = 5;
    
            // Invocar paginación
            $pagination = $paginator->paginate($query, $page, $items_per_page);
            $total = $pagination->getTotalItemCount();
    
            // Preparar array de datos para devolver
            $data = [
                'status' => 'success',
                'code' => 200,
                'total_items_count' => $total,
                'page_actual' => $page,
                'items_per_page' => $items_per_page,
                'total_pages' => ceil($total / $items_per_page),
                'videos' => $pagination,
                'user_id' => $identity->sub
            ];
        }

        return $this->resjson($data);
    }

    public function video(Request $request, JwtAuth $jwt_auth, $id = null) {
        // Respuesta por defecto
        $data = [
            'status' => 'error',
            'code' => 400,
            'message' => 'Vídeo no encontrado',
        ];

        // Sacar el token
        $token = $request->headers->get('Authorization');

        // Comprobar si es correcto
        $authCheck = $jwt_auth->checkToken($token);

        if ($authCheck) {
            // Sacar la identidad del usuario
            $identity = $jwt_auth->checkToken($token, true);

            // Sacar el objeto del vídeo en base al id de la url
            $video = $this->getDoctrine()->getRepository(Video::class)->findOneBy([
                'id' => $id
            ]);
    
            // Comprobar si el vídeo existe y es propiedad del usuario identificado
            if ($video && is_object($video) && $identity->sub == $video->getUser()->getId()) {
                // Devolver una respuesta
                $data = [
                    'status' => 'success',
                    'code' => 200,
                    'vídeo' => $video,
                ];
            }
        }

        return $this->resjson($data);
    }

    public function remove(Request $request, JwtAuth $jwt_auth, $id = null) {
        // Respuesta por defecto
        $data = [
            'status' => 'error',
            'code' => 400,
            'message' => 'Vídeo no encontrado',
        ];

        // Sacar el token
        $token = $request->headers->get('Authorization');

        // Comprobar si es correcto
        $authCheck = $jwt_auth->checkToken($token);

        if ($authCheck) {
            // Sacar la identidad del usuario
            $identity = $jwt_auth->checkToken($token, true);

            $doctrine = $this->getDoctrine();
            $entityManager = $doctrine->getManager();

            // Obtener el vídeo que queremos borrar en base al id de la url
            $video = $doctrine->getRepository(Video::class)->findOneBy([
                'id' => $id
            ]);

            // Comprobar si el vídeo existe y es propiedad del usuario identificado
            if ($video && is_object($video) && $identity->sub == $video->getUser()->getId()) {
                // Borrar el vídeo
                $entityManager->remove($video);
                $entityManager->flush();

                // Devolver una respuesta
                $data = [
                    'status' => 'success',
                    'code' => 200,
                    'vídeo' => $video,
                ];
            }
        }

        return $this->resjson($data);
    }
}
