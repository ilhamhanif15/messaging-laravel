<?php

namespace App\Console\Commands;

use App\Models\Merchant;
use Illuminate\Console\Command;

class RegisterNewMerchant extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:register-new-merchant {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Register new merchant';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');

        if (!$name) {
            $this->error('Please input merchant name in argument');
            return 0;
        }

        Merchant::create([
            'name' => $this->argument('name'),
            'is_active' => true
        ]);

        $this->info('New Merchant created');
    }
}
