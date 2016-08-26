<?php

namespace Acme\BlogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

class TokenController extends Controller
{
    /**
     * @Route("/tokens")
     * @Method("POST")
     */
    public function newTokenAction(Request $request)
    {

        $user = $this->getDoctrine()
                     ->getRepository('AcmeBlogBundle:User')
                     ->findOneBy(['username' => $request->get('username')]);
        if (!$user) {
            throw $this->createNotFoundException();
        }

        $isValid = $this->get('security.password_encoder')
                        ->isPasswordValid($user, $request->get('password'));

        if (!$isValid) {
            throw new BadCredentialsException();
        }

        $token = $this->get('lexik_jwt_authentication.encoder')
            ->encode(
                [
                    'username' => $user->getUsername(),
                    'role'     => $user->getRoles(),
                    'userId'   => $user->getId(),
                ]
            );
        
        return new JsonResponse(['token' => $token]);
    }
}
