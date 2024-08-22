<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GoogleSheetService
{
    protected $credentialsPath;

    public function __construct()
    {
        $this->credentialsPath = storage_path('app/credentials/final.json');
    }

    public function getAccessToken()
    {
        $credentials = json_decode(file_get_contents($this->credentialsPath), true);

        $jwtHeader = base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $jwtPayload = base64_encode(json_encode([
            'iss' => $credentials['client_email'],
            'scope' => 'https://www.googleapis.com/auth/spreadsheets https://www.googleapis.com/auth/drive.file',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => time() + 3600,
            'iat' => time()
        ]));

        $unsignedJWT = $jwtHeader . '.' . $jwtPayload;
        openssl_sign($unsignedJWT, $signature, $credentials['private_key'], 'SHA256');
        $signedJWT = $unsignedJWT . '.' . base64_encode($signature);

        $response = Http::withoutVerifying()->asForm()->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $signedJWT
        ]);

        return $response->json()['access_token'];
    }

    public function appendDataToSheet($values,$range)
    {
        $spreadsheetId = '1RzwuSQdGStoQs2qMr_iMPMFMrsiBpnK3Ct6rqdxrtbs';
        $accessToken = $this->getAccessToken();
        $url = "https://sheets.googleapis.com/v4/spreadsheets/$spreadsheetId/values/$range:append?valueInputOption=RAW";

        $response = Http::withoutVerifying()->withHeaders([
            'Authorization' => "Bearer $accessToken",
            'Content-Type' => 'application/json'
        ])->post($url, [
            'values' => $values
        ]);

        if (!$response->successful()) {
            throw new \Exception('Error appending data: ' . $response->body());
        }

        return $response->json();
    }
}
