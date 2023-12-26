<?php

namespace App\Console\Commands;

use App\Notifications\CertificateExpirationMailNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class CheckCertificateExpirationTime extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-certificate-expiration-time';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check certificate expiration time and send notifications to user mail';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $users=DB::table('users')->select('*')->get();
            foreach($users as $user){
                $theshold=intval($user->theshold);
                $certificates=DB::table('certificates as c')
                ->join('users as u', 'c.user_id', '=', 'u.id')
                ->where('u.id','=',$user->id)
                ->where('u.receive_notification','=','1')
                ->where('c.not_after', '<', now()->addDays($theshold))
                ->where('c.not_after', '>',now() )
                ->where('c.is_send','=',0)
                ->select('c.*')
                ->get();

                if($certificates->isNotEmpty()){
                    if($user->receive_email == null){
                        Notification::route('mail',$user->email)
                                    ->notify(new CertificateExpirationMailNotification($certificates,$user->email));

                                    DB::table('certificates as c')
                                    ->join('users as u', 'c.user_id', '=', 'u.id')
                                    ->where('u.id','=',$user->id)
                                    ->where('u.receive_notification','=','1')
                                    ->where('c.not_after', '<', now()->addDays($theshold))
                                    ->where('c.not_after', '>',now())
                                    ->update(['c.is_send'=>1])
                                    ;
                    }else{
                        $receiveEmail=explode(',',$user->receive_email);
                        foreach($receiveEmail as $email){
                            Notification::route('mail',$email)
                            ->notify(new CertificateExpirationMailNotification($certificates,$user->email));

                            DB::table('certificates as c')
                            ->join('users as u', 'c.user_id', '=', 'u.id')
                            ->where('u.id','=',$user->id)
                            ->where('u.receive_notification','=','1')
                            ->where('c.not_after', '<', now()->addDays($theshold))
                            ->where('c.not_after', '>',now())
                            ->update(['c.is_send'=>1])
                            ;

                        }
                    }
                }

            }
            $this->info('Certificate expiration check completed.');
    }

    public function should_send_notifications($receive_notification,$theshold,$not_after){

        return $receive_notification == '1'
            && is_numeric($theshold)
            && $not_after < now()->addDays(intval($theshold))
            && $not_after > now();
    }
}
