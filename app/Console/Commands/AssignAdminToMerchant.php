<?php

namespace App\Console\Commands;

use App\Models\Merchant;
use App\Models\MerchantUser;
use App\Models\User;
use Illuminate\Console\Command;

class AssignAdminToMerchant extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:assign-admin-to-merchant {user_email} {merchant_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign by user email to Merchant by merchant id';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userEmail = $this->argument('user_email');
        $merchantId = $this->argument('merchant_id');

        $user = User::whereEmail($userEmail)->firstOrFail();
        $merchant = Merchant::find($merchantId);
        
        MerchantUser::firstOrCreate([
            'user_id' => $user->id, 'merchant_id' => $merchant->id
        ]);
    }
}
