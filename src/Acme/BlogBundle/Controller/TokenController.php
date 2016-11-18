<?php

namespace Acme\BlogBundle\Controller;

use Acme\BlogBundle\Entity\User;
use Acme\BlogBundle\Security\TokenUserProvider;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use JMS\DiExtraBundle\Annotation as DI;

///**
// * Class BaseRepository.
// *
// * @DI\Service("token_controller", abstract=true)
// */
class TokenController extends Controller
{

//    /**
//     * BaseRepository constructor.
//     *
//     * @DI\InjectParams({
//     *    "securityTokenStorage" = @DI\Inject("security.token_storage"),
//     * })
//     */
//    public function __construct(TokenStorageInterface $securityTokenStorage)
//    {
//        $this->securityTokenStorage = $securityTokenStorage;
//    }

    /**
     * @Route("/tokens")
     * @Method("POST")
     */
    public function newTokenAction(Request $request)
    {

        $user = $this->getDoctrine()
                     ->getRepository('AcmeBlogBundle:User')
                     ->findOneBy(['email' => $request->get('email')]);
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
//                [
//                    'email'  => $user->getEmail(),
//                    'role'   => $user->getRoles(),
//                    'userid' => $user->getId(),
//                ]
            );
        
        return new JsonResponse(['token' => $token]);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @Route("/create_user")
     * @Method("POST")
     */
    public function createUserAction(Request $request) {

        $factory = $this->get('security.encoder_factory');

        $user = new User();

        $encoder = $factory->getEncoder($user);
        $user->setSalt(md5(microtime()));
        $pass = $encoder->encodePassword($request->get('password'), $user->getSalt());
        $user->setEmail($request->get('email'));
        $user->setPassword($pass);
//        $user->setActive(1); //enable or disable

        $em = $this->getDoctrine()->getEntityManager();
        $em->persist($user);
        $em->flush();

        return new JsonResponse('Sucessful');
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @Route("/login_with_google_token")
     * @Method("GET")
     */
    public function login_with_google_token(Request $request)
    {
        $client = new \GuzzleHttp\Client();
        $res    = $client->request(
            'GET',
            'https://www.googleapis.com/oauth2/v2/userinfo?access_token='.$request->get('access_token')
        );
        $result = json_decode($res->getBody());
        $user   = $this->getDoctrine()->getRepository('AcmeBlogBundle:User')->findOneBy(['email' => $result->email]);
        if (!$user) {
            $user = new User();
            $user->setEmail($result->email);

            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($user);
            $em->flush();
        }

        $token = $this->get('lexik_jwt_authentication.encoder')
                      ->encode(
                          [
                              'email'  => $user->getEmail(),
                              'role'   => $user->getRoles(),
                              'userId' => $user->getId(),
                              'ttl'    => $this->getParameter('token_ttl'),
                          ]
                      );

        return new JsonResponse(
            [
                'status'  => 'Login successful',
                'success' => true,
                'token'   => $token
            ]
        );

        //        'https://www.googleapis.com/oauth2/v2/userinfo?access_token='+req.query.access_token
        // http://localhost:8088/api/v1/login_with_google_token?access_token=ya29.CjGYA3wEnnE6y4p41PmgFp7OW9yddxOTlrkDMHQAQ48RSmbGPRXNcO9HocDbp6cS_G2i

    }

    /**
     *
     * @Route("/users/{email}")
     * @Method("GET")
     */
    public function getRegisteredUser($email, Request $request)
    {
        $user = $this->container->get('jwt_token_authenticator')->getUser(
            $request->headers->get('x-access-token'),
            new TokenUserProvider($this->get('logger'))
        );
        if ($user) {
            return new JsonResponse(
                ['user' => $user]
            );
        }
    }
}
