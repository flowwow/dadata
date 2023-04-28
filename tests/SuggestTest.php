<?php

namespace Dadata\Tests;

use Dadata\Client;
use Dadata\Response\Address;
use Dadata\Response\Suggestions\Party\AddressDto;
use Dadata\Response\Suggestions\Party\ManagementDto;
use Dadata\Response\Suggestions\Party\NameDto;
use Dadata\Response\Suggestions\Party\OpfDto;
use Dadata\Response\Suggestions\Party\Party;
use Dadata\Response\Suggestions\Party\StateDto;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Utils;

/**
 * @covers \Dadata\Client
 */
final class SuggestTest extends \PHPUnit\Framework\TestCase
{
    /** @var Client  */
    private $api;
    /** @var MockHandler  */
    private $mock;

    protected function setUp(): void
    {
        $this->mock = new MockHandler();
        $handler = HandlerStack::create($this->mock);
        $guzzle = new GuzzleClient(["handler" => $handler]);
        $this->api = new Client($guzzle, ['token' => 'token']);
    }

    public function testBadResponse()
    {
        $this->expectException(\JsonException::class);
        $response = '{ bar: "baz" }';
        $this->mockResponse($response);
        $this->api->suggestParties("9702020445");
    }

    /**
     * @return void
     * @dataProvider orgs
     */
    public function testSuggestParties($file, $inn, Party $expected)
    {
        $rawMockResponse = file_get_contents($file);
        $this->mockResponse($rawMockResponse);
        $response = $this->api->suggestParties($inn);

        /** @var Party $party */
        $party = $response->current();
        $this->assertEquals($expected->getValue(), $party->getValue(), 'getValue');
        $this->assertEquals($expected->getUnrestrictedValue(), $party->getUnrestrictedValue(), 'getUnrestrictedValue');
        $this->assertEquals($expected->getManagement(), $party->getManagement(), 'getManagement');
        $this->assertEquals($expected->getBranchType(), $party->getBranchType());
        $this->assertEquals($expected->getType(), $party->getType(), 'getType');
        $this->assertEquals($expected->getOpf(), $party->getOpf(), 'getOpf');
        $this->assertEquals($expected->getName(), $party->getName(), 'getName');
        $this->assertEquals($expected->getInn(),   $party->getInn(), 'getInn');
        $this->assertEquals($expected->getOgrn(), $party->getOgrn(), 'getOgrn');
        $this->assertEquals($expected->getOkved(), $party->getOkved(), 'getOkved');
        $this->assertEquals($expected->getKpp(), $party->getKpp(), 'getKpp');
        $this->assertEquals($expected->getType(), $party->getType(), 'getType');
        $this->assertEquals($expected->getSimpleAddress(), $party->getSimpleAddress(), 'getSimpleAddress');
        $this->assertEquals($expected->getState(), $party->getState(), 'getState');

        $this->assertEquals($expected->getAddress() !== null, $party->getAddress() !== null, 'address is not null');
        if ($expected->getAddress() !== null && $party->getAddress() !== null) {
            $this->assertEquals($expected->getAddress()->okato, $party->getAddress()->okato, 'okato');
            $this->assertEquals($expected->getAddress()->street_with_type, $party->getAddress()->street_with_type, 'street_with_type');
        }
    }

    public function orgs(): iterable
    {
        yield 'ООО' => [
            __DIR__.'/data/ooo.json',
            '9702020445',
            $this->orgOOO()
        ];

        yield 'ИП' => [
            __DIR__.'/data/individual.json',
            '525716891723',
            $this->orgIP()
        ];
    }

    private function orgOOO(): Party
    {
        $expectedAddress = new Address();
        $expectedAddress->okato = '45286570000';
        $expectedAddress->street_with_type = 'пр-кт Мира';
        return  new Party(
            "ООО \"ФЛАУВАУ\"",
            "ООО \"ФЛАУВАУ\"",
            '770201001',
            new ManagementDto(
                'Анонимов Аноним Анонимович',
                'ГЕНЕРАЛЬНЫЙ ДИРЕКТОР'
            ),
            'MAIN',
            'LEGAL',
            new OpfDto('2014', '12300', 'Общество с ограниченной ответственностью', 'ООО'),
            new NameDto(
                'ОБЩЕСТВО С ОГРАНИЧЕННОЙ ОТВЕТСТВЕННОСТЬЮ "ФЛАУВАУ"',
                'ООО "ФЛАУВАУ"',
                null,
                'ФЛАУВАУ',
                'ФЛАУВАУ'
            ),
            '9702020445',
            '1207700263198',
            '44997193',
            '63.11',
            new StateDto(
                'ACTIVE',
                1654646400000,
                1595980800000,
                null,
            ),
            new AddressDto(
                'г Москва, пр-кт Мира, д 3 стр 3, помещ 1',
                '129090, г Москва, Мещанский р-н, пр-кт Мира, д 3 стр 3, помещ 1',
            ),
            $expectedAddress
        );
    }

    private function orgIP()
    {
        $expectedAddress = new Address();
        return  new Party(
            "ИП Анонимов Аноним Анонимович",
            "ИП Анонимов Аноним Анонимович",
            null,
            null,
            null,
            'INDIVIDUAL',
            new OpfDto('2014', '50102', 'Индивидуальный предприниматель', 'ИП'),
            new NameDto(
                'Индивидуальный предприниматель Анонимов Аноним Анонимович',
                'ИП Анонимов Аноним Анонимович',
                null,
                'Анонимов Аноним Анонимович',
                null
            ),
            '525716891723',
            '323527500051384',
            '44997193',
            '62.01',
            new StateDto(
                'ACTIVE',
                1682553600000,
                1682380800000,
                null,
            ),
            new AddressDto(
                null,
                null,
            ),
            null
        );
    }

    protected function mockResponse($data)
    {
        $body = Utils::streamFor($data);
        $response = new Response(200, [], $body);
        $this->mock->append($response);
    }
}
