<?php

declare(strict_types=1);

namespace Omnipay\AcquiroPay\Tests\Unit\Message;

use Omnipay\AcquiroPay\Message\CaptureRequest;
use Omnipay\Tests\TestCase;

class CaptureRequestTest extends TestCase
{
    /** @var CaptureRequest */
    private $request;

    protected function setUp()
    {
        parent::setUp();

        $this->request = new CaptureRequest($this->getHttpClient(), $this->getHttpRequest());
    }

    public function testGetData()
    {
        $this->request->setTransactionReference('foo');

        $data = $this->request->getData();

        $expected = array(
            'opcode'     => 13,
            'payment_id' => 'foo',
            'token'      => $this->request->getRequestToken(),
        );

        $this->assertSame($expected, $data);
    }

    public function testGetRequestToken()
    {
        $merchantId = mt_rand(1, 1000);
        $secretWord = uniqid();
        $transactionReference = uniqid();

        $this->request
            ->setMerchantId($merchantId)
            ->setSecretWord($secretWord)
            ->setTransactionReference($transactionReference);

        $token = md5($merchantId.$transactionReference.$secretWord);

        $this->assertSame($token, $this->request->getRequestToken());
    }

    public function testSendSuccess()
    {
        $this->setMockHttpResponse('CaptureSuccess.txt');

        $this->request->setTransactionReference('foo');

        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertSame('4e95257132434fbf9bc0f19eaf08cffa', $response->getTransactionReference());
        $this->assertSame('CAPTURE', $response->getStatus());
    }

    public function testSendFailure()
    {
        $this->setMockHttpResponse('CaptureFailure.txt');

        $this->request->setTransactionReference('foo');

        $response = $this->request->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertSame('4e95257132434fbf9bc0f19eaf08cffa', $response->getTransactionReference());
        $this->assertSame('DECLINE', $response->getStatus());
    }
}
