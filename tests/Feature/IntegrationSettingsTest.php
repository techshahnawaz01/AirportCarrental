<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class IntegrationSettingsTest extends TestCase
{
    use RefreshDatabase;

    private function save(array $values, array $clear = [])
    {
        return $this->actingAs($this->admin())->ajax()->put('/admin/settings/integrations', [
            'values' => $values + ['airport_iata' => 'MIA'],
            'clear' => $clear,
        ]);
    }

    public function test_api_keys_are_stored_encrypted_and_kept_when_left_blank(): void
    {
        $this->save(['tsa_api_key' => 'abc123XYZ'])->assertOk();

        $stored = settings('integrations.tsa_api_key');
        $this->assertNotSame('abc123XYZ', $stored);
        $this->assertSame('abc123XYZ', settings()->secret('integrations.tsa_api_key'));

        // Saving again with the field empty keeps the existing key.
        $this->save(['tsa_api_key' => ''])->assertOk();
        settings()->flush();
        $this->assertSame('abc123XYZ', settings()->secret('integrations.tsa_api_key'));

        // The clear checkbox removes it.
        $this->save([], ['tsa_api_key' => 1])->assertOk();
        settings()->flush();
        $this->assertNull(settings('integrations.tsa_api_key'));
    }

    public function test_saved_key_is_used_for_wait_times_and_never_rendered(): void
    {
        $this->save(['tsa_api_key' => 'secretKey1'])->assertOk();

        Http::fake(['www.tsawaittimes.com/api/airport/secretKey1/MIA/json' => Http::response([
            'rightnow' => 12, 'rightnow_description' => 'Short', 'precheck' => 1, 'estimated_hourly_times' => [], 'precheck_checkpoints' => [],
        ])]);

        $this->actingAs($this->admin())->ajax()->post(route('admin.settings.integrations.test'))
            ->assertOk()->assertJsonPath('success', true);

        $this->actingAs($this->admin())->get('/admin/settings/integrations')
            ->assertOk()->assertSee('Saved')->assertDontSee('secretKey1');
    }

    public function test_invalid_key_characters_are_rejected(): void
    {
        $this->save(['tsa_api_key' => 'bad key<script>'])->assertStatus(422)->assertJsonValidationErrors('values.tsa_api_key');
    }
}
