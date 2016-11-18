<?php

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class JWTCreatedListener
 */
class JWTCreatedListener
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @param JWTCreatedEvent $event
     *
     * @return void
     */
    public function onJWTCreated(JWTCreatedEvent $event)
    {
        $request = $this->requestStack->getCurrentRequest();

        $payload       = $event->getData();
        $payload['ip'] = $request->getClientIp();

        $expiration = new \DateTime('+1 hour');
//        $expiration->setTime(2, 0, 0);

        $payload['exp'] = $expiration->getTimestamp();

        $event->setData($payload);
    }
}