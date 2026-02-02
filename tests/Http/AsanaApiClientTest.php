<?php

namespace BrightleafDigital\Tests\Http;

use BrightleafDigital\Exceptions\AsanaApiException;
use BrightleafDigital\Exceptions\RateLimitException;
use BrightleafDigital\Http\AsanaApiClient;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\Exception as MockException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
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

    /**
     * Test default retry constants are defined correctly.
     */
    public function testRetryConstants(): void
    {
        $this->assertSame(3, AsanaApiClient::DEFAULT_MAX_RETRIES);
        $this->assertSame(1, AsanaApiClient::DEFAULT_INITIAL_BACKOFF);
    }

    /**
     * Test constructor with custom logger.
     * @throws MockException
     */
    public function testConstructorWithCustomLogger(): void
    {
        $mockLogger = $this->createMock(LoggerInterface::class);
        $client = new AsanaApiClient('test-token', $mockLogger);

        $this->assertSame($mockLogger, $client->getLogger());
    }

    /**
     * Test constructor uses NullLogger by default.
     */
    public function testConstructorUsesNullLoggerByDefault(): void
    {
        $client = new AsanaApiClient('test-token');

        $this->assertInstanceOf(NullLogger::class, $client->getLogger());
    }

    /**
     * Test constructor with custom retry settings.
     */
    public function testConstructorWithCustomRetrySettings(): void
    {
        $client = new AsanaApiClient('test-token', null, 5, 2);

        $reflection = new ReflectionClass(AsanaApiClient::class);

        $maxRetriesProperty = $reflection->getProperty('maxRetries');
        $maxRetriesProperty->setAccessible(true);
        $this->assertSame(5, $maxRetriesProperty->getValue($client));

        $initialBackoffProperty = $reflection->getProperty('initialBackoff');
        $initialBackoffProperty->setAccessible(true);
        $this->assertSame(2, $initialBackoffProperty->getValue($client));
    }

    /**
     * Test setLogger changes the logger.
     * @throws MockException
     */
    public function testSetLogger(): void
    {
        $client = new AsanaApiClient('test-token');
        $mockLogger = $this->createMock(LoggerInterface::class);

        $result = $client->setLogger($mockLogger);

        $this->assertSame($client, $result); // Fluent interface
        $this->assertSame($mockLogger, $client->getLogger());
    }

    /**
     * Test rate limit exception is thrown after max retries.
     * @throws MockException
     */
    public function testRateLimitExceptionAfterMaxRetries(): void
    {
        // Create client with 0 retries to immediately throw exception
        $apiClient = new AsanaApiClient('test-token', null, 0);

        $mockHttpClient = $this->createMock(GuzzleClient::class);

        // Replace the internal httpClient with our mock
        $reflection = new ReflectionClass(AsanaApiClient::class);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($apiClient, $mockHttpClient);

        // Create a rate limit response
        $mockStream = $this->createMock(StreamInterface::class);
        $mockStream->method('__toString')->willReturn(json_encode([
            'errors' => [['message' => 'Rate limit exceeded']]
        ]));

        $mockResponse = $this->createMock(Response::class);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $mockResponse->method('getStatusCode')->willReturn(429);
        $mockResponse->method('getReasonPhrase')->willReturn('Too Many Requests');
        $mockResponse->method('hasHeader')->with('Retry-After')->willReturn(true);
        $mockResponse->method('getHeaderLine')->with('Retry-After')->willReturn('30');

        $mockRequest = $this->createMock(Request::class);

        $exception = new RequestException(
            'Rate limit exceeded',
            $mockRequest,
            $mockResponse
        );

        $mockHttpClient->expects($this->once())
            ->method('request')
            ->willThrowException($exception);

        $this->expectException(RateLimitException::class);
        $this->expectExceptionMessage('Rate limit exceeded. Please retry after');

        $apiClient->request('GET', 'tasks');
    }

    /**
     * Test that rate limit exception contains retry after value.
     * @throws MockException
     */
    public function testRateLimitExceptionContainsRetryAfter(): void
    {
        // Create client with 0 retries to immediately throw exception
        $apiClient = new AsanaApiClient('test-token', null, 0);

        $mockHttpClient = $this->createMock(GuzzleClient::class);

        // Replace the internal httpClient with our mock
        $reflection = new ReflectionClass(AsanaApiClient::class);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($apiClient, $mockHttpClient);

        // Create a rate limit response
        $mockStream = $this->createMock(StreamInterface::class);
        $mockStream->method('__toString')->willReturn('{}');

        $mockResponse = $this->createMock(Response::class);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $mockResponse->method('getStatusCode')->willReturn(429);
        $mockResponse->method('getReasonPhrase')->willReturn('Too Many Requests');
        $mockResponse->method('hasHeader')->with('Retry-After')->willReturn(true);
        $mockResponse->method('getHeaderLine')->with('Retry-After')->willReturn('45');

        $mockRequest = $this->createMock(Request::class);

        $exception = new RequestException(
            'Rate limit exceeded',
            $mockRequest,
            $mockResponse
        );

        $mockHttpClient->expects($this->once())
            ->method('request')
            ->willThrowException($exception);

        try {
            $apiClient->request('GET', 'tasks');
            $this->fail('Expected RateLimitException was not thrown');
        } catch (RateLimitException $e) {
            $this->assertSame(45, $e->getRetryAfter());
            $this->assertSame(429, $e->getCode());
        }
    }

    /**
     * Test logger receives debug calls during successful request.
     * @throws MockException
     */
    public function testLoggerReceivesDebugCalls(): void
    {
        $mockLogger = $this->createMock(LoggerInterface::class);
        $mockLogger->expects($this->exactly(2))
            ->method('debug');

        $apiClient = new AsanaApiClient('test-token', $mockLogger);

        $mockHttpClient = $this->createMock(GuzzleClient::class);

        // Replace the internal httpClient with our mock
        $reflection = new ReflectionClass(AsanaApiClient::class);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($apiClient, $mockHttpClient);

        $responseBody = ['data' => ['gid' => '12345']];

        $mockStream = $this->createMock(StreamInterface::class);
        $mockStream->method('__toString')->willReturn(json_encode($responseBody));

        $mockResponse = $this->createMock(Response::class);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $mockResponse->method('getStatusCode')->willReturn(200);

        $mockHttpClient->expects($this->once())
            ->method('request')
            ->willReturn($mockResponse);

        $apiClient->request('GET', 'tasks/12345');
    }

    /**
     * Test logger receives error call on API failure.
     * @throws MockException
     */
    public function testLoggerReceivesErrorOnFailure(): void
    {
        $mockLogger = $this->createMock(LoggerInterface::class);
        $mockLogger->expects($this->once())
            ->method('debug');
        $mockLogger->expects($this->once())
            ->method('error');

        $apiClient = new AsanaApiClient('test-token', $mockLogger);

        $mockHttpClient = $this->createMock(GuzzleClient::class);

        // Replace the internal httpClient with our mock
        $reflection = new ReflectionClass(AsanaApiClient::class);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($apiClient, $mockHttpClient);

        $mockStream = $this->createMock(StreamInterface::class);
        $mockStream->method('__toString')->willReturn('Server Error');

        $mockResponse = $this->createMock(Response::class);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $mockResponse->method('getStatusCode')->willReturn(500);
        $mockResponse->method('getReasonPhrase')->willReturn('Internal Server Error');

        $mockRequest = $this->createMock(Request::class);

        $exception = new RequestException(
            'Server error',
            $mockRequest,
            $mockResponse
        );

        $mockHttpClient->expects($this->once())
            ->method('request')
            ->willThrowException($exception);

        $this->expectException(AsanaApiException::class);

        $apiClient->request('GET', 'tasks');
    }

    /**
     * Test RESPONSE_FULL sanitizes options in output.
     * @throws MockException
     */
    public function testResponseFullSanitizesOptions(): void
    {
        $responseBody = ['data' => ['gid' => '12345']];

        $mockStream = $this->createMock(StreamInterface::class);
        $mockStream->method('__toString')->willReturn(json_encode($responseBody));

        $mockResponse = $this->createMock(Response::class);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $mockResponse->method('getStatusCode')->willReturn(200);
        $mockResponse->method('getReasonPhrase')->willReturn('OK');
        $mockResponse->method('getHeaders')->willReturn([]);

        $this->mockHttpClient->expects($this->once())
            ->method('request')
            ->willReturn($mockResponse);

        $optionsWithAuth = [
            'headers' => ['Authorization' => 'Bearer secret-token'],
            'query' => ['limit' => 10]
        ];

        $result = $this->apiClient->request(
            'GET',
            'tasks',
            $optionsWithAuth,
            AsanaApiClient::RESPONSE_FULL
        );

        // The options in the response should have Authorization redacted
        $this->assertSame('[REDACTED]', $result['request']['options']['headers']['Authorization']);
        $this->assertSame(['limit' => 10], $result['request']['options']['query']);
    }
}
