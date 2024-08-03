<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\rcof_boletas;
use Carbon\Carbon;

class RcofTask extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rcof:task';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generar nuevo RCOF correspondiente al nuevo día';

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
        
    }
}
