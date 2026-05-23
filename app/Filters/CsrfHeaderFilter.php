<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class CsrfHeaderFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Do nothing before
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Hanya proses jika request adalah AJAX
        if ($request instanceof IncomingRequest) {
            /** @var IncomingRequest $incomingRequest */
            $incomingRequest = $request;
            
            if ($incomingRequest->isAJAX()) {
                $referer = $incomingRequest->header('Referer')?->getValue();
                $origin = $incomingRequest->header('Origin')?->getValue();
                
                // Normalisasi Domain/Host untuk mengantisipasi load balancer/proxy
                $currentHost = $incomingRequest->getUri()->getHost();
                $parseBase = parse_url(base_url(), PHP_URL_HOST);
                $parseReferer = $referer ? parse_url($referer, PHP_URL_HOST) : null;
                $parseOrigin = $origin ? parse_url($origin, PHP_URL_HOST) : null;
                
                $isSameOrigin = false;
                if ($parseReferer && ($parseReferer === $parseBase || $parseReferer === $currentHost)) {
                    $isSameOrigin = true;
                } elseif ($parseOrigin && ($parseOrigin === $parseBase || $parseOrigin === $currentHost)) {
                    $isSameOrigin = true;
                }
                
                if ($isSameOrigin) {
                    $response->setHeader('X-CSRF-TOKEN', csrf_hash());
                }
            }
        }
        return $response;
    }
}
