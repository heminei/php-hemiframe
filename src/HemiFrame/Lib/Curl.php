<?php

namespace HemiFrame\Lib;

/**
 * @author heminei
 */
class Curl
{
    private $curl;
    private $url;
    private $cookieFile;
    private $userAgent;
    private $referer;
    private $postFields;
    private $proxy;
    private $timeout;
    private $returnTransfer;
    private $customRequest;
    private $options = [];
    private $headers = [];

    public function __construct(?string $url = null)
    {
        $this->curl = curl_init();

        if (!empty($url)) {
            $this->setUrl($url);
        }
    }

    public function __destruct()
    {
        $this->close();
    }

    /**
     * Returns a cURL handle on success, FALSE on errors.
     *
     * @return resource|false
     */
    public function getCurl()
    {
        return $this->curl;
    }

    /**
     * Set url to site.
     */
    public function setUrl(string $url): self
    {
        $this->url = $url;
        $this->setOption(CURLOPT_URL, $url);

        return $this;
    }

    /**
     *     Get site url.
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * The name of a file to save all internal cookies to when the handle is closed, e.g. after a call to curl_close.
     */
    public function setCookieFile(string $file): self
    {
        $this->cookieFile = $file;
        $this->setOption(CURLOPT_COOKIEJAR, $this->cookieFile);

        return $this;
    }

    public function getCookieFile(): string
    {
        return $this->cookieFile;
    }

    /**
     * Set user Agent.
     */
    public function setUserAgent(string $agent): self
    {
        $this->userAgent = $agent;
        $this->setOption(CURLOPT_USERAGENT, $this->userAgent);

        return $this;
    }

    /**
     * Get user Agent.
     */
    public function getUserAgent(): string
    {
        return $this->userAgent;
    }

    /**
     * The contents of the "Referer: " header to be used in a HTTP request.
     */
    public function setReferer(string $url): self
    {
        $this->referer = $url;
        $this->setOption(CURLOPT_REFERER, "Referer: $url");

        return $this;
    }

    /**
     * Get referer.
     */
    public function getReferer(): ?string
    {
        return $this->referer;
    }

    /**
     * The full data to post in a HTTP "POST" operation.
     */
    public function setPostFields(string $postFields): self
    {
        $this->postFields = $postFields;
        $this->setOption(CURLOPT_POST, 1);
        $this->setOption(CURLOPT_POSTFIELDS, $postFields);

        return $this;
    }

    public function getPostFields(): ?string
    {
        return $this->postFields;
    }

    /**
     * The HTTP proxy to tunnel requests through.
     */
    public function setProxy(string $string): self
    {
        $this->proxy = $string;
        $this->setOption(CURLOPT_PROXY, $string);

        return $this;
    }

    /**
     * Get proxy ip.
     */
    public function getProxy(): ?string
    {
        return $this->proxy;
    }

    /**
     * The maximum number of seconds to allow cURL functions to execute.
     */
    public function getTimeout(): ?int
    {
        return $this->timeout;
    }

    /**
     * The maximum number of seconds to allow cURL functions to execute.
     */
    public function setTimeout(int $timeout): self
    {
        $this->timeout = $timeout;
        $this->setOption(CURLOPT_TIMEOUT, $timeout);

        return $this;
    }

    /**
     * @return int
     */
    public function getReturnTransfer()
    {
        return $this->returnTransfer;
    }

    /**
     * TRUE to return the transfer as a string of the return value of curl_exec() instead of outputting it out directly.
     *
     * @param int $returnTransfer
     *
     * @return self
     */
    public function setReturnTransfer($returnTransfer)
    {
        $this->returnTransfer = $returnTransfer;
        $this->setOption(CURLOPT_RETURNTRANSFER, $this->returnTransfer);

        return $this;
    }

    public function getCustomRequest(): string
    {
        return $this->customRequest;
    }

    public function setCustomRequest(string $customRequest): self
    {
        $this->customRequest = $customRequest;
        $this->setOption(CURLOPT_CUSTOMREQUEST, $customRequest);

        return $this;
    }

    /**
     * Return content.
     */
    public function getContent(): ?string
    {
        $this->setReturnTransfer(1);
        $content = $this->execute();

        if (false === $content) {
            $content = null;
        }

        return $content;
    }

    /**
     * Execute the given cURL session.
     * This function should be called after initializing a cURL session and all the options for the session are set.
     *
     * @return string|bool
     */
    public function execute()
    {
        return curl_exec($this->curl);
    }

