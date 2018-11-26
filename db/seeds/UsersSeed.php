<?php

use Phinx\Seed\AbstractSeed;

class UsersSeed extends AbstractSeed
{
    public function run()
    {
        $faker = Faker\Factory::create();
        $data = [];
        for ($i = 0; $i < 25; ++$i) {
            $data[] = [
                'name' => $faker->firstName,
                'surname' => $faker->lastName,
                'password' => password_hash($faker->password, PASSWORD_BCRYPT),
                'email' => $faker->email,
                'created_at' => date('Y-m-d H:i:s'),
            ];
        }

        $this->insert('users', $data);
    }
}
