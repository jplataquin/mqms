<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;

class ReplicateDatabaseCommandTest extends TestCase
{
    /** @test */
    public function it_fails_replicate_db_command_if_required_inputs_are_missing()
    {
        $this->artisan('replicate:db')
            ->expectsQuestion('Please enter the source Database Host', '127.0.0.1')
            ->expectsQuestion('Please enter the source Database Port', '3306')
            ->expectsQuestion('Please enter the source Database Name', '') // empty name
            ->expectsQuestion('Please enter the source Database Username', '') // empty user
            ->expectsQuestion('Please enter the source Database Password', '')
            ->expectsOutput('Database Name and Username are strictly required to proceed.')
            ->assertExitCode(1);
    }

    /** @test */
    public function it_fails_replicate_db_command_if_connection_fails()
    {
        // Provide invalid host/credentials to force connection failure
        $this->artisan('replicate:db')
            ->expectsQuestion('Please enter the source Database Host', 'invalid-host-name-1234')
            ->expectsQuestion('Please enter the source Database Port', '9999')
            ->expectsQuestion('Please enter the source Database Name', 'prod_db')
            ->expectsQuestion('Please enter the source Database Username', 'prod_user')
            ->expectsQuestion('Please enter the source Database Password', 'secret_pwd')
            ->expectsOutput('Connecting to source database `prod_db`...')
            ->assertExitCode(1);
    }
}