    /**
     * Close a cURL session.
     *
     * @return $this
     */
    public function close()
    {
        curl_close($this->curl);

        return $this;
    }

    /**
     * Sets an option on the given cURL session handle. (curl_setopt()).
     *
     * @return $this
     */
    public function setOption(int $option, $value): self
    {
        $this->options[$option] = $value;
        curl_setopt($this->curl, $option, $value);

        return $this;
    }

    /**
     * @return mixed|null
     */
    public function getOption(int $option)
    {
        if (array_key_exists($option, $this->options)) {
            return $this->options[$option];
        }

        return null;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getHeader(string $name): ?string
    {
        if (array_key_exists($name, $this->headers)) {
            return $this->headers[$name];
        }

        return null;
    }

    public function setHeader(string $name, string $value)
    {
        $this->headers[$name] = $value;

        $headerLines = [];
        foreach ($this->headers as $key => $value) {
            $headerLines[] = $key.': '.$value;
        }
        $this->setOption(CURLOPT_HTTPHEADER, $headerLines);

        return $this;
    }

    /**
     *   This may be one of the following constants:
     *   CURLINFO_EFFECTIVE_URL - Last effective URL
     *   CURLINFO_HTTP_CODE - Last received HTTP code
     *   CURLINFO_FILETIME - Remote time of the retrieved document, if -1 is returned the time of the document is unknown
     *   CURLINFO_TOTAL_TIME - Total transaction time in seconds for last transfer
     *   CURLINFO_NAMELOOKUP_TIME - Time in seconds until name resolving was complete
     *   CURLINFO_CONNECT_TIME - Time in seconds it took to establish the connection
     *   CURLINFO_PRETRANSFER_TIME - Time in seconds from start until just before file transfer begins
     *   CURLINFO_STARTTRANSFER_TIME - Time in seconds until the first byte is about to be transferred
     *   CURLINFO_REDIRECT_COUNT - Number of redirects, with the CURLOPT_FOLLOWLOCATION option enabled
     *   CURLINFO_REDIRECT_TIME - Time in seconds of all redirection steps before final transaction was started, with the CURLOPT_FOLLOWLOCATION option enabled
     *   CURLINFO_REDIRECT_URL - URL of final transaction, with the CURLOPT_FOLLOWLOCATION option enabled
     *   CURLINFO_PRIMARY_IP - IP address of the most recent connection
     *   CURLINFO_PRIMARY_PORT - Destination port of the most recent connection
     *   CURLINFO_LOCAL_IP - Local (source) IP address of the most recent connection
     *   CURLINFO_LOCAL_PORT - Local (source) port of the most recent connection
     *   CURLINFO_SIZE_UPLOAD - Total number of bytes uploaded
     *   CURLINFO_SIZE_DOWNLOAD - Total number of bytes downloaded
     *   CURLINFO_SPEED_DOWNLOAD - Average download speed
     *   CURLINFO_SPEED_UPLOAD - Average upload speed
     *   CURLINFO_HEADER_SIZE - Total size of all headers received
     *   CURLINFO_HEADER_OUT - The request string sent. For this to work, add the CURLINFO_HEADER_OUT option to the handle by calling curl_setopt()
     *   CURLINFO_REQUEST_SIZE - Total size of issued requests, currently only for HTTP requests
     *   CURLINFO_SSL_VERIFYRESULT - Result of SSL certification verification requested by setting CURLOPT_SSL_VERIFYPEER
     *   CURLINFO_CONTENT_LENGTH_DOWNLOAD - content-length of download, read from Content-Length: field
     *   CURLINFO_CONTENT_LENGTH_UPLOAD - Specified size of upload
     *   CURLINFO_CONTENT_TYPE - Content-Type: of the requested document, NULL indicates server did not send valid Content-Type: header
     */
    public function getInfo(int $opt = 0)
    {
        return curl_getinfo($this->curl, $opt);
    }

    public function getHttpCode(): int
    {
        return $this->getInfo(CURLINFO_HTTP_CODE);
    }

    public function getTotalTime(): float
    {
        return $this->getInfo(CURLINFO_TOTAL_TIME);
    }

    /**
     * Return a string containing the last error for the current session - curl_error().
     */
    public function getError(): string
    {
        return curl_error($this->curl);
    }
}
