<?php
namespace Tests\Feature;
use Tests\TestCase;
class WhatsAppWebhookTest extends TestCase {
    public function test_health_endpoint_works(): void {$this->getJson('/api/test')->assertOk()->assertJson(['status'=>'success']);}
    public function test_meta_can_verify_the_webhook(): void {config(['services.whatsapp.verify_token'=>'token-prueba']); $this->get('/api/whatsapp/webhook?hub_mode=subscribe&hub_verify_token=token-prueba&hub_challenge=12345')->assertOk()->assertSeeText('12345');}
    public function test_webhook_rejects_an_invalid_verification_token(): void {config(['services.whatsapp.verify_token'=>'correcto']); $this->get('/api/whatsapp/webhook?hub_mode=subscribe&hub_verify_token=incorrecto&hub_challenge=12345')->assertForbidden();}
}
