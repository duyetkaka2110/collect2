<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CarryFinal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:carryFinal';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '回収完了データ取込';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $carry = app()->make('App\Http\Controllers\Admin\CarryController');  
        $carry->Final();
        return 0;
    }
}
