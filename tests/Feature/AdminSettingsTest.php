<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

final class AdminSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_open_settings(): void
    {
        $user = User::factory()->create();
        config(['gigranker.admin.emails' => []]);

        $this->actingAs($user)->get(route('admin.settings'))->assertForbidden();
    }

    public function test_admin_can_save_settings_and_secrets_are_encrypted(): void
    {
        $admin = User::factory()->create();
        config(['gigranker.admin.emails' => [strtolower($admin->email)]]);

        $this->actingAs($admin)->post(route('admin.settings.update'), [
            'ai_provider' => 'openai',
            'gemini_model' => 'gemini-2.5-flash',
            'groq_model' => 'llama-3.3-70b-versatile',
            'openai_model' => 'gpt-5-mini',
            'bep20_address' => '0x1234567890123456789012345678901234567890',
            'bep20_network' => 'BSC',
            'gemini_api_key' => 'gemini-secret',
            'groq_api_key' => 'groq-secret',
            'openai_api_key' => 'openai-secret',
        ])->assertRedirect()->assertSessionHas('success');

        $setting = AppSetting::query()->where('key', 'openai_api_key')->firstOrFail();
        $this->assertTrue($setting->is_secret);
        $this->assertNotSame('openai-secret', $setting->value);
        $this->assertSame('openai-secret', Crypt::decryptString($setting->value));
        $this->assertDatabaseHas('app_settings', ['key' => 'bep20_address', 'value' => '0x1234567890123456789012345678901234567890']);
    }
}
