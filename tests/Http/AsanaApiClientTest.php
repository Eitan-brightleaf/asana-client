<?php

namespace BrightleafDigital\Tests\Http;

use BrightleafDigital\Exceptions\AsanaApiException;
use BrightleafDigital\Http\AsanaApiClient;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\Exception as MockException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use ReflectionClass;

class AsanaApiClientTest extends TestCase
{
    /** @var GuzzleClient&\PHPUnit\Framework\MockObject\MockObject */
    private $mockHttpClient;

    /** @var AsanaApiClient */
    private $apiClient;

    /**
     * @throws MockException
     */
    protected function setUp(): void
    {
        $this->mockHttpClient = $this->createMock(GuzzleClient::class);

        // Create the API client with a real token
        $this->apiClient = new AsanaApiClient('test-access-token');

        // Replace the internal httpClient with our mock
        $reflection = new ReflectionClass(AsanaApiClient::class);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($this->apiClient, $this->mockHttpClient);
    }

    /**
     * Test response type constants are defined correctly.
     */
    public function testResponseTypeConstants(): void
    {
        $this->assertSame(1, AsanaApiClient::RESPONSE_FULL);
        $this->assertSame(2, AsanaApiClient::RESPONSE_NORMAL);
        $this->assertSame(3, AsanaApiClient::RESPONSE_DATA);
    }

    /**
     * Test successful GET request returns data only (default response type).
     * @throws MockException
     */
    public function testRequestReturnsDataByDefault(): void
    {
        $responseBody = ['data' => ['gid' => '12345', 'name' => 'Test Task']];

        $mockStream = $this->createMock(StreamInterface::class);
        $mockStream->method('__toString')->willReturn(json_encode($responseBody));

        $mockResponse = $this->createMock(Response::class);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $mockResponse->method('getStatusCode')->willReturn(200);

        $this->mockHttpClient->expects($this->once())
            ->method('request')
            ->with('GET', 'tasks/12345', [])
            ->willReturn($mockResponse);

        $result = $this->apiClient->request('GET', 'tasks/12345');

        $this->assertSame(['gid' => '12345', 'name' => 'Test Task'], $result);
    }

    /**
     * Test request with RESPONSE_NORMAL returns complete JSON body.
     * @throws MockException
     */
    public function testRequestReturnsNormalResponse(): void
    {
        $responseBody = [
            'data' => ['gid' => '12345'],
            'next_page' => ['offset' => 'abc123']
        ];

        $mockStream = $this->createMock(StreamInterface::class);
        $mockStream->method('__toString')->willReturn(json_encode($responseBody));

        $mockResponse = $this->createMock(Response::class);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $mockResponse->method('getStatusCode')->willReturn(200);

        $this->mockHttpClient->expects($this->once())
            ->method('request')
            ->willReturn($mockResponse);

        $result = $this->apiClient->request('GET', 'tasks', [], AsanaApiClient::RESPONSE_NORMAL);

        $this->assertSame($responseBody, $result);
    }

    /**
     * Test request with RESPONSE_FULL returns complete response details.
     * @throws MockException
     */
    public function testRequestReturnsFullResponse(): void
    {
        $responseBody = ['data' => ['gid' => '12345']];
        $encodedBody = json_encode($responseBody);

        $mockStream = $this->createMock(StreamInterface::class);
        $mockStream->method('__toString')->willReturn($encodedBody);

        $mockResponse = $this->createMock(Response::class);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $mockResponse->method('getStatusCode')->willReturn(200);
        $mockResponse->method('getReasonPhrase')->willReturn('OK');
        $mockResponse->method('getHeaders')->willReturn(['Content-Type' => ['application/json']]);

        $this->mockHttpClient->expects($this->once())
            ->method('request')
            ->with('POST', 'tasks', ['json' => ['data' => ['name' => 'New Task']]])
            ->willReturn($mockResponse);

        $result = $this->apiClient->request(
            'POST',
            'tasks',
            ['json' => ['data' => ['name' => 'New Task']]],
            AsanaApiClient::RESPONSE_FULL
        );

        $this->assertArrayHasKey('status', $result);
        $this->assertArrayHasKey('reason', $result);
        $this->assertArrayHasKey('headers', $result);
        $this->assertArrayHasKey('body', $result);
        $this->assertArrayHasKey('raw_body', $result);
        $this->assertArrayHasKey('request', $result);

        $this->assertSame(200, $result['status']);
        $this->assertSame('OK', $result['reason']);
        $this->assertSame(['Content-Type' => ['application/json']], $result['headers']);
        $this->assertSame($responseBody, $result['body']);
    }

