<?php

namespace App\Services;

use Firebase\JWT\JWT;
use App\Entity\User;

class JwtAuth {

  public $manager;
  public $key;

  public function __construct($manager) {
    $this->manager = $manager;
    $this->key = 'hola_que_tal_este_es_el_master_fullstack_1234567890';
  }

  public function signup($email, $password, $gettoken = null) {
    // Comprobar si el usuario existe
    $user = $this->manager->getRepository(User::class)->findOneBy([
      'email' => $email,
      'password' => $password,
    ]);

    $signup = false;
    if (is_object($user)) {
      $signup = true;
    }

    // Si existe, generar el token jwt
    if ($signup) {
      $token = [
        'sub' => $user->getId(),
        'name' => $user->getName(),
        'surname' => $user->getSurname(),
        'email' => $user->getEmail(),
        'iat' => time(),
        'exp' => time() + (7 * 24 * 60 * 60), // 1 Semana de tiempo de expiración
      ];

      // Comprobar el flag gettoken, condición
      $jwt = JWT::encode($token, $this->key, 'HS256');
      if ($gettoken) {
        $data = $jwt;
      } else {
        $decoded = JWT::decode($jwt, $this->key, ['HS256']);
        $data = $decoded;
      }
      
    } else {
      $data = [
        'status' => 'error',
        'message' => 'Login incorrecto',
      ];
    }

    // Devolver los datos
    return $data;
  }

}