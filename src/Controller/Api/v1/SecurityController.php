<?php

namespace App\Controller\Api\v1;

use App\Repository\UserRepository;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SecurityController extends AbstractV1Controller
{
    /**
     * @Rest\Post("/login", name="login")
     * @param Request                      $request
     * @param UserPasswordEncoderInterface $encoder
     * @return JsonResponse
     */
    public function login(Request $request, UserRepository $userRepository, UserPasswordEncoderInterface $encoder): JsonResponse
    {
        $content = $request->getContent();
        $data    = json_decode($content, true);
        if (!is_array($data)) {
            return $this->error('Invalid json');
        }

        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';

        $user = $userRepository->findOneByUsername($username);
        if (empty($user)) {
            return $this->error(sprintf('No user with username "%s"', $username), 'Access denied');
        }

        if ($encoder->isPasswordValid($user, $password)) {
            return $this->json([
                'token' => $user->getToken()
            ]);
        }

        return $this->error('Invalid credentials', 'Access denied');
    }
}