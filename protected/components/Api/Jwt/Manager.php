<?php

namespace Api\Jwt;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\ValidationData;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Token;

class Manager
{
    private $salt;
    private $signer;
    private $validator;
    private $parser;

    public function __construct($salt)
    {
        $this->salt = $salt;
        $this->signer = new Sha256();
        $this->validator = new ValidationData();
        $this->parser = new Parser();
    }

    public function tokenize(array $attributes)
    {
        $builder = (new Builder());

        foreach ($attributes as $key => $value) {
            $builder->set($key, $value);
        }

        $builder->sign($this->signer, $this->salt);

        return $builder->getToken();
    }

    public function parse($token)
    {
        if (is_string($token)) {
            return $this->parser->parse($token);
        } elseif (is_object($token) && ($token instanceof Token)) {
            return $token;
        }

        throw new \Exception('unknown jwt token!');
    }

    public function isValid($token)
    {
        $token = $this->parse($token);

        return $token->verify($this->signer, $this->salt)
            && $token->validate($this->validator);
    }

    public function renew($token)
    {
        if ( ! $this->isValid($token)) {
            throw new \Exception('jwt is not valid!');
        }

        $token = $this->parse($token);

        $attributes = [];
        foreach ($token->getClaims() as $key => $claim) {
            $attributes[$key] = $claim->getValue();
        }

        return $this->tokenize($attributes);
    }
}
