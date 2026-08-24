<?php
/**
 * Behat tests feature context class
 *
 * @package   StudipAttendance\Tests\Behat
 * @since     0.1.0
 * @author    Farbod Zamani <zamani@elan-ev.de>
 * @copyright 2026 elan e.V.
 * @license   GPL-3.0 WITH License-Supplement (see LICENSE-SUPPLEMENT.txt)
 * @link      https://elan-ev.de
 */

namespace StudipAttendance\Tests\Behat;

use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Step\Given;
use Behat\Step\When;
use Behat\Step\Then;
use PHPUnit\Framework\Assert;

class ApiContext extends RawMinkContext
{
    private array $requestHeaders = [];
    private ?string $requestPayload = null;
    private string $lastResponseBody = '';
    private static array $savedVariables = [];

    public function __construct(private string $username, private string $password)
    {
    }

    /**
     * Saves a property value from the last JSON response into a variable name.
     * Example: And I save the JSON property "data.id" as "entry_id"
     */
    #[Then('I save the JSON property :path as :varName')]
    public function iSaveTheJsonPropertyAs(string $path, string $varName): void
    {
        $data = json_decode($this->lastResponseBody, true);

        Assert::assertNotNull(
            $data,
            sprintf("Failed to decode JSON response:\n%s", $this->lastResponseBody)
        );

        $keys = explode('.', $path);
        $current = $data;

        foreach ($keys as $key) {
            Assert::assertArrayHasKey(
                $key,
                $current,
                sprintf('Property key "%s" does not exist in path "%s".', $key, $path)
            );
            $current = $current[$key];
        }

        if (empty(self::$savedVariables[$varName])) {
            self::$savedVariables[$varName] = (string) $current;
        }
    }

    #[Given('I am logged into StudIP as :username with password :password')]
    public function iAmLoggedIntoStudipAsWithPassword(string $username, string $password): void
    {
        $session = $this->getSession();

        $session->visit($this->locatePath('/index.php?again=yes'));

        $page = $session->getPage();
        $page->fillField('loginname', $username);
        $page->fillField('password', $password);

        $page->pressButton('submit_login');

        $content = $session->getPage()->getContent();
        $stuckInLoginPage = str_contains($content, '<body id="login"');
        Assert::assertEquals(false, $stuckInLoginPage, 'Login failed! Check your credentials.');
    }

    #[Given('I am logged into StudIP')]

    public function iAmLoggedIntoStudip(): void
    {
        $session = $this->getSession();

        $session->visit($this->locatePath('/index.php'));

        $page = $session->getPage();
        $page->fillField('loginname', $this->username);
        $page->fillField('password', $this->password);

        $page->pressButton('submit_login');

        $content = $session->getPage()->getContent();
        $stuckInLoginPage = str_contains($content, '<body id="login"');
        Assert::assertEquals(false, $stuckInLoginPage, 'Login failed! Check your credentials.');
    }

    #[Given('I set header :name to :value')]
    public function iSetHeaderTo(string $name, string $value): void
    {
        $this->requestHeaders[$name] = $value;
    }

    #[Given('I set the request body to:')]
    public function iSetTheRequestBodyTo(string $payload): void
    {
        $this->requestPayload = $this->replacePlaceholders($payload);
    }

    #[When('I send a :method request to :path')]
    public function iSendARequestTo(string $method, string $path): void
    {
        $session = $this->getSession();
        $path = $this->replacePlaceholders($path);
        $fullUrl = $this->locatePath($path);

        // Required headers for StudIP JSON:API
        $headers = array_merge([
            'Accept' => 'application/vnd.api+json',
            'Content-Type' => 'application/vnd.api+json',
            'X-Requested-With' => 'XMLHttpRequest',
        ], $this->requestHeaders);

        // Apply headers to Mink session
        foreach ($headers as $name => $value) {
            $session->setRequestHeader($name, $value);
        }

        /** @var \Behat\Mink\Driver\BrowserKitDriver $driver */
        $driver = $session->getDriver();
        $client = $driver->getClient();

        if ($method === 'GET' && null === $this->requestPayload) {
            $session->visit($fullUrl);
        } else {
            $client->request(
                strtoupper($method),
                $fullUrl,
                [],
                [],
                [],
                $this->requestPayload ?? ''
            );
        }

        if (method_exists($client, 'getInternalResponse') && $client->getInternalResponse()) {
            $this->lastResponseBody = (string) $client->getInternalResponse()->getContent();
        } else {
            $this->lastResponseBody = $session->getPage()->getContent();
        }

        $this->requestHeaders = [];
        $this->requestPayload = null;
    }

    #[Then('the response status code should be :expectedCode')]
    public function theResponseStatusCodeShouldBe(int $expectedCode): void
    {
        $actualCode = $this->getSession()->getStatusCode();

        $message = sprintf('Expected status code %d, but got %d.', $expectedCode, $actualCode);

        if (!empty($this->lastResponseBody)) {
            $data = json_decode($this->lastResponseBody, true);
            if (!empty($data['errors'][0])) {
                $error = $data['errors'][0];
                $title = !empty($error['title']) ? $error['title'] : '';
                $detail = !empty($error['detail']) ? $error['detail'] : '';
                $message .= " [{$title}: {$detail}]";
            }
        }

        Assert::assertEquals(
            $expectedCode,
            $actualCode,
            $message
        );
    }

    #[Then('the JSON response property :path should equal or greater than :expectedValue')]
    public function theJsonResponsePropertyShouldEqualOrGreaterThan(string $path, string $expectedValue): void
    {
        $data = json_decode($this->lastResponseBody, true);

        Assert::assertNotNull(
            $data,
            'Failed to decode JSON response: ' . json_last_error_msg()
        );

        $keys = explode('.', $path);
        foreach ($keys as $key) {
            Assert::assertArrayHasKey(
                $key,
                $data,
                sprintf('Property key "%s" does not exist in JSON response.', $key)
            );
            $data = $data[$key];
        }

        if (is_numeric($expectedValue) ) {
            Assert::assertGreaterThanOrEqual($expectedValue, (string) $data);
        } else {
            Assert::assertEquals($expectedValue, (string) $data);
        }
    }

    /**
     * Replaces placeholders like {entry_id} in paths or request bodies.
     */
    private function replacePlaceholders(string $content): string
    {
        foreach (self::$savedVariables as $key => $val) {
            $content = str_replace('{' . $key . '}', $val, $content);
        }
        return $content;
    }
}
