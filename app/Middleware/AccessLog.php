<?php


namespace App\Middleware;


use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class AccessLog implements MiddlewareInterface
{
    /**
     * @var string
     */
    private $format = '$remote_addr - $remote_user [$time_local] "$request" $status $body_bytes_sent "$http_referer" "$http_user_agent" "$http_x_forwarded_for" rt=$request_time';

    /**
     * 放到 extra 中变量，可选值 query, body, jwt, cookies, headers, header.{name}.
     *
     * @var string[]
     */
    private $extra = ['query', 'body'];

    /**
     * @var int
     */
    private $bodyMaxSize = 4096;

    /**
     * @Inject()
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * Process an incoming server request.
     *
     * Processes an incoming server request in order to produce a response.
     * If unable to produce the response itself, it may delegate to the provided
     * request handler to do so.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $start = microtime(true);
        /** @var ResponseInterface $response */
        $response = $handler->handle($request);
        $time = sprintf('%.2f', (microtime(true) - $start) * 1000);

        $ips = self::getIpList($request);
        $message = strtr($this->format, [
            '$remote_addr' => isset($ips[0]) ? $ips[0] : '-',
            '$remote_user' => $request->getUri()->getUserInfo() ?: '-',
            '$time_local' => strftime('%d/%b/%Y:%H:%M:%S %z'),
            '$request' => strtoupper($request->getMethod())
                .' '.$request->getUri()->getPath()
                .' '.strtoupper($request->getUri()->getScheme()).'/'.$request->getProtocolVersion(),
            '$status' => $response->getStatusCode(),
            '$body_bytes_sent' => $response->getBody()->getSize(),
            '$http_referer' => $request->getHeaderLine('Referer'),
            '$http_user_agent' => $request->getHeaderLine('User-Agent'),
            '$http_x_forwarded_for' => isset($ips) ? implode(',', $ips) : '',
            '$request_time' => $time,
        ]);
        $extra = [];
        foreach ($this->extra as $name) {
            if ('query' == $name) {
                $extra['query'] = http_build_query($request->getQueryParams());
            } elseif ('body' == $name) {
                $extra['body'] = isset($this->bodyMaxSize) && $request->getBody()->getSize() > $this->bodyMaxSize
                    ? 'body-too-big'
                    : (string) $request->getBody();
            } elseif ('headers' == $name) {
                $extra['headers'] = $request->getHeaders();
            } elseif ('cookies' == $name) {
                $extra['cookies'] = $request->getHeaderLine('cookie');
            } elseif ('jwt' == $name) {
                $extra['jwt'] = $this->parseJwtToken($request->getHeaderLine('Authorization'));
            } elseif (0 === strpos($name, 'header.')) {
                $header = substr($name, 7);
                $extra[$header] = $request->getHeaderLine($header);
            }
        }
        $message .= ' ' . json_encode(array_filter($extra), JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
        if ($response->getStatusCode() >= 400 && $response->getStatusCode() < 600) {
            $this->logger->error($message);
        } else {
            $this->logger->info($message);
        }

        return $response;

    }
    /**
     * @param string $format
     */
    public function setFormat($format)
    {
        $this->format = $format;
    }

    /**
     * @param string[] $extra
     */
    public function setExtra($extra)
    {
        $this->extra = $extra;
    }

    /**
     * @param int $bodyMaxSize
     */
    public function setBodyMaxSize($bodyMaxSize)
    {
        $this->bodyMaxSize = $bodyMaxSize;
    }

    public function parseJwtToken($tokenHeader)
    {
        if ($tokenHeader && 0 === strpos($tokenHeader, 'Bearer ')) {
            $parts = explode('.', substr($tokenHeader, 7));
            if (isset($parts[1])) {
                return json_decode(base64_decode($parts[1]), true);
            }
        }
    }

    public static function getIpList(ServerRequestInterface $request)
    {
        $server = $request->getServerParams();
        $ips = [];

        $name = 'X-Forwarded-For';
        $header = $request->getHeaderLine($name);

        if (!empty($header)) {
            foreach (array_map('trim', explode(',', $header)) as $ip) {
                if ((false === array_search($ip, $ips)) && filter_var($ip, FILTER_VALIDATE_IP)) {
                    $ips[] = $ip;
                }
            }
        }

        if (!empty($server['REMOTE_ADDR']) && filter_var($server['REMOTE_ADDR'], FILTER_VALIDATE_IP)) {
            $ips[] = $server['REMOTE_ADDR'];
        }

        return $ips;
    }
}