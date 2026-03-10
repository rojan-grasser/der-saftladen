<?php

namespace App\Console\Commands;

use App\Enums\Role;
use App\Models\User;
use Database\Factories\UserFactory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Log;

class FeatureDeploymentInit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:feature-deployment-init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a initial user with admin role';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $factory = new UserFactory();

        $user = User::create([
            ...$factory->definition(),
            'password' => Hash::make('password'),
            'name' => 'Test Admin',
            'email' => 'test-admin@example.com',
        ]);
        $user->assignRole(Role::ADMIN);

        $user->markEmailAsVerified();

        Log::info('Created user test admin');
    }
}
