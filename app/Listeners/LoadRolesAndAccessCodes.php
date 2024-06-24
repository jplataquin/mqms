<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class LoadRolesAndAccessCodes
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(Login $event)
    {   
        $user_id = $event->user->id;
        
        $result = DB::table('user_roles')
        ->select('access_codes.code')
        ->distinct()
        ->where('user_id',$user_id)
        ->join('role_access_codes', 'role_access_codes.role_id', '=', 'user_roles.role_id')
        ->join('access_codes', 'access_codes.id', '=', 'role_access_codes.access_code_id')
        ->get();

        $accessCodes = [];

        foreach($result as $row){
            $accessCodes[] = $row->code;
        }

        $this->request->session()->put('access_codes', $accessCodes);
    }
}
