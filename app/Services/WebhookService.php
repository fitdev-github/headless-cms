<?php

namespace App\Services;

use App\Models\Webhook;
use App\Models\WebhookLog;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class WebhookService
{
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client(['timeout' => 10, 'verify' => !app()->isLocal()]);
    }

    /**
     * Dispatch an event to all matching enabled webhooks.
     *
     * @param string $event   e.g. 'entry.create'
     * @param array  $payload  The body to POST as JSON
     */
    public function dispatch(string $event, array $payload): void
    {
        $webhooks = Webhook::forEvent($event);

        foreach ($webhooks as $webhook) {
            $this->send($webhook, $event, $payload);
        }
    }

    protected function send(Webhook $webhook, string $event, array $payload): void
    {
        $body        = json_encode($payload);
        $statusCode  = null;
        $response    = null;
        $success     = false;

        // Build headers
        $headers = [
            'Content-Type'  => 'application/json',
            'User-Agent'    => 'HeadlessCMS-Webhook/1.0',
            'X-HeadlessCMS-Event' => $event,
        ];

        foreach ($webhook->headers ?? [] as $h) {
            if (!empty($h['key']) && isset($h['value'])) {
                $headers[$h['key']] = $h['value'];
            }
        }

        try {
            $res        = $this->client->post($webhook->url, [
                'headers' => $headers,
                'body'    => $body,
            ]);
            $statusCode = $res->getStatusCode();
            $response   = substr((string) $res->getBody(), 0, 1000);
            $success    = $statusCode >= 200 && $statusCode < 300;
        } catch (RequestException $e) {
            $statusCode = $e->hasResponse() ? $e->getResponse()->getStatusCode() : null;
            $response   = $e->getMessage();
        } catch (\Throwable $e) {
            $response = $e->getMessage();
        }

        WebhookLog::create([
            'webhook_id'   => $webhook->id,
            'event'        => $event,
            'payload'      => $body,
            'status_code'  => $statusCode,
            'response'     => $response,
            'success'      => $success,
            'delivered_at' => now(),
        ]);
    }

    /**
     * Build a standard entry payload.
     */
    public static function entryPayload(string $event, string $model, array $entryData): array
    {
        return [
            'event'     => $event,
            'createdAt' => now()->toISOString(),
            'model'     => $model,
            'uid'       => 'api::' . $model . '.' . $model,
            'entry'     => $entryData,
        ];
    }

    /**
     * Build a standard media payload.
     */
    public static function mediaPayload(string $event, array $mediaData): array
    {
        return [
            'event'     => $event,
            'createdAt' => now()->toISOString(),
            'media'     => $mediaData,
        ];
    }
}