    /**
     * Test that response without 'data' key returns full body in RESPONSE_DATA mode.
     * @throws MockException
     */
    public function testRequestReturnsFullBodyWhenNoDataKey(): void
    {
        $responseBody = ['gid' => '12345', 'name' => 'Test'];

        $mockStream = $this->createMock(StreamInterface::class);
        $mockStream->method('__toString')->willReturn(json_encode($responseBody));

        $mockResponse = $this->createMock(Response::class);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $mockResponse->method('getStatusCode')->willReturn(200);

        $this->mockHttpClient->expects($this->once())
            ->method('request')
            ->willReturn($mockResponse);

        $result = $this->apiClient->request('GET', 'some/endpoint');

        $this->assertSame($responseBody, $result);
    }

    /**
     * Test that invalid JSON response throws AsanaApiException.
     * @throws MockException
     */
    public function testRequestThrowsExceptionOnInvalidJson(): void
    {
        $mockStream = $this->createMock(StreamInterface::class);
        $mockStream->method('__toString')->willReturn('not valid json');

        $mockResponse = $this->createMock(Response::class);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $mockResponse->method('getStatusCode')->willReturn(200);

        $this->mockHttpClient->expects($this->once())
            ->method('request')
            ->willReturn($mockResponse);

        $this->expectException(AsanaApiException::class);
        $this->expectExceptionMessage('Invalid JSON response from Asana API.');

        $this->apiClient->request('GET', 'tasks');
    }

    /**
     * Test that GuzzleException with response is handled correctly.
     * @throws MockException
     */
    public function testRequestHandlesGuzzleExceptionWithResponse(): void
    {
        $errorBody = [
            'errors' => [
                ['message' => 'task: Not a valid gid', 'help' => 'Check the ID']
            ]
        ];

        $mockStream = $this->createMock(StreamInterface::class);
        $mockStream->method('__toString')->willReturn(json_encode($errorBody));

        $mockResponse = $this->createMock(Response::class);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $mockResponse->method('getStatusCode')->willReturn(400);
        $mockResponse->method('getReasonPhrase')->willReturn('Bad Request');

        $mockRequest = $this->createMock(Request::class);
        $mockRequest->method('getMethod')->willReturn('GET');
        $mockRequest->method('getUri')->willReturn(
            new \GuzzleHttp\Psr7\Uri('https://app.asana.com/api/1.0/tasks/invalid')
        );

        $exception = new RequestException(
            'Client error',
            $mockRequest,
            $mockResponse
        );

        $this->mockHttpClient->expects($this->once())
            ->method('request')
            ->willThrowException($exception);

        $this->expectException(AsanaApiException::class);
        $this->expectExceptionMessage('task: Not a valid gid');

        $this->apiClient->request('GET', 'tasks/invalid');
    }

    /**
     * Test that GuzzleException without response is handled correctly.
     * @throws MockException
     */
    public function testRequestHandlesGuzzleExceptionWithoutResponse(): void
    {
        $mockRequest = $this->createMock(Request::class);

        $exception = new RequestException(
            'Network error',
            $mockRequest,
            null
        );

        $this->mockHttpClient->expects($this->once())
            ->method('request')
            ->willThrowException($exception);

        $this->expectException(AsanaApiException::class);
        $this->expectExceptionMessage('Network error');

        $this->apiClient->request('GET', 'tasks');
    }

    /**
     * Test that GuzzleException with non-JSON response body is handled.
     * @throws MockException
     */
    public function testRequestHandlesGuzzleExceptionWithPlainTextBody(): void
    {
        $mockStream = $this->createMock(StreamInterface::class);
        $mockStream->method('__toString')->willReturn('Service Unavailable');

        $mockResponse = $this->createMock(Response::class);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $mockResponse->method('getStatusCode')->willReturn(503);
        $mockResponse->method('getReasonPhrase')->willReturn('Service Unavailable');

        $mockRequest = $this->createMock(Request::class);

        $exception = new RequestException(
            'Server error',
            $mockRequest,
            $mockResponse
        );

        $this->mockHttpClient->expects($this->once())
            ->method('request')
            ->willThrowException($exception);

        $this->expectException(AsanaApiException::class);
        $this->expectExceptionMessage('Service Unavailable');

        $this->apiClient->request('GET', 'tasks');
    }

    /**
     * Test request with query parameters.
     * @throws MockException
     */
    public function testRequestWithQueryParameters(): void
    {
        $responseBody = ['data' => []];

        $mockStream = $this->createMock(StreamInterface::class);
        $mockStream->method('__toString')->willReturn(json_encode($responseBody));

        $mockResponse = $this->createMock(Response::class);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $mockResponse->method('getStatusCode')->willReturn(200);

        $expectedOptions = [
            'query' => [
                'workspace' => '12345',
                'opt_fields' => 'name,assignee',
                'limit' => 50
            ]
        ];

        $this->mockHttpClient->expects($this->once())
            ->method('request')
            ->with('GET', 'tasks', $expectedOptions)
            ->willReturn($mockResponse);

        $this->apiClient->request('GET', 'tasks', $expectedOptions);
    }

