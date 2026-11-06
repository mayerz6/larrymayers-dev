<?php

declare(strict_types=1);

/**
 * A tiny wrapper around PHP's own curl extension (ext-curl), used instead of
 * shelling out to a curl.exe binary. Three reasons that matters:
 *
 *   1. Portability. ext-curl works identically on Windows and the Linux
 *      runners GitHub Actions uses -- there's no "curl.exe on Windows behaves
 *      slightly differently than curl on Linux" risk to design around.
 *   2. One language. The whole test suite stays PHP; nobody touching this
 *      later needs to also know PowerShell or bash to understand what a
 *      smoke test does.
 *   3. No subprocess per request. Shelling out to curl.exe spawns a new OS
 *      process for every single HTTP call. curl_exec() reuses one open
 *      handle (and, via CURLOPT_COOKIEJAR/COOKIEFILE, one cookie jar file)
 *      for the whole test -- faster, and there's no risk of a stray curl.exe
 *      process surviving a failed test.
 *
 * One instance = one logged-in-or-not browser session, exactly like one
 * cookie jar file in the PowerShell tutorial's SmokeLib.ps1. Create a new
 * HttpClient when you want to simulate a different visitor (e.g. "an
 * anonymous stranger" vs "the logged-in admin").
 */
final class HttpClient
{
    private readonly string $baseUrl;
    private readonly string $cookieJarPath;
    private ?array $lastResponseHeaders = null;

    public function __construct(string $baseUrl)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->cookieJarPath = tempnam(sys_get_temp_dir(), 'portfolio-smoke-cookies-');
    }

    /**
     * @return array{status:int, body:string}
     */
    public function get(string $path): array
    {
        return $this->request('GET', $path);
    }

    /**
     * @param array<string,string> $fields Sent as application/x-www-form-urlencoded,
     *                                      exactly like a plain HTML <form> submit --
     *                                      this app's forms don't use multipart.
     * @return array{status:int, body:string}
     */
    public function post(string $path, array $fields): array
    {
        return $this->request('POST', $path, $fields);
    }

    /**
     * The value of a response header from the most recently made request, or
     * null if that header wasn't present. Used to read "Location" on a 302
     * redirect without following it.
     */
    public function lastHeader(string $name): ?string
    {
        return $this->lastResponseHeaders[strtolower($name)] ?? null;
    }

    private function request(string $method, string $path, array $fields = []): array
    {
        $ch = curl_init($this->baseUrl . $path);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_FOLLOWLOCATION => false, // we want to see 302s ourselves, not silently follow them
            CURLOPT_COOKIEJAR => $this->cookieJarPath,
            CURLOPT_COOKIEFILE => $this->cookieJarPath,
            CURLOPT_HEADERFUNCTION => function ($curlHandle, $headerLine) {
                $parts = explode(':', $headerLine, 2);
                if (count($parts) === 2) {
                    $this->lastResponseHeaders[strtolower(trim($parts[0]))] = trim($parts[1]);
                }
                return strlen($headerLine);
            },
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
        }

        $this->lastResponseHeaders = [];
        $body = curl_exec($ch);

        if ($body === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new RuntimeException("HTTP request to {$path} failed: {$error}");
        }

        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return ['status' => $status, 'body' => (string) $body];
    }

    public function close(): void
    {
        if (is_file($this->cookieJarPath)) {
            unlink($this->cookieJarPath);
        }
    }
}
