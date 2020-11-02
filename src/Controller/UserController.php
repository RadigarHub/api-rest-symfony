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

class UserController extends AbstractController
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
        $user_repo = $this->getDoctrine()->getRepository(User::class);
        $video_repo = $this->getDoctrine()->getRepository(Video::class);

        $users = $user_repo->findAll();
        $user = $user_repo->find(1);
        $videos = $video_repo->findAll();

        $data = [
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/UserController.php',
        ];

        /*
        foreach($users as $user) {
            echo "<h1>{$user->getName()} {$user->getSurname()}</h1>";

            foreach($user->getVideos() as $video) {
                echo "<p>{$video->getTitle()} - {$video->getUser()->getEmail()}</p>";
            }
        }
        */
        
        //var_dump($user);
        //die();
        return $this->resjson($videos);
    }

    public function create(Request $request) {
        // Recoger los datos del post
        $json = $request->get('json', null);

        // Decodificar el json
        $params = json_decode($json);

        // Respuesta por defecto
        $data = [
            'status' => 'error',
            'code' => 400,
            'message' => 'El usuario no se ha creado',
        ];

        // Comprobar y validar los datos
        if ($json != null) {
            $name = !empty($params->name) ? $params->name : null;
            $surname = !empty($params->surname) ? $params->surname : null;
            $email = !empty($params->email) ? $params->email : null;
            $password = !empty($params->password) ? $params->password : null;

            $validator = Validation::createValidator();
            $validate_email = $validator->validate($email, [
                new Email()
            ]);

            if (!empty($email) && count($validate_email) == 0 && !empty($password) && !empty($name) && !empty($surname)) {
                // Si la validación es correcta, crear el objeto del usuario
                $user = new User();
                $user->setName($name);
                $user->setSurname($surname);
                $user->setEmail($email);
                $user->setRole('ROLE_USER');
                $user->setCreatedAt(new \DateTime('now'));

                // Cifrar la contraseña
                $pwd = hash('sha256', $password);
                $user->setPassword($pwd);

                // Comprobar si el usuario ya existe (duplicados)
                $doctrine = $this->getDoctrine();
                $entityManager = $doctrine->getManager();

                $user_repo = $doctrine->getRepository(User::class);
                $isset_user = $user_repo->findBy(array(
                    'email' => $email
                ));

                if (count($isset_user) == 0) {
                    // Si no existe, guardarlo en la base de datos
                    $entityManager->persist($user);     // Sólo lo guarda en el ORM
                    $entityManager->flush();            // Ya sí lo guarda en la Base de datos

                    $data = [
                        'status' => 'success',
                        'code' => 200,
                        'message' => 'El usuario se ha creado correctamente',
                        'user' => $user,
                    ];
                } else {
                    $data = [
                        'status' => 'error',
                        'code' => 400,
                        'message' => 'El usuario ya existe',
                    ];
                }
            }
        }

        // Devolver respuesta en json
        return new JsonResponse($data);
    }

    public function login(Request $request) {
        // Recibir los datos por post
        $json = $request->get('json', null);

        // Decodificar el json
        $params = json_decode($json);

        // Respuesta por defecto
        $data = [
            'status' => 'error',
            'code' => 400,
            'message' => 'El usuario no se ha podido identificar',
        ];

        // Comprobar y validar los datos
        if ($json != null) {
            $email = !empty($params->email) ? $params->email : null;
            $password = !empty($params->password) ? $params->password : null;
            $gettoken = !empty($params->gettoken) ? $params->gettoken : null;
            
            $validator = Validation::createValidator();
            $validate_email = $validator->validate($email, [
                new Email()
            ]);

            if (!empty($email) && !empty($password) && count($validate_email) == 0) {
                // Cifrar la contraseña
                $pwd = hash('sha256', $password);
        
                // Si todo es válido, llamaremos a un servicio para identificar al usuario (jwt, token o un objeto)

                // Crear servicio de jwt
                $data = [
                    'message' => 'Validación correcta'
                ];
            } else {
                $data = [
                    'message' => 'Validación incorrecta'
                ];
            }
        }

        // Devolver respuesta en formato json
        return $this->json($data);
    }
}