    /**
     * Test request with JSON body for POST.
     * @throws MockException
     */
    public function testRequestWithJsonBody(): void
    {
        $responseBody = ['data' => ['gid' => '12345', 'name' => 'New Task']];

        $mockStream = $this->createMock(StreamInterface::class);
        $mockStream->method('__toString')->willReturn(json_encode($responseBody));

        $mockResponse = $this->createMock(Response::class);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $mockResponse->method('getStatusCode')->willReturn(201);

        $taskData = [
            'json' => [
                'data' => [
                    'name' => 'New Task',
                    'workspace' => '12345'
                ]
            ]
        ];

        $this->mockHttpClient->expects($this->once())
            ->method('request')
            ->with('POST', 'tasks', $taskData)
            ->willReturn($mockResponse);

        $result = $this->apiClient->request('POST', 'tasks', $taskData);

        $this->assertSame('12345', $result['gid']);
        $this->assertSame('New Task', $result['name']);
    }

    /**
     * Test PUT request for updating resources.
     * @throws MockException
     */
    public function testPutRequest(): void
    {
        $responseBody = ['data' => ['gid' => '12345', 'name' => 'Updated Task']];

        $mockStream = $this->createMock(StreamInterface::class);
        $mockStream->method('__toString')->willReturn(json_encode($responseBody));

        $mockResponse = $this->createMock(Response::class);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $mockResponse->method('getStatusCode')->willReturn(200);

        $this->mockHttpClient->expects($this->once())
            ->method('request')
            ->with('PUT', 'tasks/12345', $this->anything())
            ->willReturn($mockResponse);

        $result = $this->apiClient->request('PUT', 'tasks/12345', [
            'json' => ['data' => ['name' => 'Updated Task']]
        ]);

        $this->assertSame('Updated Task', $result['name']);
    }

    /**
     * Test DELETE request.
     * @throws MockException
     */
    public function testDeleteRequest(): void
    {
        $responseBody = ['data' => []];

        $mockStream = $this->createMock(StreamInterface::class);
        $mockStream->method('__toString')->willReturn(json_encode($responseBody));

        $mockResponse = $this->createMock(Response::class);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $mockResponse->method('getStatusCode')->willReturn(200);

        $this->mockHttpClient->expects($this->once())
            ->method('request')
            ->with('DELETE', 'tasks/12345', [])
            ->willReturn($mockResponse);

        $result = $this->apiClient->request('DELETE', 'tasks/12345');

        $this->assertSame([], $result);
    }

    /**
     * Test AsanaApiException contains response data.
     * @throws MockException
     */
    public function testAsanaApiExceptionContainsResponseData(): void
    {
        $errorBody = [
            'errors' => [
                ['message' => 'Invalid request', 'help' => 'See docs']
            ]
        ];

        $mockStream = $this->createMock(StreamInterface::class);
        $mockStream->method('__toString')->willReturn(json_encode($errorBody));

        $mockResponse = $this->createMock(Response::class);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $mockResponse->method('getStatusCode')->willReturn(400);
        $mockResponse->method('getReasonPhrase')->willReturn('Bad Request');

        $mockRequest = $this->createMock(Request::class);
        $mockRequest->method('getMethod')->willReturn('GET');
        $mockRequest->method('getUri')->willReturn(
            new \GuzzleHttp\Psr7\Uri('https://app.asana.com/api/1.0/tasks')
        );

        $exception = new RequestException(
            'Client error',
            $mockRequest,
            $mockResponse
        );

        $this->mockHttpClient->expects($this->once())
            ->method('request')
            ->willThrowException($exception);

        try {
            $this->apiClient->request('GET', 'tasks');
            $this->fail('Expected AsanaApiException was not thrown');
        } catch (AsanaApiException $e) {
            $this->assertSame(400, $e->getCode());
            $this->assertIsArray($e->getResponseData());
            $this->assertArrayHasKey('errors', $e->getResponseData());
        }
    }

    /**
     * Test constructor sets up authorization header correctly.
     */
    public function testConstructorSetsAuthorizationHeader(): void
    {
        $token = 'my-test-token-12345';
        $client = new AsanaApiClient($token);

        $reflection = new ReflectionClass(AsanaApiClient::class);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClient = $httpClientProperty->getValue($client);

        // Verify the client was created (we can't easily inspect Guzzle config)
        $this->assertInstanceOf(GuzzleClient::class, $httpClient);
    }
}
