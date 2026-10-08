<?php

namespace Tests\Feature;

use App\Enums\GeneratorStatus;
use App\Mail\GeneratorCheckpointMail;
use App\Mail\GeneratorUpdatedMail;
use App\Mail\WelcomeClientMail;
use App\Mail\WelcomeUserCreatedMail;
use App\Models\Checkpoint;
use App\Models\Client;
use App\Models\Generator;
use Tests\TestCase;

class NotificationMailUrlTest extends TestCase
{
    public function test_generator_updated_mail_uses_frontend_url_and_query_param(): void
    {
        config(['app.frontend_url' => 'https://app.baifa.com']);

        $client = new Client([
            'id' => 1,
            'company_fiscal_name' => 'Cliente Test C.A.',
            'contact_email' => 'cliente@test.com',
        ]);
        $generator = new Generator([
            'id' => 1,
            'serial_number' => 'GEN-TEST-999',
            'model' => 'Model X',
            'status' => GeneratorStatus::IN_TRANSIT,
        ]);

        $mail = new GeneratorUpdatedMail($generator, $client);

        $this->assertEquals('https://app.baifa.com/tracking?serial=GEN-TEST-999', $mail->trackingUrl);
        $mail->assertSeeInHtml('https://app.baifa.com/tracking?serial=GEN-TEST-999');
    }

    public function test_generator_checkpoint_mail_uses_frontend_url_and_query_param(): void
    {
        config(['app.frontend_url' => 'http://3.147.220.136']);

        $client = new Client([
            'id' => 1,
            'company_fiscal_name' => 'Cliente Test C.A.',
            'contact_email' => 'cliente@test.com',
        ]);
        $generator = new Generator([
            'id' => 1,
            'serial_number' => 'GEN-TEST-888',
            'model' => 'Model Y',
            'status' => GeneratorStatus::WAREHOUSE,
        ]);
        $checkpoint = new Checkpoint([
            'id' => 1,
            'generator_id' => 1,
            'checkpoint_name' => 'Almacén Central',
            'status' => GeneratorStatus::WAREHOUSE,
        ]);

        $mail = new GeneratorCheckpointMail($generator, $checkpoint, $client);

        $this->assertEquals('http://3.147.220.136/tracking?serial=GEN-TEST-888', $mail->trackingUrl);
        $mail->assertSeeInHtml('http://3.147.220.136/tracking?serial=GEN-TEST-888');
    }

    public function test_welcome_mails_use_frontend_url(): void
    {
        config(['app.frontend_url' => 'https://tracking.baifa.com.ve']);

        $welcomeClient = new WelcomeClientMail('Cliente Demo', 'cliente@demo.com');
        $this->assertEquals('https://tracking.baifa.com.ve/login', $welcomeClient->loginUrl);

        $welcomeUser = new WelcomeUserCreatedMail('Usuario Demo', 'user@demo.com', 'Operador', 'secret123');
        $this->assertEquals('https://tracking.baifa.com.ve/login', $welcomeUser->loginUrl);
    }
}
