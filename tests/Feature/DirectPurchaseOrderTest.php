<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\UserRole;
use App\Models\AccessCode;
use App\Models\RoleAccessCode;
use App\Models\Project;
use App\Models\Supplier;
use App\Models\PaymentTerm;
use App\Models\MaterialItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DirectPurchaseOrderTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    public function createApplication()
    {
        $app = parent::createApplication();

        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => false,
        ]]);

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'status' => 'ACTV',
        ]);

        $this->grantAccessCode($this->user, 'direct_purchase_order:own:create');
    }

    protected function grantAccessCode($user, $codeString)
    {
        $accessCode = AccessCode::where('code', $codeString)->first();
        if (!$accessCode) {
            $accessCode = new AccessCode();
            $accessCode->code = $codeString;
            $accessCode->description = 'Test code';
            $accessCode->save();
        }

        $role = new Role();
        $role->name = 'Role ' . $codeString;
        $role->description = 'Test role';
        $role->save();

        $userRole = new UserRole();
        $userRole->user_id = $user->id;
        $userRole->role_id = $role->id;
        $userRole->save();

        $roleAccessCode = new RoleAccessCode();
        $roleAccessCode->role_id = $role->id;
        $roleAccessCode->access_code_id = $accessCode->id;
        $roleAccessCode->save();
    }

    /** @test */
    public function it_can_render_the_direct_purchase_order_create_page()
    {
        $paymentTerm2 = new PaymentTerm();
        $paymentTerm2->text = 'Net 60 Days';
        $paymentTerm2->created_by = $this->user->id;
        $paymentTerm2->save();

        $paymentTerm1 = new PaymentTerm();
        $paymentTerm1->text = 'Cash on Delivery';
        $paymentTerm1->created_by = $this->user->id;
        $paymentTerm1->save();

        $response = $this->actingAs($this->user)->get('/direct_purchase_order/create');
        $response->assertStatus(200);
        $response->assertSee('Cash on Delivery');
        $response->assertSee('Net 60 Days');
        $response->assertSeeInOrder(['Cash on Delivery', 'Net 60 Days']);
    }

    /** @test */
    public function it_can_display_payment_term_name_via_model_accessor()
    {
        $paymentTerm = new PaymentTerm();
        $paymentTerm->text = 'Net 15 Days';
        $paymentTerm->created_by = $this->user->id;
        $paymentTerm->save();

        $this->assertEquals('Net 15 Days', $paymentTerm->name);
        $this->assertEquals('Net 15 Days', $paymentTerm->toArray()['name']);
    }
}
