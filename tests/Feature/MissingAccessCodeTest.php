<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Exceptions\MissingAccessCodeException;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Controller;

class MissingAccessCodeTestController extends Controller
{
    public function viewAction()
    {
        $this->requireAccess(['test:access:view']);
        return response('Success', 200);
    }

    public function jsonAction()
    {
        $this->requireAccess(['test:access:json', 'test:access:alternate']);
        return response()->json(['status' => 1, 'message' => 'Success']);
    }
}

class MissingAccessCodeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::get('/_test/missing_access_code/view', [MissingAccessCodeTestController::class, 'viewAction'])->middleware(['web', 'access_codes']);
        Route::post('/_test/missing_access_code/json', [MissingAccessCodeTestController::class, 'jsonAction'])->middleware(['web', 'access_codes']);
    }

    public function test_view_request_returns_access_denied_with_missing_codes()
    {
        $user = \Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('getAccessCodes')->andReturn([]);
        $user->id = 99999;
        
        $response = $this->actingAs($user)->get('/_test/missing_access_code/view');

        $response->assertStatus(403);
        $response->assertViewIs('access_denied');
        $response->assertViewHas('missing_codes', ['test:access:view']);
        $response->assertSee('test:access:view');
    }

    public function test_json_request_returns_json_error_with_missing_codes()
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $user = \Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('getAccessCodes')->andReturn([]);
        $user->id = 99999;

        $response = $this->actingAs($user)->postJson('/_test/missing_access_code/json');

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 0,
            'data' => [
                'missing_codes' => ['test:access:json', 'test:access:alternate']
            ]
        ]);
        $response->assertJsonFragment([
            'missing_codes' => ['test:access:json', 'test:access:alternate']
        ]);
    }
}
