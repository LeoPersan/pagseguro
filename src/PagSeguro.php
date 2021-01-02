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
            'base_uri' => $sandbox ? 'https://ws.sandbox.pagseguro.uol.com.br/' : 'https://ws.pagseguro.uol.com.br/',
        ]);
    }

    public function startSession()
    {
        $this->session = $this->postXml('v2/sessions?email='.$this->email.'&token='.$this->token)->id;
    }

    public function postXml($uri, array $options = [])
    {
        return new SimpleXMLElement(
            (string) $this->client->post($uri, $options)->getBody()
        );
    }

    public function getXml($uri, array $options = [])
    {
        return new SimpleXMLElement(
            (string) $this->client->get($uri, $options)->getBody()
        );
    }

    public function postJson($uri, array $options = [])
    {
        return json_decode(utf8_encode(
            (string) $this->client->post($uri, $options)->getBody()
        ));
    }

    public function getJson($uri, array $options = [])
    {
        return json_decode(utf8_encode(
            (string) $this->client->get($uri, $options)->getBody()
        ));
    }

    public function getSession()
    {
        return $this->session;
    }

    public function importJavascript()
    {
        return str_replace('##SESSION##', $this->getSession(), file_get_contents(__DIR__.'/js/PagSeguro.js'));
    }

    public function getPaymentMethods($sessionId, $amount)
    {
        return $this->getJson('payment-methods/?sessionId='.$sessionId.'&amount='.$amount, ['headers' => [
            'Accept' => 'application/vnd.pagseguro.com.br.v1+json;charset=ISO-8859-1',
        ]]);
    }

    public function getCreditCardBrand($sessionId, $creditCard)
    {
        return $this->getJson('https://df.uol.com.br/df-fe/mvc/creditcard/v1/getBin?tk='.$sessionId.'&creditCard='.substr($creditCard, 0, 6), ['headers' => [
            'Accept' => 'application/vnd.pagseguro.com.br.v1+json;charset=ISO-8859-1',
        ]]);
    }

    public function getCreditCardToken($sessionId, $amount, $cardNumber, $cardBrand, $cardCvv, $cardExpirationMonth, $cardExpirationYear)
    {
        return $this->postJson('https://df.uol.com.br/v2/cards/?email='.$this->email.'&token='.$this->token, [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'form_params' => [
                'sessionId' => $sessionId,
                'amount' => $amount,
                'cardNumber' => $cardNumber,
                'cardBrand' => $cardBrand,
                'cardCvv' => $cardCvv,
                'cardExpirationMonth' => $cardExpirationMonth,
                'cardExpirationYear' => $cardExpirationYear,
            ]
        ]);
    }

    public function getInstallments($sessionId, $amount, $creditCardBrand, $maxInstallmentNoInterest)
    {
        return $this->getJson('https://pagseguro.uol.com.br/checkout/v2/installments.json?sessionId='.$sessionId.'&amount='.$amount.'&creditCardBrand='.$creditCardBrand.'&maxInstallmentNoInterest='.$maxInstallmentNoInterest, ['headers' => [
            'Accept' => 'application/json',
        ]]);
    }
}
