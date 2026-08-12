<?php

namespace Tests\Support;

class HttpResponse
{
    public $status;
    public $body;
    public $urlFinal;

    public function __construct($status, $body, $urlFinal)
    {
        $this->status = $status;
        $this->body = $body;
        $this->urlFinal = $urlFinal;
    }

    public function contem($texto)
    {
        return mb_strpos($this->body, $texto) !== false;
    }

    /** Caminho (sem domínio) para onde a requisição terminou, após seguir redirects. */
    public function caminhoFinal()
    {
        return parse_url($this->urlFinal, PHP_URL_PATH) . (parse_url($this->urlFinal, PHP_URL_QUERY) ? '?' . parse_url($this->urlFinal, PHP_URL_QUERY) : '');
    }

    public function temErroPhp()
    {
        return mb_strpos($this->body, 'A PHP Error was encountered') !== false
            || mb_strpos($this->body, 'Fatal error') !== false;
    }
}
