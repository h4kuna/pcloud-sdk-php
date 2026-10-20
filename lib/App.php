<?php declare(strict_types = 1);

namespace pCloud\Sdk;

use stdClass;
use function abs;
use function curl_exec;
use function curl_getinfo;
use function curl_init;
use function curl_setopt;
use function get_object_vars;
use function http_build_query;
use function is_int;
use function is_string;
use function json_decode;
use function sprintf;
use function strpos;
use function trim;
use const CURLINFO_CONTENT_TYPE;
use const CURLOPT_RETURNTRANSFER;
use const CURLOPT_SSL_VERIFYPEER;

/**
 * Main App class
 */
final class App
{

	/**
	 * App key. In https://docs.pcloud.com/my_apps/ you will find it also as "Client ID"
	 */
	private string $appKey = '';

	/**
	 * App secret. In https://docs.pcloud.com/my_apps/ you will find it also as "Client secret"
	 */
	private string $appSecret = '';

	/**
	 * Redirect URL. It's usually set in https://docs.pcloud.com/my_apps/
	 */
	private string $redirectUri = '';

	/**
	 * The access token, you will receive one if you call getTokenFromCode()
	 */
	private string $accessToken = '';

	/**
	 * Data center location, USA by default
	 */
	private Location $location = Location::Us;

	/**
	 * cURL function execution timeout ( in seconds )
	 */
	private int $curlExecTimeout = 3600;

	/**
	 * Set App key
	 */
	public function setAppKey(string $appKey): void
	{
		$this->appKey = trim($appKey);
	}

	/**
	 * Get App key
	 */
	public function getAppKey(): string
	{
		return $this->appKey;
	}

	/**
	 * Sets app secret
	 */
	public function setAppSecret(string $appSecret): void
	{
		$this->appSecret = trim($appSecret);
	}

	/**
	 * Get app secret
	 */
	public function getAppSecret(): string
	{
		return $this->appSecret;
	}

	/**
	 * Set Redirect URL
	 */
	public function setRedirectURI(string $redirectUri): void
	{
		$this->redirectUri = trim($redirectUri);
	}

	/**
	 * Get Redirect URL
	 */
	public function getRedirectURI(): string
	{
		return $this->redirectUri;
	}

	/**
	 * Sets the Access Token
	 */
	public function setAccessToken(string $accessToken): void
	{
		$this->accessToken = trim($accessToken);
	}

	/**
	 * Returns the Access Token
	 */
	public function getAccessToken(): string
	{
		return $this->accessToken;
	}

	/**
	 * Set location ID
	 */
	public function setLocationId(string|int $locationid): void
	{
		$this->location = Location::fromId($locationid);
	}

	public function setLocation(Location $location): void
	{
		$this->location = $location;
	}

	public function getLocation(): Location
	{
		return $this->location;
	}

	/**
	 * Will build and return oAuth2 authorization URL
	 * from which you will receive auth code.
	 *
	 * @throws Exception
	 */
	public function getAuthorizeCodeUrl(): string
	{
		$this->validParams(['appKey' => $this->appKey]);

		$params = [
			'client_id' => $this->appKey,
			'response_type' => 'code',
		];

		if ($this->redirectUri !== '') {
			$params['redirect_uri'] = $this->redirectUri;
		}

		return 'https://my.pcloud.com/oauth2/authorize?' . http_build_query($params);
	}

	/**
	 * @return array{access_token: string, locationid: int}
	 *
	 * @throws Exception
	 */
	public function getTokenFromCode(
		string $code,
		Location $location,
	): array
	{
		$this->validParams(['appKey' => $this->appKey, 'appSecret' => $this->appSecret]);

		$params = [
			'client_id' => $this->appKey,
			'client_secret' => $this->appSecret,
			'code' => $code,
		];

		$host = $location->value;

		$url = $host . '/oauth2_token?' . http_build_query($params);

		$curl = curl_init($url);

		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

		$response = curl_exec($curl);

		$contentType = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);

		if (!is_string($response) || !is_string($contentType) || strpos($contentType, 'application/json') === false) {
			throw new Exception('Unexpected response from ' . $host);
		}

		$data = json_decode($response);

		if (!$data instanceof stdClass) {
			throw new Exception('Unexpected response from ' . $host);
		}

		$fields = get_object_vars($data);
		$accessToken = $fields['access_token'] ?? null;
		$locationId = $fields['locationid'] ?? null;

		if (($data->result ?? null) === 0 && is_string($accessToken) && is_int($locationId)) {
			return ['access_token' => $accessToken, 'locationid' => $locationId];
		}

		$error = $data->error ?? null;

		throw new Exception(is_string($error) ? $error : 'Unexpected response from ' . $host);
	}

	/**
	 * Sets cURL function execution timeout
	 */
	public function setCurlExecutionTimeout(int $timeout): void
	{
		$this->curlExecTimeout = abs($timeout);
	}

	/**
	 * Sets cURL function execution timeout
	 */
	public function getCurlExecutionTimeout(): int
	{
		return $this->curlExecTimeout;
	}

	/**
	 * Verifies that none of the given parameters is empty
	 *
	 * @param array<string, string> $params parameter name => value
	 *
	 * @throws Exception
	 */
	private function validParams(array $params): void
	{
		foreach ($params as $name => $value) {
			if ($value === '') {
				throw new Exception(sprintf('%s not found', $name));
			}
		}
	}

}
