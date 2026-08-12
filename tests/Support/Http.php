<?php

namespace Tests\Support;

/**
 * Cliente HTTP mínimo (cURL puro, sem dependências) usado pelos testes de
 * integração: eles rodam FORA do container, batendo direto no app já
 * publicado pelo Docker (http://localhost:8090 por padrão) e mantendo
 * cookies entre as chamadas — assim dá pra logar uma vez e reusar a sessão,
 * igual um navegador de verdade faria.
 */
class Http
{
    private $baseUrl;
    /** @var resource|\CurlHandle handle reusado por toda a vida da instância */
    private $ch;

    public function __construct($baseUrl = null)
    {
        $this->baseUrl = rtrim($baseUrl ?: (getenv('APP_TEST_BASE_URL') ?: 'http://localhost:8090'), '/');
        $this->ch = curl_init();
        // CURLOPT_COOKIEFILE = "" liga o "cookie engine" guardando os cookies
        // EM MEMÓRIA, presos a este handle. É de propósito não usar arquivo:
        // com COOKIEJAR o cURL só grava no curl_close(), e criar um handle novo
        // por requisição criava uma corrida (a requisição seguinte às vezes lia
        // o arquivo antes do flush e ia sem o ci_session, caindo de volta no
        // login). Reusando um handle só, a sessão nunca se perde.
        curl_setopt($this->ch, CURLOPT_COOKIEFILE, '');
    }

    public function __destruct()
    {
        if ($this->ch) {
            curl_close($this->ch);
        }
    }

    public function get($path)
    {
        return $this->request('GET', $path);
    }

    public function post($path, array $dados)
    {
        return $this->request('POST', $path, $dados);
    }

    /**
     * Descarta os cookies acumulados (equivalente a abrir uma aba anônima
     * nova) — usado para testar acesso sem login, sem precisar de outro
     * processo.
     */
    public function resetSessao()
    {
        curl_setopt($this->ch, CURLOPT_COOKIELIST, 'ALL');
    }

    private function request($metodo, $path, array $dados = null, $redirectsRestantes = 5)
    {
        $url = preg_match('#^https?://#i', $path) ? $path : $this->baseUrl . '/' . ltrim($path, '/');

        $ch = $this->ch;
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            // NÃO usar CURLOPT_FOLLOWLOCATION: essa app usa redirect(..., 'refresh')
            // do CodeIgniter, que manda um header "Refresh: 0;url=..." (truque de
            // meta-refresh via header) em vez de um "Location:" de verdade — só
            // navegador entende isso, o cURL não segue automaticamente. Seguimos
            // manualmente abaixo, igual um navegador faria.
            CURLOPT_TIMEOUT => 15,
        ));

        if ($metodo === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($dados ?: array()));
        } else {
            // Handle reusado: precisa desfazer explicitamente o POST anterior,
            // senão a próxima chamada continuaria sendo POST.
            curl_setopt($ch, CURLOPT_HTTPGET, true);
        }

        $raw = curl_exec($ch);
        if ($raw === false) {
            throw new \RuntimeException("Falha na requisição {$metodo} {$url}: " . curl_error($ch));
        }

        $status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $urlFinal = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        $tamanhoHeader = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

        $headers = substr($raw, 0, $tamanhoHeader);
        $body = substr($raw, $tamanhoHeader);

        // redirect(..., 'refresh'): "Refresh: 0;url=http://..."
        if ($redirectsRestantes > 0 && preg_match('/^Refresh:\s*[\d.]+\s*;\s*url=(\S+)/im', $headers, $m)) {
            return $this->request('GET', trim($m[1]), null, $redirectsRestantes - 1);
        }

        // Location: (redirect HTTP padrão) — cobre qualquer uso futuro de
        // redirect() sem 'refresh'.
        if ($redirectsRestantes > 0 && $status >= 300 && $status < 400 && preg_match('/^Location:\s*(\S+)/im', $headers, $m)) {
            return $this->request('GET', trim($m[1]), null, $redirectsRestantes - 1);
        }

        return new HttpResponse($status, $body, $urlFinal);
    }
}
