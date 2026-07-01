<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;

class QuoteAutocompleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_autocomplete_returns_empty_when_no_query_provided()
    {
        $response = $this->get('/api/autocomplete');

        $response->assertStatus(200);
        $response->assertJson([]);
    }

    public function test_autocomplete_returns_suggestions_from_google_places_api_when_key_is_configured()
    {
        Config::set('services.google.maps_api_key', 'mock-google-places-key');

        Http::fake([
            'maps.googleapis.com/maps/api/place/autocomplete/json*' => Http::response([
                'status' => 'OK',
                'predictions' => [
                    ['description' => 'Av. Insurgentes Sur 123, CDMX, México'],
                    ['description' => 'Insurgentes Centro 45, CDMX, México'],
                    ['description' => 'Colonia Insurgentes, Aguascalientes, México'],
                ]
            ], 200)
        ]);

        $response = $this->get('/api/autocomplete?query=Insurgentes');

        $response->assertStatus(200);
        $response->assertJson([
            'Av. Insurgentes Sur 123, CDMX, México',
            'Insurgentes Centro 45, CDMX, México',
            'Colonia Insurgentes, Aguascalientes, México',
        ]);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'maps.googleapis.com') && 
                   $request['key'] === 'mock-google-places-key' &&
                   $request['input'] === 'Insurgentes';
        });

        Http::assertNotSent(function ($request) {
            return str_contains($request->url(), 'generativelanguage.googleapis.com');
        });
    }

    public function test_autocomplete_falls_back_to_gemini_when_google_maps_key_is_missing()
    {
        Config::set('services.google.maps_api_key', null);
        Config::set('gemini.api_key', 'mock-gemini-key');

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => '["Calle Insurgentes 10, Guadalajara", "Avenida Insurgentes 20, Monterrey"]']
                            ]
                        ]
                    ]
                ]
            ], 200)
        ]);

        $response = $this->get('/api/autocomplete?query=Insurgentes');

        $response->assertStatus(200);
        $response->assertJson([
            'Calle Insurgentes 10, Guadalajara',
            'Avenida Insurgentes 20, Monterrey'
        ]);

        Http::assertNotSent(function ($request) {
            return str_contains($request->url(), 'maps.googleapis.com');
        });

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'generativelanguage.googleapis.com');
        });
    }

    public function test_autocomplete_falls_back_to_gemini_when_google_maps_api_fails()
    {
        Config::set('services.google.maps_api_key', 'mock-google-places-key');
        Config::set('gemini.api_key', 'mock-gemini-key');

        Http::fake([
            'maps.googleapis.com/*' => Http::response(['status' => 'REQUEST_DENIED'], 200),
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => '["Insurgentes 100, Mérida", "Insurgentes 200, Querétaro"]']
                            ]
                        ]
                    ]
                ]
            ], 200)
        ]);

        $response = $this->get('/api/autocomplete?query=Insurgentes');

        $response->assertStatus(200);
        $response->assertJson([
            'Insurgentes 100, Mérida',
            'Insurgentes 200, Querétaro'
        ]);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'maps.googleapis.com');
        });

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'generativelanguage.googleapis.com');
        });
    }

    public function test_autocomplete_returns_static_fallback_when_both_apis_fail()
    {
        Config::set('services.google.maps_api_key', 'mock-google-places-key');
        Config::set('gemini.api_key', 'mock-gemini-key');

        Http::fake([
            'maps.googleapis.com/*' => Http::response([], 500),
            'generativelanguage.googleapis.com/*' => Http::response([], 500)
        ]);

        $response = $this->get('/api/autocomplete?query=Insurgentes');

        $response->assertStatus(200);
        $response->assertJson([
            'Insurgentes, Ciudad de México, CDMX, México',
            'Insurgentes, Monterrey, Nuevo León, México',
            'Insurgentes, Guadalajara, Jalisco, México',
        ]);
    }
}
