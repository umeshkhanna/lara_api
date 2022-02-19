<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Redis;

class UpdateRankingsCommand extends Command
{
    protected $signature = 'update:rankings';

    public function handle()
    {
        $ambassadors = User::ambassadors()->get();

        $bar = $this->output->createProgressBar($ambassadors->count());
        $bar->start();

        $ambassadors->each(function(User $user)use($bar){
            Redis::zadd('rankings', $user->revenue, $user->name);
            $bar->advance();
        });

        $bar->finish();

    }
}
