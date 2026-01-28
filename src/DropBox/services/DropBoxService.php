<?php

use BuildCake\SqlKit\Sql;
use BuildCake\Utils\Utils;

class DropBoxService {
    private $appKey;
    private $appSecret;
    private $refreshToken;
    private $accessToken;

    public function __construct() {
        // Carrega as credenciais do Dropbox do .env
        $this->appKey = $_ENV['DROPBOX_APP_KEY'] ?? null;
        $this->appSecret = $_ENV['DROPBOX_APP_SECRET'] ?? null;
        $this->refreshToken = $_ENV['DROPBOX_REFRESH_TOKEN'] ?? null;
        $this->accessToken = $_ENV['DROPBOX_ACCESS_TOKEN'] ?? null;
    }

    /**
     * Gera um novo token de acesso temporário do Dropbox
     * @return array Retorna o token de acesso e informações relacionadas
     */
    function generateDropBoxAccessToken() {
        // Se temos refresh token, usamos ele para renovar
        if ($this->refreshToken) {
            return $this->refreshAccessToken();
        }
        
        // Se não temos refresh token mas temos access token, tentamos renovar usando ele
        if ($this->accessToken) {
            return $this->refreshAccessTokenWithCurrentToken();
        }
        
        throw new Exception("Credenciais do Dropbox não configuradas", 500);
    }

    /**
     * Renova o access token usando refresh token
     */
    private function refreshAccessToken() {
        $url = 'https://api.dropbox.com/oauth2/token';
        
        $data = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $this->refreshToken,
            'client_id' => $this->appKey,
            'client_secret' => $this->appSecret
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception("Erro ao conectar com Dropbox: " . $error, 500);
        }

        if ($httpCode !== 200) {
            throw new Exception("Erro ao renovar token do Dropbox. Código: " . $httpCode . " - Resposta: " . $response, 500);
        }

        $tokenData = json_decode($response, true);
        
        if (!isset($tokenData['access_token'])) {
            throw new Exception("Resposta inválida do Dropbox", 500);
        }

        return [
            'access_token' => $tokenData['access_token'],
            'expires_in' => $tokenData['expires_in'] ?? 14400, // 4 horas padrão
            'token_type' => $tokenData['token_type'] ?? 'bearer',
            'app_key' => $this->appKey
        ];
    }

    /**
     * Troca o código de autorização por tokens (access token e refresh token)
     * @param string $code Código de autorização recebido do Dropbox
     * @param string $redirectUri URI de redirecionamento configurada no app
     * @return array Retorna access token e refresh token
     */
    function exchangeAuthorizationCode($code, $redirectUri = null) {
        if (!$this->appKey || !$this->appSecret) {
            throw new Exception("App Key e App Secret do Dropbox não configurados", 500);
        }

        $url = 'https://api.dropbox.com/oauth2/token';
        
        $data = [
            'code' => $code,
            'grant_type' => 'authorization_code',
            'client_id' => $this->appKey,
            'client_secret' => $this->appSecret
        ];

        // Se tiver redirect URI configurado, adiciona
        if ($redirectUri) {
            $data['redirect_uri'] = $redirectUri;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception("Erro ao conectar com Dropbox: " . $error, 500);
        }

        if ($httpCode !== 200) {
            throw new Exception("Erro ao trocar código por tokens. Código: " . $httpCode . " - Resposta: " . $response, 500);
        }

        $tokenData = json_decode($response, true);
        
        if (!isset($tokenData['access_token'])) {
            throw new Exception("Resposta inválida do Dropbox", 500);
        }

        return [
            'access_token' => $tokenData['access_token'],
            'refresh_token' => $tokenData['refresh_token'] ?? null,
            'expires_in' => $tokenData['expires_in'] ?? 14400,
            'token_type' => $tokenData['token_type'] ?? 'bearer',
            'scope' => $tokenData['scope'] ?? null,
            'app_key' => $this->appKey
        ];
    }

    /**
     * Tenta renovar usando o access token atual (fallback)
     */
    private function refreshAccessTokenWithCurrentToken() {
        // Se não temos refresh token, retornamos o access token atual
        // Nota: Este é um fallback. O ideal é ter um refresh token configurado
        return [
            'access_token' => $this->accessToken,
            'expires_in' => 14400,
            'token_type' => 'bearer',
            'app_key' => $this->appKey,
            'warning' => 'Usando token atual. Configure DROPBOX_REFRESH_TOKEN para renovação automática.'
        ];
    }

    function getDropBox($filters){
        // Por padrão, retorna o token de acesso do Dropbox para upload
        // Se precisar buscar dados do banco, use o filtro 'data=true'
        if (!isset($filters['data']) || $filters['data'] !== 'true') {
            return $this->generateDropBoxAccessToken();
        }

        // Comportamento original: busca dados do banco quando data=true
        unset($filters['data']); // Remove o filtro 'data' antes de executar a query
        $retorno = Sql::runQuery("SELECT * FROM DropBox  WHERE is_active = true {filter}",$filters);

        if(!$retorno){
            Utils::sendResponse(200, [], "Nenhum(a) DropBox encontrado");
        }
        
        return $retorno;
    }

    function insertDropBox($data){
        //Sql::runPost( table name , parametros para insert na tabela);
        $retorno = Sql::runPost("DropBox",$data);
        $data['id'] = $retorno;
        return $data;
    }

    function editDropBox($data){
        //Sql::runPost( table name , parametros para insert na tabela); diferença que em $data deve conter um parametro id pu conjunto de ids para edição
        $retorno = Sql::runPut("DropBox",$data);
        return $retorno;
    }

     function deletDropBox($data){
        //Sql::runPost( table name , apenas o id ou conjunto de ids separados por virgula para remoção);  
        $retorno = Sql::runDelet("DropBox",$data);
        return $retorno;
    }
}