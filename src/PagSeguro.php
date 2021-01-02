<?php

namespace LeoPersan;

use GuzzleHttp\Client;
use SimpleXMLElement;

class PagSeguro
{
    private $client = false;
    private $email = false;
    private $token = false;
    private $session = false;

    public function __construct($email, $token, $sandbox = false) {
        $this->email = $email;
        $this->token = $token;
        $this->client = new Client([
            'base_uri' => $sandbox ? 'https://ws.sandbox.pagseguro.uol.com.br/v2/' : 'https://ws.pagseguro.uol.com.br/v2/',
        ]);
    }

    public function startSession()
    {
        $this->session = $this->post('sessions?email='.$this->email.'&token='.$this->token)->id;
    }

    public function post($uri, array $options = [])
    {
        return new SimpleXMLElement(
            (string) $this->client->post($uri, $options)->getBody()
        );
    }

    public function getSession()
    {
        return $this->session;
    }

    public function importJavascript()
    {
        return str_replace('##SESSION##', $this->getSession(), file_get_contents(__DIR__.'/js/PagSeguro.js'));
    }
}
