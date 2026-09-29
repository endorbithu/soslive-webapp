<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

#[Signature('admin:create {email} {--name=Admin}')]
#[Description('Admin felhasználó létrehozása vagy jelszavának cseréje')]
class CreateAdmin extends Command
{
    public function handle(): int
    {
        $email = mb_strtolower($this->argument('email'));
        $password = $this->secret('Jelszó (min. 12 karakter)');

        $validator = Validator::make(compact('email', 'password'), [
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:12'],
        ]);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $values = ['name' => $this->option('name'), 'password' => Hash::make($password), 'updated_at' => now()];
        if (DB::table('admins')->where('email', $email)->exists()) {
            DB::table('admins')->where('email', $email)->update($values);
        } else {
            DB::table('admins')->insert($values + ['email' => $email, 'created_at' => now()]);
        }

        $this->info("Admin mentve: {$email}");

        return self::SUCCESS;
    }
}
