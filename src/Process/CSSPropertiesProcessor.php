<?php

declare(strict_types=1);

namespace Jimbo2150\PhpCssTokenizer\Process;

use GuzzleHttp\Client;
use Jimbo2150\PhpCssTokenizer\Exception\DontWriteException;
use Psr\Http\Message\ResponseInterface;

final class CSSPropertiesProcessor
{
	private const PROPERTIES_DIR = 'dist/CSSProperties';

	private const JSON_FILE = 'CSSProperties.json';

	private const DB_FILE = 'CSSProperties.sqlite';

	private const JSON_FILE_URL = 'https://raw.githubusercontent.com/WebKit/WebKit/main/Source/WebCore/css/CSSProperties.json';

	protected function __construct()
	{
		// Don't allow instances
	}

	public static function run()
	{
		$forceRegenerate = false;
		if (static::needToAcquireJsonFile()) {
			static::acquireJsonFile();
		}
		if ($forceRegenerate = $forceRegenerate || static::needToGenerateDatabase()) {
			static::generateDatabase($forceRegenerate);
		}
	}

	public static function getPropertiesPath(): string
	{
		static $rootPath = realpath('.');

		return $rootPath.DIRECTORY_SEPARATOR.static::PROPERTIES_DIR;
	}

	public static function getJsonFilePath(): string
	{
		return static::getPropertiesPath().DIRECTORY_SEPARATOR.static::JSON_FILE;
	}

	public static function getDbPath(): string
	{
		return static::getPropertiesPath().DIRECTORY_SEPARATOR.static::DB_FILE;
	}

	private static function needToAcquireJsonFile(): bool
	{
		$propFile = static::getJsonFilePath();
		if (!file_exists($propFile)) {
			$dir = dirname($propFile);
			if (!file_exists($dir)) {
				mkdir($dir, 0777, true);
			}
			if (!touch($propFile)) {
				throw new \Exception('Could not create CSS properties file.');
			}
			if (!touch($propFile.'.etag')) {
				throw new \Exception('Could not create CSS properties etag file.');
			}

			return true;
		}
		$propMtime = new \DateTimeImmutable('@'.filemtime($propFile));
		$now = new \DateTimeImmutable();
		if (
			$now > $propMtime->modify('+5 minutes') ||
			filesize($propFile) < 1
		) {
			return true;
		}

		return false;
	}

	private static function acquireJsonFile(): int|bool
	{
		$file = static::getJsonFilePath();
		$etag_file = $file.'.etag';
		$dir = dirname($file);

		if (!file_exists($dir)) {
			mkdir($dir, fileperms('.') ?? 0777, true);
		}
		if (!file_exists($file)) {
			touch($etag_file);
			touch($file);
		}

		$etag = trim(file_get_contents($etag_file));

		$headers = [
			'Accept' => ['text/plain'],
			'User-Agent' => ['PHP Typed OM Package'],
		];

		if (!empty($etag)) {
			$headers['If-None-Match'][] = $etag;
		}

		$client = new Client();
		echo 'Acquiring CSS Properties file... ';

		try {
			$response = $client->request('GET', static::JSON_FILE_URL, [
				'headers' => $headers,
				'sink' => $file,
				'on_headers' => function (ResponseInterface $response): void {
					if (304 == $response->getStatusCode()) {
						throw new DontWriteException('File was not modified, nothing to do: '.$response->getStatusCode());
					} elseif (
						$response->getStatusCode() >= 300 || $response->getStatusCode() < 200
					) {
						throw new \Exception('HTTP Error: '.$response->getStatusCode());
					} elseif (
						count(array_filter(
							$response->getHeader('Content-Length'),
							fn (int $length) => $length > 5
						)) < 1
					) {
						throw new DontWriteException('No content returned.');
					}
				},
			]);
		} catch (\Exception $e) {
			if (!($e->getPrevious() instanceof DontWriteException)) {
				echo $e->getMessage();
			} else {
				echo $e->getPrevious()->getMessage().PHP_EOL;
			}
		}

		if (!isset($response)) {
			return filemtime($etag_file);
		}

		if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
			file_put_contents($etag_file, $response->getHeader('ETag'));
		}

		echo 'Success.'.PHP_EOL;

		return filemtime($etag_file);
	}

	private static function needToGenerateDatabase(): bool
	{
		$dbFile = static::getDbPath();
		$propFile = static::getJsonFilePath();
		$isDevEnvironment = 'dev' === getenv('APP_ENV');
		if (!file_exists($dbFile)) {
			if (!touch($dbFile)) {
				throw new \Exception('Could not create database file.');
			}

			return true;
		}
		if (
			$isDevEnvironment ||
			filesize($dbFile) < 1 ||
			filemtime($propFile) > filemtime($dbFile)
		) {
			return true;
		}

		return false;
	}

	private static function generateDatabase(bool $regenerate = false): void
	{
		$propFile = json_decode(file_get_contents(static::getJsonFilePath()));
		$dbFile = static::getDbPath();
		echo 'Creating CSS Properties database... ';
		if ($regenerate) {
			echo 'Regenerating database... ';
			unlink($dbFile);
			touch($dbFile);
		}
		$db = new \PDO('sqlite:'.$dbFile);
		if (!$db || $db->errorCode()) {
			throw new \Exception('Could not initialized CSS properties database.');
		}

		// Create tables
		$db->exec('
			CREATE TABLE IF NOT EXISTS Property (
				name TEXT PRIMARY KEY
			)
		');

		// Create Metadata table
		$db->exec('
			CREATE TABLE IF NOT EXISTS Metadata (
				property TEXT,
				key TEXT,
				value TEXT,
				PRIMARY KEY (property, key),
				FOREIGN KEY (property) REFERENCES Property(name)
			)
		');

		$db->exec(
			'CREATE INDEX metadata_propery_key_value_idx ON '.
				'Metadata(property, key, value COLLATE NOCASE)'
		);

		$db->exec(
			'CREATE INDEX metadata_key_value_idx ON '.
				'Metadata(key, value COLLATE NOCASE)'
		);

		// Insert properties into the Property table
		$insertPropertyStmt = $db->prepare('INSERT INTO Property (name) VALUES (:name)');
		$insertMetadataStmt = $db->prepare('INSERT INTO Metadata (property, key, value) VALUES (:property, :key, :value)');
		foreach ($propFile->properties as $property => $propEntry) {
			$insertPropertyStmt->execute([':name' => $property]);
			static::insertMetadata(
				$insertMetadataStmt,
				$property,
				$propEntry
			);
		}
		echo 'Success.';
		echo PHP_EOL;
	}

	private static function insertMetadata($stmt, $property, $propEntry, $prefix = ''): void
	{
		foreach ($propEntry as $key => $value) {
			$fullKey = $prefix ? $prefix.'.'.$key : $key;
			if (is_string($value)) {
				$stmt->execute([
					':property' => $property,
					':key' => $fullKey,
					':value' => $value,
				]);
			} elseif (is_object($value)) {
				static::insertMetadata(
					$stmt,
					$property,
					$value,
					$fullKey
				);
			}
		}
	}
}
