<?php

namespace Acme\BlogBundle\Security;

use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

/**
 * Class PasswordEncoder
 *
 * @package Mindk\PublishBundle\Security
 */
class PasswordEncoder implements PasswordEncoderInterface
{
    public function encodePassword($raw, $salt)
    {
        return $salt?md5($raw.$salt):md5($raw);
    }

    public function isPasswordValid($encoded, $raw, $salt)
    {
        return $encoded === $this->encodePassword($raw, $salt);
    }
}
